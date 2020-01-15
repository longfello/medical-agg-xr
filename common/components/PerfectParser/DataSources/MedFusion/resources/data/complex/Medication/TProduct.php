<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 16:23
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Medication;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;

/**
 * Class TProduct
 * @package common\components\PerfectParser
 */
class TProduct extends TComplex
{
    /** @var TCodeableConcept powder | tablets | carton + */
    public $form;

    /** @var TIngredient[]|TArray|null Active or inactive ingredient */
    public $ingredient;

    /** @var TBatch */
    public $batch;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['form', TCodeableConcept::class],
            ['ingredient', [TIngredient::class]],
            ['batch', TBatch::class],
        ];
    }

}