<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 14:06
 */

use \common\components\NotificationManager\transports\popup\Popup;

return [
    [
        'class'   => Popup::class,
        'enabled' => false,
    ]
];