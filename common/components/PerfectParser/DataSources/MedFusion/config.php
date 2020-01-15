<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 01.06.18
 * Time: 9:28
 */

use \common\components\PerfectParser\Common\Prototype\DataSource;
use \common\components\PerfectParser\DataSources\MedFusion\common\AWSParam;

$param = new AWSParam();

return [
    'name'         => 'Med Fusion',
    'location'     => $param->get('medfusion.location'),
    'customerUUID' => $param->get('medfusion.customer.uuid'),
    'apiKey'       => $param->get('medfusion.api.key'),
    'clientID'     => $param->get('medfusion.client.id'),
    'clientSecret' => $param->get('medfusion.client.secret'),
    'restMethodsAvailable' => [
        DataSource::METHOD_MED_INFO,
        DataSource::METHOD_CHECK_UPDATE,
        DataSource::METHOD_MANUAL_UPDATE,
    ],
    'restTestMethodsAvailable' => [
        DataSource::METHOD_MED_INFO,
        DataSource::METHOD_CHECK_UPDATE
    ],
    'processAllPractices' => true,
];
