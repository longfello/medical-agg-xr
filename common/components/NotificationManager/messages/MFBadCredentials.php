<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class MFBadCredentials
 */
class MFBadCredentials extends BaseMessage
{
    /** @var string */
    public $practice_name;
}
