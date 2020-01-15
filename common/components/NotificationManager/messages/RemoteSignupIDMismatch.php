<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;


/**
 * Class RemoteSignupIDMismatch
 * @package common\components\NotificationManager\messages
 */
class RemoteSignupIDMismatch extends BaseMessage
{
    /** @var string */
    public $remoteUrl;
}
