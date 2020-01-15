<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 13:25
 */

namespace common\components\NotificationManager\transports\NexmoLongcodes;

use common\components\NotificationManager\transports\Nexmo\NexmoCommon;

/**
 * Class NexmoLongcodes
 * @package common\components\NotificationManager\transports\NexmoLongcodes
 */
class NexmoLongcodes extends NexmoCommon
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Nexmo using long codes';
    }

    /**
     * @return string
     */
    public static function getID()
    {
        return 'nexmo-longcode';
    }

}
