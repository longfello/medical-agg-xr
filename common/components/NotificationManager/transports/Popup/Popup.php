<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 13:26
 */

namespace common\components\NotificationManager\transports\Popup;


use common\components\NotificationManager\components\BaseTransport as BaseTransportAlias;

/**
 * Class PopupTransport
 * @package common\components\NotificationManager\transports\Popup
 */
class Popup extends BaseTransportAlias
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Popup message to user';
    }

    /**
     * @return string
     */
    public static function getID()
    {
        return 'popup';
    }

    /**
     * @param $message
     * @return bool|string
     */
    public function __send($message)
    {
        return true;
    }
}
