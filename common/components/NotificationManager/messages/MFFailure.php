<?php
/**
 * Created by PhpStorm.
 * User: mystik
 * Date: 09.08.17
 * Time: 16:25
 */

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class MFFailure
 */
class MFFailure extends BaseMessage
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
}
