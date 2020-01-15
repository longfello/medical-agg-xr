<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.11.2018
 * Time: 3:13
 */

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;


/**
 * Class ConfirmPhoneEmail
 * @package common\components\NotificationManager\messages
 */
class ConfirmPhoneEmail extends BaseMessage
{
    /** @var string */
    public $validateUrl;
}
