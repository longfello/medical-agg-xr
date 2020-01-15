<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 13:10
 */

namespace common\components\NotificationManager\channels\SMS;


use common\components\NotificationManager\components\BaseChannel;
use common\models\MessagesBlocking;

/**
 * Class SmsChannel
 * @package common\components\NotificationManager\channels\sms
 */
class SMS extends BaseChannel
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'SMS';
    }

    /**
     * @return string
     */
    public static function getID()
    {
        return 'sms';
    }

    /**
     * Return Contact Type. Basicaly - as constant of MessagesBlocking::CONTACT_TYPE_PHONE or MessagesBlocking::CONTACT_TYPE_EMAIL
     * @return string
     */
    public function getContactType()
    {
        return MessagesBlocking::CONTACT_TYPE_PHONE;
    }
}
