<?php
namespace common\components\NotificationManager\transports\Nexmo;

use common\components\NotificationManager\components\BaseTransport;
use common\components\NotificationManager\messages\UnroutablePhoneNumber;
use common\components\NotificationManager\channels\Email\Email;
use common\models\Announcements;
use common\models\AnnouncementTypes;
use common\models\SmsQueue;
use common\models\SmsLog;
use common\models\SmsChain;
use common\models\SmsDataHash;
use common\models\PatientMeta;
use common\models\PatientsSmsRequest;
use common\components\Nexmo;

abstract class NexmoCommon extends BaseTransport
{
    public $fromNumber;

    /**
     * {@inheritdoc}
     */
    public function __send($message)
    {
        try {
            $queue = SmsQueue::add($message);
            if ($queue) {
                return $queue->sendSms();
            }
        } catch (\Throwable $e) {
            return $this->setError($e->getCode(), $e->getMessage(), $e->getTraceAsString());
        }
        return false;
    }

    /**
     * Send the SMS message from queue to Nexmo 
     * @param type SmsQueue $queue
     * @return array Provider's response
     */
    public function sendToProvider($queue)
    {
        $messageContent = $queue->message->getMessageContent(false);

        $response = \Yii::$app->nexmo->send_message(
                $this->fromNumber,
                $queue->to,
                ['text' => $messageContent['body'], 'id' => $queue->id],
                'text'
            );

        // Nexmo response example (for testing)
        /*
        $response = [
            'message-count' => '1',
            'messages' => [
                [
                    'to' => '380667778899',
                    'message-id' => '1300000015FD1832',
                    'status' => '11',
                    'remaining-balance' => '9.99',
                    'message-price' => '0.07300000',
                    'network' => '25501',
                ],
            ]
        ];
        */

        if (isset($response['message-count']) && $response['message-count'] > 0 && isset($response['messages'])) {
            $queue->patient->meta->deleteValue(PatientMeta::CELLPHONE_INVALID);

            foreach ($response['messages'] as $msg) {
                $this->createChainFromNexmo($msg, $queue->id);
                /** @var BaseMessage|SmsTestReport $message */
                $message = $queue->message->getMessage();
                if (isset($message->test_id)) {
                    $queue->createTestSign($msg, $message->test_id);
                }

                if ((int)$msg['status'] !== Nexmo::RESPONSE_OK) {
                    $this->setError($msg['status']);

                    switch ($msg['status']) {
                        case Nexmo::RESPONSE_INCORRECT_NUMBER:
                        case Nexmo::RESPONSE_UNROUTABLE_NUMBER:
                            $queue->patient->is_confirmed_cell_phone = 0;
                            $queue->patient->save();

                            $smsRequest = new PatientsSmsRequest();
                            $smsRequest->create($queue->patient, $queue->to);

                            $queue->patient->meta->saveValue(PatientMeta::CELLPHONE_INVALID, date('Y-m-d H:i:s').' it was blocked');

                            $notification = new UnroutablePhoneNumber(['phoneNumber' => $queue->to]);
                            $announceText = $notification->compose($notification::LAYOUT_TEXT, ['phoneNumber' => $queue->to]);
                            Announcements::add(AnnouncementTypes::TYPE_SYSTEM_ANNOUNCEMENT_INFO, $announceText, $queue->patient);

                            if ($queue->patient->email) {
                                $notification->send($queue->patient->email, false, Email::getID());
                            }

                            $this->setError($msg['status'], "Unfortunately it's impossible to send SMS to the cell phone ". $queue->to ." - becouse it's incorrect.");
                            $queue->message->disable("SMS is not sent. Phone ". $queue->to ." is unroutable");
                            break;

                        default:
                            $this->setError($msg['status'], $msg['error-text'] ?? 'Unknown error');
                    }
                    break;
                }
            }
        } else {
            $queue->log(SmsLog::LOGEVENT_PROVIDER_ERROR, $response, [static::getID() => 'Undefined response']);
            $this->setError(Nexmo::RESPONSE_UNDEFINED, 'Undefined response: '.json_encode($response));
        }

        return $response;
    }

