<?php
namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class CcdaFailure
 */
class CcdaFailure extends BaseMessage
{
    /**
     * @const integer
     */
    const TRACE_LEVEL = 3;

    /** @var string */
    public $errors;

    /** @var string */
    public $slid;

    /** @var string */
    public $server;

    /** @var string */
    public $href2file;

    /** @var string */
    public $lastLog;

    /** @var bool */
    public $allowUnsubscribe = false;
}
