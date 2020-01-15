<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.11.2018
 * Time: 12:44
 */

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;


/**
 * Class RejectPhoneEmail
 * @package common\components\NotificationManager\messages
 */
class RejectPhoneEmail extends BaseMessage
{
    /** @var string */
    public $cellPhone;

    /** @var bool  */
    public $allowUnsubscribe = false;
}
