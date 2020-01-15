<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 17:28
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Immunization;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TPositiveInt;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class TVaccinationProtocol
 * @package common\components\PerfectParser
 */
class TVaccinationProtocol extends TComplex
{
    /** @var TPositiveInt Dose number within series */
    public $doseSequence;

    /** @var TString  Details of vaccine protocol */
    public $description;

    /** @var TReference Who is responsible for protocol */
    public $authority;

    /** @var TString Name of vaccine series */
    public $series;

    /** @var TPositiveInt Recommended number of doses for immunity */
    public $seriesDoses;

    /** @var TCodeableConcept Disease immunized against */
    public $targetDisease;

    /** @var TCodeableConcept Indicates if dose counts towards immunity */
    public $doseStatus;

    /** @var TCodeableConcept Why dose does (not) count */
    public $doseStatusReason;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['doseSequence', TPositiveInt::class],
            ['description', TString::class],
            ['authority', TReference::class],
            ['series', TString::class],
            ['seriesDoses', TPositiveInt::class],
            ['targetDisease', TCodeableConcept::class],
            ['doseStatus', TCodeableConcept::class],
            ['doseStatusReason', TCodeableConcept::class],
        ];
    }

}