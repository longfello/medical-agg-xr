<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 13:29
 */

use \common\components\NotificationManager\channels\Email\Email;
use \common\components\NotificationManager\channels\Popup\Popup;
use \common\components\NotificationManager\channels\SMS\SMS;

return [
    'channels' => [
        [
            'class' => Email::class,
            'priority' => 10,
            'enabled' => true,
        ],
        [
            'class' => Popup::class,
            'priority' => 30,
            'enabled' => false,
        ],
        [
            'class' => SMS::class,
            'priority' => 20,
            'enabled' => true,
        ],
    ],
];