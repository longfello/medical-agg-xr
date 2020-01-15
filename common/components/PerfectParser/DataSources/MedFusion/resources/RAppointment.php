<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:02
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Appointment\TParticipant;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TInstant;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TPositiveInt;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUnsignedInt;
use common\components\PerfectParser\DataSources\MedFusion\resources\labs\RLab;

/**
 * Class RAppointment
 * @package common\components\PerfectParser
 */
class RAppointment extends RResource
{

    /** @var TIdentifier[] External ids for this item */
    public $identifier;

    /** @var TString|null  */
    public $resourceType;

    /** @var RResource[]|RProcedure[]|RPractitioner[]|RPatient[]|ROrganization[]|RLab|RMedicationStatement[]|RMedicationOrder[]|RMedicationDispense[]|RMedicationAdministration[]|RMedication[]|RImmunization[]|REmergencyContact[]|RDevice[]|RCondition[]|RBundle[]|RAppointment[]|RAllergyIntolerance[] sub-resources */
    public $contained;

    /** @var TCode proposed | pending | booked | arrived | fulfilled | cancelled | noshow */
    public $status;

    /** @var TCodeableConcept The type of appointment that is being booked */
    public $type;

    /** @var TCodeableConcept Reason this appointment is scheduled */
    public $reason;

    /** @var TUnsignedInt Used to make informed decisions if needing to re-prioritize */
    public $priority;

    /** @var TString Shown on a subject line in a meeting request, or appointment list */
    public $description;

    /** @var TInstant When appointment is to take place */
    public $start;

    /** @var TInstant When appointment is to conclude */
    public $end;

    /** @var TPositiveInt 	Can be less than start/end (e.g. estimate) */
    public $minutesDuration;

    /** @var TReference If provided, then no schedule and start/end values MUST match slot */
    public $slot;

    /** @var TString Additional comments */
    public $comment;

    /** @var TParticipant[] Participants involved in appointment */
    public $participant;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['identifier', [TIdentifier::class]],
            ['resourceType', TString::class],
            ['contained', [RResource::class]],
            ['status', TCode::class],
            ['type', TCodeableConcept::class],
            ['reason', TCodeableConcept::class],
            ['priority', TUnsignedInt::class],
            ['description', TString::class],
            ['minutesDuration', TPositiveInt::class],
            ['slot', TReference::class],
            ['comment', TString::class],
            ['participant', [TParticipant::class]],
            [['start', 'end'], TInstant::class],
        ];
    }
}