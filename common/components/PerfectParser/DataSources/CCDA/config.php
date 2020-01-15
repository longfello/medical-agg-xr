<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 01.06.18
 * Time: 9:28
 */
use \common\components\PerfectParser\Common\Prototype\DataSource;

return [
    'name' => 'CDDA',
    'restMethodsAvailable' => [
        DataSource::METHOD_MED_INFO
    ],
    'restTestMethodsAvailable' => [
        DataSource::METHOD_MED_INFO
    ],
    'processAllPractices' => false,
];
