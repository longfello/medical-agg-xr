<?php

namespace frontend\modules\api\components;

use frontend\modules\api\models\LogWebhooks;
use frontend\modules\patient\components\PaymentProcessor;
use common\models\Patient;
use common\models\Payments;
use common\models\LifeCardOrders;
use common\components\Helper;
use Stripe\Subscription;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Html;
use yii\helpers\Url;
use common\components\NotificationManager\channels\Email\Email;
use common\components\NotificationManager\messages\StripeSubscriptionCancelled;
use common\components\NotificationManager\messages\InvoicePaymentFailed;
use common\components\NotificationManager\messages\InvoicePaymentFailedLast;
use common\components\NotificationManager\messages\InvoiceUpcoming;

/**
 * Class Webhook
 * @package frontend\modules\api\components
 */
class Webhook extends Component
{
    /**
     * @var
     */
    public $event;
    /** @var LogWebhooks */
    public $model;
    /**
     * @var
     */
    private $patient;

    /**
     * @const string SLUG_SENT_FAILED_EMAIL Name of slug in life_patients_meta for identity flag of email about payment failures is sent
     * This slug can have such values in life_patients_meta:
     * 0 - No one notification was sent. Flag will be set in this state after any succeed payment
     * 1 - Sent email about payment failed
     * 2 - Sent email about subscription deleted
     */
    const SLUG_SENT_FAILED_EMAIL = 'sent_failed_email';

    /*
     * Purpose of the flush, is to send the response code back to Stripe right away
     * to prevent their request from timing out, and retries being sent.
     */

    /**
     *
     */
    public function init()
    {
        $this->patient = null;
        $this->model = LogWebhooks::findOne(['stripe_event_id' => $this->event->id]);
        if (!$this->model) {
            $customer_id = $patients_id = null;
            if (isset($this->event->data) && isset($this->event->data->object) && isset($this->event->data->object->customer)) {
                $customer_id = $this->event->data->object->customer;
            }
            if ($customer_id) {
                $patient = Patient::findOne(['stripe_customer' => $customer_id]);
                if ($patient) {
                    $patients_id = $patient->patients_id;
                    $this->patient = $patient;
                }
            }

            $this->model = new LogWebhooks();
            $this->model->stripe_event_id = $this->event->id;
            $this->model->type = $this->event->type;
            $this->model->event = json_encode($this->event);
            $this->model->customer_id = $customer_id;
            $this->model->patients_id = $patients_id;
        } else {
            $this->patient = Patient::findOne($this->model->patients_id);
        }
    }

    /**
     *
     */
    public function account_updated()
    {
    }

    /**
     *
     */
    public function account_application_deauthorized()
    {
    }

    /**
     *
     */
    public function account_external_account_created()
    {
    }

    /**
     *
     */
    public function account_external_account_deleted()
    {
    }

    /**
     *
     */
    public function account_external_account_updated()
    {
    }

    /**
     *
     */
    public function application_fee_created()
    {
    }

    /**
     *
     */
    public function application_fee_refunded()
    {
    }

    /**
     *
     */
    public function application_fee_refund_updated()
    {
    }

    /**
     *
     */
    public function balance_available()
    {
    }

    /**
     *
     */
    public function bitcoin_receiver_created()
    {
    }

    /**
     *
     */
    public function bitcoin_receiver_filled()
    {
    }

    /**
     *
     */
    public function bitcoin_receiver_updated()
    {
    }

    /**
     *
     */
    public function bitcoin_receiver_transaction_created()
    {
    }

    /**
     *
     */
    public function charge_captured()
    {
    }

    /**
     *
     */
    public function charge_failed()
    {
    }

    /**
     *
     */
    public function charge_pending()
    {
    }

    /**
     *
     */
    public function charge_refunded()
    {
    }

