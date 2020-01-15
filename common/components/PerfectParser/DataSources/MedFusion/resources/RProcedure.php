<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 17:58
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Procedure\TFocalDevice;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Procedure\TPerformer;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAnnotation;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TNarrative;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\labs\RLab;
use common\components\PerfectParser\Resources\REmergencyContacts;

/**
 * Class RProcedure
 * @package common\components\PerfectParser
 */
class RProcedure extends RResource
{
    /** @var TIdentifier[] Unique Id for this particular observation */
    public $identifier;

    /** @var TString|null  */
    public $resourceType;

    /** @var TReference Who the procedure was performed on */
    public $subject;

    /** @var TCode in-progress | aborted | completed | entered-in-error */
    public $status;

    /** @var TCodeableConcept Classification of the procedure */
    public $category;

    /** @var TCodeableConcept Identification of the procedure */
    public $code;

    /** @var TBoolean True if procedure was not performed as scheduled */
    public $notPerformed;

    /** @var TCodeableConcept Reason procedure was not performed */
    public $reasonNotPerformed;

    /** @var TCodeableConcept[] Target body sites */
    public $bodySite;

    /** @var TCodeableConcept Reason procedure performed */
    public $reasonCodeableConcept;

    /** @var TReference Reason procedure performed */
    public $reasonReference;

    /** @var TPerformer[] The people who performed the procedure */
    public $performer;

    /** @var TDateTime Date/Period the procedure was performed */
    public $performedDateTime;

    /** @var TPeriod Date/Period the procedure was performed */
    public $performedPeriod;

    /** @var TReference The encounter associated with the procedure */
    public $encounter;

    /** @var TReference Where the procedure happened */
    public $location;

    /** @var TCodeableConcept The result of procedure */
    public $outcome;

    /** @var TReference Any report resulting from the procedure */
    public $report;

    /** @var TCodeableConcept Complication following the procedure */
    public $complication;

    /** @var TCodeableConcept Instructions for follow up */
    public $followUp;

    /** @var TReference A request for this procedure */
    public $request;

    /** @var TAnnotation Additional information about the procedure */
    public $notes;

    /** @var TFocalDevice Device changed in procedure */
    public $focalDevice;

    /** @var TReference Items used during procedure */
    public $used;

    /** @var RResource[]|RProcedure[]|RPractitioner[]|RPatient[]|ROrganization[]|RLab|RMedicationStatement[]|RMedicationOrder[]|RMedicationDispense[]|RMedicationAdministration[]|RMedication[]|RImmunization[]|REmergencyContacts[]|RDevice[]|RCondition[]|RBundle[]|RAppointment[]|RAllergyIntolerance[] sub-resources */
    public $contained;

    /** @var TNarrative */
    public $text;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['identifier', [TIdentifier::class]],
            ['resourceType', TString::class],
            ['subject', TReference::class],
            ['status', TCode::class],
            ['category', TCodeableConcept::class],
            ['code', TCodeableConcept::class],
            ['notPerformed', TBoolean::class],
            ['reasonNotPerformed', TCodeableConcept::class],
            ['bodySite', [TCodeableConcept::class]],
            ['reasonCodeableConcept', TCodeableConcept::class],
            ['reasonReference', TReference::class],
            ['performer', [TPerformer::class]],
            ['performedDateTime', TDateTime::class],
            ['performedPeriod', TPeriod::class],
            ['encounter', TReference::class],
            ['location', TReference::class],
            ['outcome', TCodeableConcept::class],
            ['report', TReference::class],
            ['complication', TCodeableConcept::class],
            ['followUp', TCodeableConcept::class],
            ['request', TReference::class],
            ['focalDevice', TFocalDevice::class],
            ['used', TReference::class],
            ['text', TNarrative::class],
            ['contained', [RResource::class]],
        ];
    }

}