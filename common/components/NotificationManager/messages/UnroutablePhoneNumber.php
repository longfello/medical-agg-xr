<?php
namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class UnroutablePhoneNumber
 */
class UnroutablePhoneNumber extends BaseMessage
{
    /** @var string */
    public $phoneNumber;
}
