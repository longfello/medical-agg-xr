<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 02.08.18
 * Time: 17:23
 */

namespace common\components\PerfectParser\DataSources\MedFusion\common;

use common\components\AWS;

/**
 * Class AWSParam
 * @package common\components\PerfectParser
 */
class AWSParam extends \common\components\AWSParam
{
    /**
     * @inheritdoc
     */
    public $bucket = AWS::BUCKET_MEDFUSION;
    /**
     * @inheritdoc
     */
    public $cachePrefix = 'mf-aws-param';
}