    /**
     * @return bool
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\HttpException
     */
    public function charge_succeeded()
    {
        // For test processing real event:
        //$this->event = json_decode(raw event data from life_log_webhooks.event);

        if (isset($this->event) && isset($this->event->data) && isset($this->event->data->object)) {
            $data = $this->event->data->object;
        } else {
            return false;
        }

        if ($data) {
            if (\Yii::$app->stripe->isTestEvent) {
                $this->patient = Patient::findOne(['patients_id' => 755]);
                $internal_id = $this->patient->internal_id;
                $customer_id = $this->patient->stripe_customer;
            } else {
                $internal_id = ($this->patient ? $this->patient->internal_id : '');
                $customer_id = (isset($data->customer) ? $data->customer : '');
            }

            if (isset($this->patient) && $this->patient->meta->getValue(self::SLUG_SENT_FAILED_EMAIL, 0) > 0) {
                $this->patient->meta->saveValue(self::SLUG_SENT_FAILED_EMAIL, 0);
            }

            $model = new Payments();
            $model->setAttributes([
                'internal_id' => $internal_id,
                'amount' => (isset($data->amount) ? $data->amount / 100 : ''),
                'date' => (isset($this->event->created) ? date('Y-m-d H:i:s', $this->event->created) : ''),
                'description' => (isset($data->description) ? $data->description : ''),
                'stripe_customer' => $customer_id,
                'stripe_charge_id' => (isset($data->id) ? $data->id : null),
            ]);

            if (isset($data->invoice)) {
                $model->stripe_invoice_id = $data->invoice;
                $invoice = \Yii::$app->stripe->retrieveInvoice($data->invoice);
                if ($invoice && isset($invoice->subscription)) {
                    $model->stripe_subscription_id = $invoice->subscription;
                    $subscription = \Yii::$app->stripe->getSubscribe(true, $invoice->subscription);
                    if (isset($subscription->metadata->coupon)) {
                        $model->coupon_name = $subscription->metadata->coupon;
                    }

                    if (empty($model->description) && isset($subscription->metadata->subscription_type)) {
                        $model->description = $subscription->metadata->subscription_type;
                    }
                }
            }

            if (isset($data->source) && (
                    isset($data->source->address_line1)
                    || isset($data->source->address_line2)
                    || isset($data->source->address_city)
                    || isset($data->source->address_state)
                    || isset($data->source->address_zip)
                )) {

                $sourceAddress = (isset($data->source->address_line1) ? $data->source->address_line1 : '');
                $delimiter = (empty($sourceAddress) ? '' : ' ');
                $sourceAddress .= (isset($data->source->address_line2) ? $delimiter . $data->source->address_line2 : '');

                $model->setAttributes([
                    'payment_address' => $sourceAddress,
                    'payment_city' => (isset($data->source->address_city) ? $data->source->address_city : ''),
                    'payment_state' => (isset($data->source->address_state) ? $data->source->address_state : ''),
                    'payment_zip' => (isset($data->source->address_zip) ? (string)$data->source->address_zip : ''),
                ]);
            } elseif (isset($this->patient)) {
                $sourceAddress = $this->patient->address_1;
                $delimiter = (empty($sourceAddress) ? '' : ' ');
                $sourceAddress .= $delimiter . $this->patient->address_2;

                $model->setAttributes([
                    'payment_address' => $sourceAddress,
                    'payment_city' => $this->patient->city,
                    'payment_state' => $this->patient->state,
                    'payment_zip' => $this->patient->zip,
                ]);
            }

            if (isset($data->shipping)) {
                $shippingAddress = (isset($data->shipping->address_line1) ? $data->shipping->address_line1 : '');
                $delimiter = (empty($shippingAddress) ? '' : ' ');
                $shippingAddress .= (isset($data->shipping->address_line2) ? $delimiter . $data->shipping->address_line2 : '');

                $model->setAttributes([
                    'shipping_address' => $shippingAddress,
                    'shipping_city' => (isset($data->shipping->address_city) ? $data->shipping->address_city : ''),
                    'shipping_state' => (isset($data->shipping->address_state) ? $data->shipping->address_state : ''),
                    'shipping_zip' => (isset($data->shipping->address_zip) ? (string)$data->shipping->address_zip : ''),
                ]);

            } elseif (isset($data->metadata) && isset($data->metadata->transaction) && isset($data->metadata->card_order_id) && $data->metadata->transaction == PaymentProcessor::TRANSACTION_CARD_ORDER) {
                $orderId = $data->metadata->card_order_id;
                $order = LifeCardOrders::findOne($orderId);

                $shippingAddress = $order->address_1;
                $delimiter = (empty($shippingAddress) ? '' : ' ');
                $shippingAddress .= $delimiter . $order->address_2;

                $model->setAttributes([
                    'shipping_address' => $shippingAddress,
                    'shipping_city' => $order->city,
                    'shipping_state' => $order->state,
                    'shipping_zip' => $order->zip,
                ]);
            }

            if (isset($data->metadata->coupon_name)) {
                $model->coupon_name = $data->metadata->coupon_name;
            }

            if (!$model->save()) {
                \Yii::warning(
                    "Record to \"" . Payments::tableName() . "\" not saved."
                    . "\n\"stripe_customer\" = \"$customer_id\"."
                    . "\nErrors:" . json_encode($model->errors, JSON_PRETTY_PRINT)
                );
            }
        }
        return true;
    }

