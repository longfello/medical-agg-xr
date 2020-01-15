<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class AdminAnnounce
 * @package common\components\mail
 */
class AdminAnnounce extends BaseMessage
{
    /** @var string */
    public $subject;

    /** @var string */
    public $content;

}
