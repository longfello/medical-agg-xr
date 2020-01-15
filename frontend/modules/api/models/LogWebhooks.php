<?php

namespace frontend\modules\api\models;

/**
 * This is the model class for table "life_log_webhooks".
 *
 * @property string $stripe_event_id
 * @property string $type
 * @property string $received
 * @property string $event
 * @property string $customer_id
 * @property integer $patients_id
 */
class LogWebhooks extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'life_log_webhooks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['stripe_event_id', 'event'], 'required'],
            [['received'], 'safe'],
            [['event'], 'string'],
            [['patients_id'], 'integer'],
            [['stripe_event_id'], 'string', 'max' => 32],
            [['customer_id', 'type'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'stripe_event_id' => 'Stripe Event ID',
            'received' => 'Received',
            'event' => 'Event',
            'customer_id' => 'Customer ID',
            'patients_id' => 'Patients ID',
            'type' => 'Type',
        ];
    }
}
