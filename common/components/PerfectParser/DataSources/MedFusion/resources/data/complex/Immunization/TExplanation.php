<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 17:10
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Immunization;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;

/**
 * Class TExplanation
 * @package common\components\PerfectParser
 */
class TExplanation extends TComplex
{
    /** @var TCodeableConcept Why immunization occurred */
    public $reason;

    /** @var TCodeableConcept Why immunization did not occur */
    public $reasonNotGiven;


    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['reason', [TCodeableConcept::class]],
            ['reasonNotGiven', [TCodeableConcept::class]],
        ];
    }

}