    /**
     *
     */
    public function charge_updated()
    {
    }

    /**
     *
     */
    public function charge_dispute_closed()
    {
    }

    /**
     *
     */
    public function charge_dispute_created()
    {
    }

    /**
     *
     */
    public function charge_dispute_funds_reinstated()
    {
    }

    /**
     *
     */
    public function charge_dispute_funds_withdrawn()
    {
    }

    /**
     *
     */
    public function charge_dispute_updated()
    {
    }

    /**
     *
     */
    public function coupon_created()
    {
    }

    /**
     *
     */
    public function coupon_deleted()
    {
    }

    /**
     *
     */
    public function coupon_updated()
    {
    }

    /**
     *
     */
    public function customer_created()
    {
    }

    /**
     *
     */
    public function customer_deleted()
    {
    }

    /**
     *
     */
    public function customer_updated()
    {
    }

    /**
     *
     */
    public function customer_discount_created()
    {
    }

    /**
     *
     */
    public function customer_discount_deleted()
    {
    }

    /**
     *
     */
    public function customer_discount_updated()
    {
    }

    /**
     *
     */
    public function customer_source_created()
    {
    }

    /**
     *
     */
    public function customer_source_deleted()
    {
    }

    /**
     *
     */
    public function customer_source_updated()
    {
    }

    /**
     *
     */
    public function customer_subscription_created()
    {
    }

    /**
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function customer_subscription_deleted()
    {
        if ($this->event->data->object->cancel_at_period_end) {
            if ($this->event->data->object->status == Subscription::STATUS_CANCELED) {
                if (\Yii::$app->stripe->isTestEvent) {
                    $patient = Patient::findOne(['patients_id' => 755]);
                } else {
                    $patient = Patient::findOne(['stripe_subscription_id' => $this->event->data->object->id]);
                }

                if (!$patient) {
                    echo("Patient not found: {$this->event->data->object->customer}. ");
                    return false;
                }

                $patient->stripe_subscription_id = '';
                $patient->update();

                if ($patient->meta->getValue(self::SLUG_SENT_FAILED_EMAIL, 0) == 0) {
                    $patient->meta->saveValue(self::SLUG_SENT_FAILED_EMAIL, 2);

                    $notification = new StripeSubscriptionCancelled();
                    return $notification->send($patient, false, Email::getID());
                }
            }
        }
        return false;
    }

    /**
     *
     */
    public function customer_subscription_trial_will_end()
    {
    }

    /**
     *
     */
    public function customer_subscription_updated()
    {
    }

    /**
     *
     */
    public function invoice_created()
    {
    }

    /**
     * @return bool
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function invoice_payment_failed()
    {
        $mail = null;
        $patient = Patient::findOne(['stripe_customer' => $this->event->data->object->customer]);

        if (!$patient) {
            echo("Patient not found: {$this->event->data->object->customer}. ");
            return false;
        }

        if ($patient->status == Patient::STATUS_CANCEL) {
            echo("Patient already has status CANCEL: {$this->event->data->object->customer}. ");
            return false;
        }

        if ($patient->meta->getValue(self::SLUG_SENT_FAILED_EMAIL, 0) < 2) {
            $patient->meta->saveValue(self::SLUG_SENT_FAILED_EMAIL, 1);

            if ($this->event->data->object->attempt_count > 3) {
                $mail = new InvoicePaymentFailedLast();
            } else {
                $mail = new InvoicePaymentFailed([
                    'weeks' => 4 - $this->event->data->object->attempt_count,
                ]);
            }
            $mail->href = Url::to('/subscriber-home/account-status?mode=popup', true);
        }

        if ($mail) {
            echo ("Patient {$this->event->data->object->customer} exist. Processing... \n");
            foreach (Helper::asFlatArray($this->event->data->object) as $key => $val) {
                if (property_exists($mail, $key)) {
                    $mail->$key = $val;
                }
            }

            $patient->stripe_subscription_id = '';
            $patient->save();
            $mail->patient = $patient;

            if (!$mail->send($patient, false, Email::getID())) {
                echo ('Error occurred when notification sending');
                // throw new Exception(' Error occurred: '.strip_tags(Html::errorSummary($mail)));
            }
        }

        return true;
    }

    /**
     *
     */
    public function invoice_payment_succeeded()
    {
    }

