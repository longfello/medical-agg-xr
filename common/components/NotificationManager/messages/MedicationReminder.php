<?php
namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;
use common\components\NotificationManager\channels\SMS\SMS;
use common\models\MedicationReminders;
use common\models\MedicationRemindersConfirmation;
use common\models\PatientMeta;
use yii\db\Expression;

/**
 * Class MedicationReminder
 * @package common\components\NotificationManager\messages
 */
class MedicationReminder extends BaseMessage
{
    /** @var string */
    public $text;

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
        $patient = $this->patient;
        $reminder = MedicationReminders::findOne(['reminder_id' => $this->message_id]);
        if ($reminder) {
            $reminder->resetConfirmation();
        } else {
            return false;
        }
        /** @var MedicationReminders $reminder */

        $medicationList = $reminder->getRelatedMedications('medication_text');
        $duration = MedicationRemindersConfirmation::DURATION_DEFAULT;
        if ($patient->reminderConfirmation->duration) {
            $duration = $patient->reminderConfirmation->duration;
        }

        if ($this->patient->meta->getValue(PatientMeta::SECOND_REMINDER_SMS, 0)) {
            $reminderSecond = new MedicationReminderSecond([
                'message_id'     => (string) $reminder->reminder_id,
                'send_time'      => new Expression("NOW() + INTERVAL $duration MINUTE"),
                'medicationList' => $medicationList,
                'time'           => date('g:i A', strtotime($reminder->reminder_time) + ($patient->tz)*3600),
                'confirmUrl'     => $this->confirmUrl,
            ]);

            if ($reminderSecond->send($patient, false, SMS::getID())) {
                return parent::beforeSend();
            }
            return false;
        } else {
            $sendTime = new Expression("NOW() + INTERVAL ".($duration*2)." MINUTE");
            (new MedicationReminderSecond())->createNotifyCompliance($reminder, $sendTime);
            return parent::beforeSend();
        }
    }

}
