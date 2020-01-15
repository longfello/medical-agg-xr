<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class EmergencyContactDecline
 */
class EmergencyContactDecline extends BaseMessage
{
    /** @var string */
    public $comment;

    /** @var string */
    public $contact;

    /** @var string */
    public $type;
}
