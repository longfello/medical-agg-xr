<?php
namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class MFFailureConnection
 */
class MFFailureConnection extends BaseMessage
{
    /** @var string */
    public $errorText;

    /** @var string */
    public $slid;

    /** @var string */
    public $server;
}
