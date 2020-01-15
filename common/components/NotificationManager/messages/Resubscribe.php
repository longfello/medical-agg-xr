<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class Resubscribe
 */
class Resubscribe extends BaseMessage
{
    /** @var string */
    public $resubscribeUrl;

    /** @var string */
    public $receiverFullName;

    /** @var bool  */
    public $allowUnsubscribe = false;
}