    /**
     *
     */
    public function invoice_sent()
    {
    }

    /**
     *
     */
    public function invoice_updated()
    {
    }

    /**
     * @return bool
     */
    public function invoice_upcoming()
    {
        if (\Yii::$app->stripe->isTestEvent) {
            $patient = Patient::findOne(['patients_id' => 755]);
            $customer_id = $patient->stripe_customer;
            // $customer_id = 'cus_AwCNYaMOB9VVkY';
        } else {
            $customer_id = $this->event->data->object->customer;
            $patient = Patient::findOne(['stripe_customer' => $customer_id]);
        }

        if (!$patient) {
            echo("Patient not found: {$customer_id}. ");
            return false;
        }
        \Yii::$app->stripe->setKey();
        try {
            foreach ($this->event->data->object->lines->data as $data) {
                $plan = $data->plan;
                if ($plan->interval == 'year' || $plan->interval == 'month') {
                    $mail = new InvoiceUpcoming([
                        'amount' => $plan->amount,
                        'start'  => $data->period->start,
                    ]);

                    if (!$mail->send($patient, false, Email::getID())) {
                        echo ('Error occurred when notification sending');
                        // throw new Exception(' Error occurred: '.strip_tags(Html::errorSummary($mail)));
                    }
                }
            }
        } catch (\Exception $e) {
            echo(' Error occurred: ' . $e->getMessage() . ' in ' . $e->getFile() . " [" . $e->getLine() . "]");
        }
        return true;
    }

    /**
     *
     */
    public function invoiceitem_created()
    {
    }

    /**
     *
     */
    public function invoiceitem_deleted()
    {
    }

    /**
     *
     */
    public function invoiceitem_updated()
    {
    }

    /**
     *
     */
    public function order_created()
    {
    }

    /**
     *
     */
    public function order_payment_failed()
    {
    }

    /**
     *
     */
    public function order_payment_succeeded()
    {
    }

    /**
     *
     */
    public function order_updated()
    {
    }

    /**
     *
     */
    public function order_return_created()
    {
    }

    /**
     *
     */
    public function payout_paid()
    {
    }

    /**
     *
     */
    public function payout_failed()
    {
    }

    /**
     *
     */
    public function payout_canceled()
    {
    }

    /**
     *
     */
    public function payout_created()
    {
    }

    /**
     *
     */
    public function payout_updated()
    {
    }

    /**
     *
     */
    public function plan_created()
    {
    }

    /**
     *
     */
    public function plan_deleted()
    {
    }

    /**
     *
     */
    public function plan_updated()
    {
    }

    /**
     *
     */
    public function product_created()
    {
    }

    /**
     *
     */
    public function product_deleted()
    {
    }

    /**
     *
     */
    public function product_updated()
    {
    }

    /**
     *
     */
    public function review_closed()
    {
    }

    /**
     *
     */
    public function review_opened()
    {
    }

    /**
     *
     */
    public function sku_created()
    {
    }

    /**
     *
     */
    public function sku_deleted()
    {
    }

    /**
     *
     */
    public function sku_updated()
    {
    }

    /**
     *
     */
    public function source_canceled()
    {
    }

    /**
     *
     */
    public function source_chargeable()
    {
    }

    /**
     *
     */
    public function source_failed()
    {
    }

    /**
     *
     */
    public function source_transaction_created()
    {
    }

    /**
     *
     */
    public function transfer_created()
    {
    }

    /**
     *
     */
    public function transfer_deleted()
    {
    }

    /**
     *
     */
    public function transfer_failed()
    {
    }

    /**
     *
     */
    public function transfer_paid()
    {
    }

    /**
     *
     */
    public function transfer_reversed()
    {
    }

    /**
     *
     */
    public function transfer_updated()
    {
    }

    /**
     *
     */
    public function ping()
    {
    }

    /**
     * @return bool
     */
    public function isNewEvent()
    {
        return $this->model->isNewRecord;
    }

    /**
     *
     */
    public function log()
    {
        if (!$this->model->save()) {
            echo Html::errorSummary($this->model);
        }
    }

}
