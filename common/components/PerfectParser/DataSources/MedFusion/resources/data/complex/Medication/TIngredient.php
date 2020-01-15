<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 16:33
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Medication;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRatio;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;

/**
 * Class TIngredient
 * @package common\components\PerfectParser
 */
class TIngredient extends TComplex
{
    /** @var TReference The product contained */
    public $item;

    /** @var TRatio Quantity of ingredient present */
    public $amount;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['item', TReference::class],
            ['amount', TRatio::class],
        ];
    }

}