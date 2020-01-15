<?php
namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class MedicationReminderNotifyCompliance
 * @package common\components\NotificationManager\messages
 */
class MedicationReminderNotifyCompliance extends BaseMessage
{
    /** @var string */
    public $time;

    /** @var string[] */
    public $medicationList;

}
