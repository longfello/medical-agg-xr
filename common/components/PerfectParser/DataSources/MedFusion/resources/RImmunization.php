<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:02
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Immunization\TExplanation;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Immunization\TReaction;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Immunization\TVaccinationProtocol;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAnnotation;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TNarrative;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TSimpleQuantity;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDate;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class RImmunization
 * @package common\components\PerfectParser
 */
class RImmunization extends RResource
{
    /**
     * entered-in-error status
     */
    const STATUS_ENTERED_IN_ERROR = 'entered-in-error';

    /** @var TIdentifier[] Unique Id for this particular observation */
    public $identifier;

    /** @var TString|null  */
    public $resourceType;

    /** @var TCode in-progress | on-hold | completed | entered-in-error | stopped */
    public $status;

    /** @var TDateTime Vaccination administration date */
    public $date;

    /** @var TCodeableConcept Vaccine product administered */
    public $vaccineCode;

    /** @var TReference Who was immunized */
    public $patient;

    /** @var TBoolean Flag for whether immunization was given */
    public $wasNotGiven;

    /** @var TBoolean Indicates a self-reported record */
    public $reported;

    /** @var TReference 	Who administered vaccine */
    public $performer;

    /** @var TReference Who ordered vaccination */
    public $requester;

    /** @var TReference Encounter administered as part of */
    public $encounter;

    /** @var TReference Vaccine manufacturer */
    public $manufacturer;

    /** @var TReference Where vaccination occurred */
    public $location;

    /** @var TString Vaccine lot number */
    public $lotNumber;

    /** @var TDate Vaccine expiration date */
    public $expirationDate;

    /** @var TCodeableConcept Body site vaccine was administered */
    public $site;

    /** @var TCodeableConcept How vaccine entered body */
    public $route;

    /** @var TSimpleQuantity Amount of vaccine administered */
    public $doseQuantity;

    /** @var TAnnotation Vaccination notes */
    public $note;

    /** @var TExplanation Administration/non-administration reasons */
    public $explanation;

    /** @var TReaction Details of a reaction that follows immunization */
    public $reaction;

    /** @var TVaccinationProtocol What protocol was followed */
    public $vaccinationProtocol;

    /** @var TNarrative */
    public $text;

    /** @var RResource[]|null  */
    public $contained;


    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['identifier', [TIdentifier::class]],
            ['resourceType', TString::class],
            ['status', TCode::class],
            ['date', TDateTime::class],
            ['vaccineCode', TCodeableConcept::class],
            ['patient', TReference::class],
            ['wasNotGiven', TBoolean::class],
            ['reported', TBoolean::class],
            [['performer', 'requester', 'encounter', 'manufacturer', 'location'], TReference::class],
            ['lotNumber', TString::class],
            ['expirationDate', TDate::class],
            ['site', TCodeableConcept::class],
            ['route', TCodeableConcept::class],
            ['doseQuantity', TSimpleQuantity::class],
            ['note', TAnnotation::class],
            ['explanation', TExplanation::class],
            ['reaction', TReaction::class],
            ['vaccinationProtocol', TVaccinationProtocol::class],
            ['text', TNarrative::class],
            ['contained', [RResource::class]],
        ];
    }
}