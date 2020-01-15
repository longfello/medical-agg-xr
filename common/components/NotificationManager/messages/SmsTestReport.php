<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 15.01.19
 * Time: 14:44
 */

namespace common\components\NotificationManager\messages;


use common\components\NotificationManager\components\BaseMessage;

/**
 * Class SmsTestReport
 * @package common\components\NotificationManager\messages
 */
class SmsTestReport extends BaseMessage
{
    /** @var string */
    public $test_name;
    /** @var array */
    public $times;
    /** @var integer */
    public $test_id;
    /** @var string */
    public $body;
}