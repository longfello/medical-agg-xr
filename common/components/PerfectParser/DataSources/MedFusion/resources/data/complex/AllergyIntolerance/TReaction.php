<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 07.02.18
 * Time: 12:52
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\AllergyIntolerance;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAnnotation;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class TReaction
 * @package common\components\PerfectParser
 */
class TReaction extends TComplex
{
    /**
     *
     */
    const SEVERITY_SEVERE = 'severe';
    /**
     *
     */
    const SEVERITY_MODERATE = 'moderate';

    /** @var TCodeableConcept[] Specific substance or pharmaceutical product considered to be responsible for event */
    public $substance;

    /** @var TCodeableConcept[] Clinical symptoms/signs associated with the Event */
    public $manifestation;

    /** @var TString Description[] of the event as a whole */
    public $description;

    /** @var TDateTime Date(/time) when manifestations showed */
    public $onset;

    /** @var TCode mild | moderate | severe (of event as a whole) */
    public $severity;

    /** @var TCodeableConcept How the subject was exposed to the substance */
    public $exposureRoute;

    /** @var TAnnotation Text about event not captured in other fields */
    public $note;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['substance', [TSubstance::class]],
            [['manifestation', 'exposureRoute'], [TCodeableConcept::class]],
            ['description', TString::class],
            ['onset', TDateTime::class],
            ['severity', TCode::class, self::REQUIRED],
            ['note', TAnnotation::class],
        ];
    }
}