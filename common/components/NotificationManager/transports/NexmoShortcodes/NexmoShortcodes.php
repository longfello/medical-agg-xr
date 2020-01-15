<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 13:26
 */

namespace common\components\NotificationManager\transports\NexmoShortcodes;

use common\components\NotificationManager\transports\Nexmo\NexmoCommon;

/**
 * Class NexmoShortcodes
 * @package common\components\NotificationManager\transports\NexmoShortcodes
 */
class NexmoShortcodes extends NexmoCommon
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Nexmo using short codes';
    }

    /**
     * @return string
     */
    public static function getID()
    {
        return 'nexmo-shortcode';
    }

}
