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
 * Class LogRotationFailure
 */
class LogRotationFailure extends BaseMessage
{
    /**
     * @const integer
     */
    const TRACE_LEVEL = 3;

    /** @var string */
    public $table;

    /** @var string */
    public $errorText;
}
