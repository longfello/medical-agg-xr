<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:02
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;

use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\MedicationDispense\TDosageInstruction;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\MedicationDispense\TSubstitution;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TSimpleQuantity;

/**
 * Class RMedicationDispense
 * @package common\components\PerfectParser
 *
 * @property null|string $medicationDirectionToPatient
 * @property null $medicationEndDate
 * @property null|string $medicationText
 * @property null $medicationRoute
 * @property null|mixed $medicationStatus
 * @property null $medicationDuration
 * @property null|string $medicationDatePrescribed
 * @property null|string $medicationDoseUnit
 * @property null $medicationNumRefills
 * @property null $medicationStrange
 * @property null $medicationDoseTiming
 */
class RMedicationDispense extends RResource
{
    use MedicationTrait;

    /** @var TString|null  */
    public $resourceType;

    /** @var RResource[]  */
    public $contained;

    /** @var TIdentifier | null External identifier */
    public $identifier;

    /** @var TCode | null in-progress | on-hold | completed | entered-in-error | stopped */
    public $status;

    /** @var TReference | null Who the dispense is for */
    public $patient;

    /** @var TReference | null Practitioner responsible for dispensing medication */
    public $dispenser;

    /** @var TReference[] | null Medication order that authorizes the dispense */
    public $authorizingPrescription;

    /** @var TCodeableConcept Trial fill, partial fill, emergency fill, etc. */
    public $type;

    /** @var TSimpleQuantity | null Amount dispensed */
    public $quantity;

    /** @var TSimpleQuantity | null Days Supply */
    public $daysSupply;

    /** @var TCodeableConcept What medication was supplied */
    public $medicationCodeableConcept;

    /** @var TReference What medication was supplied */
    public $medicationReference;

    /** @var TDateTime | null Dispense processing time */
    public $whenPrepared;

    /** @var TDateTime | null When product was given out */
    public $whenHandedOver;

    /** @var TReference | null Where the medication was sent */
    public $destination;

    /** @var TReference[] | null Who collected the medication */
    public $receiver;

    /** @var TString | null Information about the dispense */
    public $note;

    /** @var TDosageInstruction[]|TArray|null Medicine administration instructions to the patient/caregiver */
    public $dosageInstruction;

    /** @var TSubstitution | null Deals with substitution of one medicine for another */
    public $substitution;

    /** @var TCodeableConcept  */
    public $code;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['resourceType', TString::class],
            ['contained', [RResource::class]],
            ['identifier', TIdentifier::class],
            ['status', TCode::class],
            ['patient', TReference::class],
            ['dispenser', TReference::class],
            ['authorizingPrescription', [TReference::class]],
            ['type', TCodeableConcept::class],
            ['quantity', TSimpleQuantity::class],
            ['daysSupply', TSimpleQuantity::class],
            ['medicationCodeableConcept', TCodeableConcept::class],
            ['medicationReference', TReference::class],
            ['whenPrepared', TDateTime::class],
            ['whenHandedOver', TDateTime::class],
            ['destination', TReference::class],
            ['receiver', [TReference::class]],
            ['note', TString::class],
            ['dosageInstruction', [TDosageInstruction::class]],
            ['substitution', [TSubstitution::class]],
            ['code', TCodeableConcept::class],
        ];
    }

    /**
     * @return bool|null|string
     */
    public function getMedicationDuration()
    {
        if ($this->daysSupply){
            return $this->daysSupply->format();
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationDatePrescribed()
    {
        if ($this->whenHandedOver) return $this->whenHandedOver->asDate();
        if ($this->whenPrepared) return $this->whenPrepared->asDate();
        return null;
    }

}
