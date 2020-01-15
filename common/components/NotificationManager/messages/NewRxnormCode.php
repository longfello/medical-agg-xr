<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 15.01.19
 * Time: 17:17
 */

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class NewRxnormCode
 * @package common\components\NotificationManager\messages
 */
class NewRxnormCode extends BaseMessage
{
    /** @var string */
    public $code;
}