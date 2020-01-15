<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 16:38
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Medication;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TSimpleQuantity;

/**
 * Class TContent
 * @package common\components\PerfectParser
 */
class TContent extends TComplex
{
    /** @var TReference A product in the package */
    public $item;

    /** @var TSimpleQuantity Quantity present in the package */
    public $amount;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['item', TReference::class],
            ['amount', TSimpleQuantity::class],
        ];
    }

}