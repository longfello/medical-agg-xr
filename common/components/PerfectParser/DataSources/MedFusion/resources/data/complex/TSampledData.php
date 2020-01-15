<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:51
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDecimal;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TPositiveInt;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class TSampledData
 * @package common\components\PerfectParser
 */
class TSampledData extends TComplex
{
    /**
     * @var TSimpleQuantity
     */
    public $origin;

    /**
     * @var TDecimal
     */
    public $period;

    /**
     * @var TDecimal
     */
    public $factor;

    /**
     * @var TDecimal
     */
    public $lowerLimit;

    /**
     * @var TDecimal
     */
    public $upperLimit;

    /**
     * @var TPositiveInt
     */
    public $dimensions;

    /**
     * @var TString
     */
    public $data;

    /**
     * @inheritdoc
     */
    public function structure()
    {
        return [
            [['origin'], TSimpleQuantity::class],
            [['period', 'factor', 'lowerLimit', 'upperLimit'], TDecimal::class],
            [['dimensions'], TPositiveInt::class],
            [['data'], TString::class]
        ];
    }
}