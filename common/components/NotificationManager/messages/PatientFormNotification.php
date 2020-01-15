<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class PatientFormNotification
 */
class PatientFormNotification extends BaseMessage
{
    /** @var string */
    public $message;

    /** @var string */
    public $hospital;

    /** @var string */
    public $callbackNumber;
}
