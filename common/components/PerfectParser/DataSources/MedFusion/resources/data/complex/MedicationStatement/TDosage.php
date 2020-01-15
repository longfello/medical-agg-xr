<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 15:18
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\MedicationStatement;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRange;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRatio;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TSimpleQuantity;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TTiming;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;
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

    /** @var TString Reported dosage information */
    public $text;

    /** @var TTiming When/how often was medication taken */
    public $timing;

    /** @var TBoolean Take "as needed" (for x) */
    public $asNeededBoolean;

    /** @var TCodeableConcept Take "as needed" (for x) */
    public $asNeededCodeableConcept;

    /** @var TCodeableConcept  Where (on body) medication is/was administered */
    public $siteCodeableConcept;

    /** @var TReference Where (on body) medication is/was administered */
    public $siteReference;

    /** @var TCodeableConcept How the medication entered the body */
    public $route;

    /** @var TCodeableConcept Technique used to administer medication */
    public $method;

    /** @var TSimpleQuantity Amount administered in one dose */
    public $quantityQuantity;

    /** @var TRange Amount administered in one dose */
    public $quantityRange;

    /** @var TRatio Dose quantity per unit of time */
    public $rateRatio;

    /** @var TRange Dose quantity per unit of time */
    public $rateRange;

    /** @var TRatio Maximum dose that was consumed per unit of time */
    public $maxDosePerPeriod;

    /**
     * @inheritdoc
     */
    public function structure()
    {
        return [
            ['text', TString::class],
            ['timing', TTiming::class],
            ['asNeededBoolean', TBoolean::class],
            ['asNeededCodeableConcept', TCodeableConcept::class],
            ['siteCodeableConcept', TCodeableConcept::class],
            ['siteReference', TReference::class],
            ['route', TCodeableConcept::class],
            ['method', TCodeableConcept::class],
            ['quantityQuantity', TSimpleQuantity::class],
            ['quantityRange', TRange::class],
            ['rateRatio', TRatio::class],
            ['rateRange', TRange::class],
            ['maxDosePerPeriod', TRatio::class],
        ];
    }

    /**
     * @return bool|null|string
     */
    public function format_timing(){
        if ($this->asNeededBoolean && $this->asNeededBoolean->getValue()) return "as needed";
        if ($this->timing) return $this->timing->format();
        return null;
    }

}