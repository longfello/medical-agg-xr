<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 13:09
 */

namespace common\components\NotificationManager\channels\Email;


use common\components\NotificationManager\components\BaseChannel;
use common\models\MessagesBlocking;

/**
 * Class EmailChannel
 * @package common\components\NotificationManager\channels\email
 */
class Email extends BaseChannel
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Email';
    }

    /**
     * @return string
     */
    public static function getID()
    {
        return 'email';
    }

    /**
     * Return Contact Type. Basicaly - as constant of MessagesBlocking::CONTACT_TYPE_PHONE or MessagesBlocking::CONTACT_TYPE_EMAIL
     * @return string
     */
    public function getContactType()
    {
        return MessagesBlocking::CONTACT_TYPE_EMAIL;
    }
}
