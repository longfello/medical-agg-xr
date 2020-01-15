<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 14:06
 */

use \common\components\NotificationManager\transports\File\File;
use \common\components\NotificationManager\transports\SMTP\SMTP;
return [
    [
        'class'   => File::class,
        'priority' => 20,
        'enabled' => true,
    ],
    [
        'class'   => SMTP::class,
        'priority' => 10,
        'enabled' => true,
    ]
];
