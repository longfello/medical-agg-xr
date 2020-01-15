<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 14:06
 */

use common\components\NotificationManager\transports\NexmoLongcodes\NexmoLongcodes;
use common\components\NotificationManager\transports\NexmoShortcodes\NexmoShortcodes;

return [
    [
        'class'   => NexmoLongcodes::class,
        'enabled' => true,
        'priority'=> 10,
    ],
    [
        'class'   => NexmoShortcodes::class,
        'enabled' => false,
        'priority'=> 20,
    ],
];