<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 15.01.19
 * Time: 17:38
 */

namespace common\components\NotificationManager\messages;


use common\components\NotificationManager\components\BaseMessage;

/**
 * Class MFNowAccepted
 * @package common\components\NotificationManager\messages
 */
class MFNowAccepted extends BaseMessage
{
    /** @var string */
    public $practice_name;
}