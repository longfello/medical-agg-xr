<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class RemotePendingSms
 * @package common\components\NotificationManager\messages
 */
class RemotePendingSms extends BaseMessage
{
    /** @var string */
    public $code;

    /** @var string */
    public $patientName;

}
