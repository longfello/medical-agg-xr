<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 01.06.18
 * Time: 9:28
 */
use \common\components\PerfectParser\Common\Prototype\DataSource;

return [
    'name' => 'EMR',
    'restMethodsAvailable' => [
        DataSource::METHOD_FRAME,
    ],
    'restTestMethodsAvailable' => [
        DataSource::METHOD_FRAME,
    ],
];