    /**
     * Process Nexmo webhook
     * @param array $data Webhook data
     * @param SmsQueue $queue related queue instance
     * @return void
     * @throws \Exception
     */
    public function processWebhook($data, $queue)
    {
        $status = isset($data['status']) ? $data['status'] : null;
        $message_id = isset($data['messageId']) ? $data['messageId'] : false;
        if (!$message_id) {
            $message_id = isset($data['message-id']) ? $data['message-id'] : false;
        }

        // NOT PROCESS STATUS "accepted"
        if (!in_array($status, [Nexmo::STATUS_DELIVERED, Nexmo::STATUS_FAILED, Nexmo::STATUS_EXPIRED, Nexmo::STATUS_REJECTED])) {
            $queue->log(SmsLog::LOGEVENT_CALLBACK, $data, [static::getID() => 'message status: '.$status]);
            return;
        }

        if ($message_id) {
            $totalSmsChainCount = count(SmsChain::findAll(['sms_id' => $queue->id]));

            // CHECK HASH. IF ALREADY WAS REQUEST WITH SAME PARAMS - RETURN
            if ($status) {
                $hash = md5($message_id . $status);
                $dataHash = SmsDataHash::findOne(['hash' => $hash]);
                if ($dataHash) {
                    return;
                }
            } else {
                $hash = null;
            }

            // START TRANSACTION
            $db = \Yii::$app->getDb();
            $db->createCommand("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ; START TRANSACTION;")->execute();

            try {
                $chain = SmsChain::findOne(['sms_id' => $queue->id, 'message_id' => $message_id]);
                if ($chain) {
                    if ($status == Nexmo::STATUS_DELIVERED) {

                        // REPEAT TRANSACTION FROM THIS POINT IF DEADLOCK APPEARED
                        $db->createCommand("SAVEPOINT sp")->execute();

                        $query = $db->createCommand("SELECT * FROM `life_sms_data_hash` WHERE sms_id = {$queue->id} FOR UPDATE");
                        $hashCount = count($query->queryAll());

                        // PROCESS CURRENT CHAIN
                        $queue->last_failure_reason = null;
                        $chain->message_status = SmsChain::STATUS_DELIVERED;
                        $chain->save();

                        // SAVE HASH, NEED TO PREVENT REQUESTS WITH SAME PARAMS
                        $dataHash = new SmsDataHash();
                        $dataHash->sms_id = $queue->id;
                        $dataHash->hash = $hash;
                        $dataHash->save();

                        // BECAUSE CURRENT HASH WAS ADDED AFTER TOTAL HASH COUNT WAS RETRIEVE
                        $hashCount++;

                        // COMMIT
                        $db->createCommand("RELEASE SAVEPOINT sp; COMMIT;")->execute();

                        // IF CURRENT HASH IS LAST - MESSAGE DELIVERED
                        if ($hashCount == $totalSmsChainCount) {
                            $queue->log(SmsLog::LOGEVENT_CALLBACK, $data, [static::getID() => 'message is delivered']);
                            $queue->callbackTolog($queue->executeCallback($message_id));
                            $queue->message->close();
                        } else {
                            $queue->log(SmsLog::LOGEVENT_CALLBACK, $data, [static::getID() => 'chain is delivered']);
                        }
                    } elseif (in_array($status, [Nexmo::STATUS_FAILED, Nexmo::STATUS_EXPIRED, Nexmo::STATUS_REJECTED])) {

                        // PROCESS CURRENT CHAIN
                        $chain->message_status = SmsChain::STATUS_ERROR;
                        $chain->save();

                        // COMMIT
                        $db->createCommand("COMMIT")->execute();

                        // LOG
                        $eventType = (in_array($status, [Nexmo::STATUS_FAILED, Nexmo::STATUS_EXPIRED, Nexmo::STATUS_REJECTED])) ? SmsLog::LOGEVENT_PROVIDER_ERROR : SmsLog::LOGEVENT_CALLBACK;
                        $queue->log($eventType, $data, [static::getID() => 'not delivered message status: '.$status]);
                        $queue->last_failure_reason = json_encode([
                            'action' => 'Nexmo callback',
                            'data' => json_encode($data),
                            'exception' => 'Nexmo reported not delivered message status: '.$status
                        ]);
                    } else {
                        $queue->log(SmsLog::LOGEVENT_CALLBACK, $data, [static::getID() => 'not delivered message status: '.$status]);
                    }
                } else {

                    // COMMIT
                    $db->createCommand("COMMIT")->execute();

                    // LOG
                    $queue->log(SmsLog::LOGEVENT_CALLBACK, $data, [static::getID() => 'Absent record for sms chain']);
                    $queue->last_failure_reason = json_encode([
                        'action' => 'Nexmo callback',
                        'data' => json_encode($data),
                        'exception' => 'Absent record for sms chain'
                    ]);
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();

                // CHECK IF IF WAS DEADLOCK. IF YES - WE NEED REPEAT TRANSACTION FROM SAVEPOINT
                $result = strpos($error, 'Deadlock found when trying to get lock');
                if ($result === false) { // HERE WE NEED === COMPARE

                    // ROLLBACK
                    $db->createCommand("ROLLBACK")->execute();

                    $queue->log(SmsLog::LOGEVENT_INTERNAL_ERROR, ['Error processing webhook from ' . static::getID() => ['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]]);
                    $queue->last_failure_reason = json_encode([
                        'action' => 'Nexmo callback',
                        'data' => json_encode($data),
                        'exception' => json_encode(['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ]);
                } else {
                    $db->createCommand("ROLLBACK TO SAVEPOINT sp")->execute();
                }
            }
        } else {
            $queue->log(SmsLog::LOGEVENT_CALLBACK, $data, [static::getID() => 'Absent chain message id in Nexmo callback']);
            $queue->last_failure_reason = json_encode([
                'action' => 'Nexmo callback',
                'data' => json_encode($data),
                'exception' => 'Absent chain message id in Nexmo callback'
            ]);
        }

        if ($queue->last_failure_reason) {
            $queue->is_sent = false;
            if (!$queue->save()) {
                throw new \Exception(strip_tags(Html::errorSummary($queue)), 500);
            }
        }
    }

    /**
     * @param array $data
     * @param integer $queueId
     * @throws \Exception
     */
    private function createChainFromNexmo(array $data, $queueId)
    {
        if (isset($data['message-id'])) {
            $chain = new SmsChain();
            $chain->sms_id = $queueId;
            $chain->message_id = $data['message-id'];
            $chain->message_data = json_encode($data);
            $chain->message_status = SmsChain::STATUS_QUEUED;
            if (!$chain->save()) {
                throw new \Exception(strip_tags(Html::errorSummary($chain)));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSend($message = null)
    {
        if ($message && $message->patient) {
            if ($message->getMessage()->forceSend) {
                $message->patient->meta->deleteValue(PatientMeta::CELLPHONE_INVALID);
            } else if ($message->patient->meta->getValue(PatientMeta::CELLPHONE_INVALID, false)) {
                $message->last_failure_reason = "SMS is not sent. Phone ". $message->to ." is unroutable\n";
                $message->save();
                return false;
            }
        }
        return parent::beforeSend($message);
    }

}
