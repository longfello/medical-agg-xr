<?php
namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class UnblockMsg
 */
class UnblockMsg extends BaseMessage
{
    /** @var string */
    public $unblockUrl;
    /** @var bool  */
    public $allowUnsubscribe = false;
}
