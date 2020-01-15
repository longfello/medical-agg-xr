<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 13.03.18
 * Time: 11:59
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\RMedicationOrder;


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
 * Class TDosageInstruction
 * @package common\components\PerfectParser
 */
class TDosageInstruction extends TComplex
{
    /**
     *
     */
    const FORMAT_TIMING = '{timing()}';

    /** @var TString|null Dosage instructions expressed as text */
    public $text;

    /** @var TCodeableConcept|null Supplemental instructions - e.g. "with meals" */
    public $additionalInstructions;

    /** @var TTiming|null When medication should be administered */
    public $timing;

    /** @var TBoolean|null Take "as needed" (for x) */
    public $asNeededBoolean;

    /** @var TCodeableConcept|null Take "as needed" (for x) */
    public $asNeededCodeableConcept;

    /** @var TCodeableConcept|null Body site to administer to SNOMED CT Anatomical Structure for Administration Site Codes */
    public $siteCodeableConcept;

    /** @var TCodeableConcept|null How drug should enter body SNOMED CT Route Codes */
    public $route;

    /** @var TCodeableConcept|null Technique for administering medication */
    public $method;

    /** @var TReference|null Body site to administer to SNOMED CT Anatomical Structure for Administration Site Codes */
    public $siteReference;

    /** @var TRange|null Amount of medication per dose */
    public $doseRange;

    /** @var TSimpleQuantity|null Amount of medication per dose */
    public $doseQuantity;

    /** @var TRatio|null Amount of medication per unit of time */
    public $rateRatio;

    /** @var TRange|null Amount of medication per unit of time */
    public $rateRange;

    /** @var TRatio|null Upper limit on medication per unit of time */
    public $maxDosePerPeriod;

    /**
     * @inheritdoc
     */
    public function structure()
    {
        return [
            ['text', TString::class],
            [['additionalInstructions', 'asNeededCodeableConcept', 'siteCodeableConcept', 'route', 'method'], TCodeableConcept::class],
            ['timing', TTiming::class],
            ['asNeededBoolean', TBoolean::class],
            ['siteReference', TReference::class],
            ['doseQuantity', TSimpleQuantity::class],
            [['doseRange', 'rateRange'], TRange::class],
            [['rateRatio', 'maxDosePerPeriod'], TRatio::class],
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