<?php
namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;
use common\components\NotificationManager\channels\Email\Email;
use common\components\NotificationManager\channels\SMS\SMS;
use common\models\MedicationReminders;
use common\models\MedicationRemindersConfirmation;
use yii\db\Expression;
use yii\validators\EmailValidator;


/**
 * Class MedicationReminderSecond
 * @package common\components\sms
 */
class MedicationReminderSecond extends BaseMessage
{
    /** @var time */
    public $time;

    /** @var string[] */
    public $medicationList;

    /** @var string */
    public $confirmUrl;


    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function beforeSend()
    {
        $reminder = MedicationReminders::findOne(['reminder_id' => $this->message_id]);
        if (!$reminder || $reminder->is_confirmed) {
            return false;
        }

        $duration = MedicationRemindersConfirmation::DURATION_DEFAULT;
        if ($this->patient->reminderConfirmation->duration) {
            $duration = $this->patient->reminderConfirmation->duration;
        }

        $sendTime = new Expression("NOW() + INTERVAL $duration MINUTE");
        $this->createNotifyCompliance($reminder, $sendTime);

        return parent::beforeSend();
    }

    /**
     * @param MedicationReminders $reminder
     * @param Expression $sendTime
     */
    public function createNotifyCompliance($reminder, $sendTime)
    {
        $patient = $reminder->patient;
        $medicationList = $reminder->getRelatedMedications('medication_text');
        $localReminderTime = date('g:i A', strtotime($reminder->reminder_time) + ($patient->tz) * 3600);

        foreach (['contact1', 'contact2', 'contact3'] as $contactAttr) {
            if ($patient->reminderConfirmation->$contactAttr) {
                $contact = $patient->reminderConfirmation->$contactAttr;
                $channel = ((new EmailValidator())->validate($contact) ? Email::getID() : SMS::getID());
                $message = new MedicationReminderNotifyCompliance([
                    'medicationList' => $medicationList,
                    'patient'    => $patient,
                    'time'       => $localReminderTime,
                    'message_id' => (string) $reminder->reminder_id,
                    'send_time'  => $sendTime,
                ]);
                $message->send($contact, false, $channel);
            }
        }
    }

}
