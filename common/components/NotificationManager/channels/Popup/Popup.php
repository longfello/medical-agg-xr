<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 13:10
 */

namespace common\components\NotificationManager\channels\Popup;


use common\components\NotificationManager\components\BaseChannel;

/**
 * Class PopupChannel
 * @package common\components\NotificationManager\channels\popup
 */
class Popup extends BaseChannel
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Popup';
    }

    /**
     * @return string
     */
    public static function getID()
    {
        return 'popup';
    }

    /**
     * Return Contact Type. Basicaly - as constant of MessagesBlocking::CONTACT_TYPE_PHONE or MessagesBlocking::CONTACT_TYPE_EMAIL
     * @return string|false
     */
    public function getContactType()
    {
        return false;
    }
}