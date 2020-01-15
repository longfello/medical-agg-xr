<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 15:18
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\MedicationAdministration;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRange;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRatio;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TSimpleQuantity;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class TDosage
 * @package common\components\PerfectParser
 */
class TDosage extends TComplex
{
    /**
     *
     */
    const FORMAT_TIMING = '{timing()}';

    /** @var TString | null Dosage Instructions */
    public $text;

    /** @var TCodeableConcept | null  Body site administered to */
    public $siteCodeableConcept;

    /** @var TReference | null Body site administered to */
    public $siteReference;

    /** @var TCodeableConcept | null Path of substance into body */
    public $route;

    /** @var TCodeableConcept | null How drug was administered */
    public $method;

    /** @var TSimpleQuantity | null Amount administered in one dose */
    public $quantity;

    /** @var TRatio | null Dose quantity per unit of time */
    public $rateRatio;

    /** @var TRange | null Dose quantity per unit of time */
    public $rateRange;


    /**
     * @inheritdoc
     */
    public function structure()
    {
        return [
            ['text', TString::class],
            ['siteCodeableConcept', TCodeableConcept::class],
            ['siteReference', TReference::class],
            ['route', TCodeableConcept::class],
            ['method', TCodeableConcept::class],
            ['quantity', TSimpleQuantity::class],
            ['rateRatio', TRatio::class],
            ['rateRange', TRange::class],
        ];
    }

    /**
     * @return mixed|null|string
     */
    public function format_timing(){
        if ($this->text) return $this->text->getValue();
        return null;
    }

}
