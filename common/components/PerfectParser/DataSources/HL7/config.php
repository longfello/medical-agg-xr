<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 01.06.18
 * Time: 9:28
 */

use \common\components\PerfectParser\Common\Prototype\DataSource;

return [
    'name' => 'HL7',
    'restMethodsAvailable' => [
        DataSource::METHOD_PRE_ENROLLMENT
    ],
    'restTestMethodsAvailable' => [
        DataSource::METHOD_PRE_ENROLLMENT
    ],
];