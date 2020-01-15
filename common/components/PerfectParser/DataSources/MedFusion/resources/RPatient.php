<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 17:58
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Patient\TAnimal;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Patient\TCommunication;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Patient\TContact;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TExtension;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAddress;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAttachment;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TContactPoint;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\THumanName;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDate;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TInteger;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\labs\RLab;

/**
 * Class RPatient
 * @package common\components\PerfectParser
 */
class RPatient extends RResource
{
    /** @var TString[]|TArray|null */
    public $id;

    /** @var TString|null  */
    public $resourceType;

    /** @var TIdentifier[]|null An identifier for this patient */
    public $identifier;

    /** @var TBoolean Whether this patient's record is in active use */
    public $active;

    /** @var THumanName[] A name associated with the patient */
    public $name;

    /** @var TContactPoint[] A contact detail for the individual */
    public $telecom;

    /** @var TCode male | female | other | unknown */
    public $gender;

    /** @var TDate The date on which the practitioner was born */
    public $birthDate;

    /** @var TAttachment Image of the person */
    public $photo;

    /** @var TBoolean Indicates if the individual is deceased or not */
    public $deceasedBoolean;

    /** @var TDateTime Indicates if the individual is deceased or not */
    public $deceasedDateTime;

    /** @var TAddress[] Addresses for the individual */
    public $address;

    /** @var TCodeableConcept Marital (civil) status of a patient */
    public $maritalStatus;

    /** @var TBoolean Whether patient is part of a multiple birth */
    public $multipleBirthBoolean;

    /** @var TInteger Whether patient is part of a multiple birth */
    public $multipleBirthInteger;

    /** @var TContact[] A contact party (e.g. guardian, partner, friend) for the patient SHALL at least contain a contact's details or a reference to an organization */
    public $contact;

    /** @var TAnimal This patient is known to be an animal (non-human) */
    public $animal;

    /** @var TCommunication[] A list of Languages which may be used to communicate with the patient about his or her health */
    public $communication;

    /** @var TExtension[]  */
    public $extension;

    /** @var RResource[]|RProcedure[]|RPractitioner[]|RPatient[]|ROrganization[]|RLab|RMedicationStatement[]|RMedicationOrder[]|RMedicationDispense[]|RMedicationAdministration[]|RMedication[]|RImmunization[]|REmergencyContact[]|RDevice[]|RCondition[]|RBundle[]|RAppointment[]|RAllergyIntolerance[] sub-resources */
    public $contained;

    /** @var TReference  */
    public $managingOrganization;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['id', [TString::class]],
            ['resourceType', TString::class],
            ['identifier', [TIdentifier::class]],
            ['active', TBoolean::class],
            ['name', [THumanName::class]],
            ['telecom', [TContactPoint::class]],
            ['gender', TCode::class],
            ['birthDate', TDate::class],
            ['photo', TAttachment::class],
            ['deceasedBoolean', TBoolean::class],
            ['deceasedDateTime', TDateTime::class],
            ['address', [TAddress::class]],
            ['maritalStatus', TCodeableConcept::class],
            ['multipleBirthBoolean', TBoolean::class],
            ['multipleBirthInteger', TInteger::class],
            ['contact', [TContact::class]],
            ['animal', TAnimal::class],
            ['communication', [TCommunication::class]],
            ['extension', [TExtension::class]],
            ['contained', [RResource::class]],
            ['managingOrganization', TReference::class],
        ];
    }

}