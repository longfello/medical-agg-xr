<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:01
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\RMedicationOrder\TDispenseRequest;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\RMedicationOrder\TDosageInstruction;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\RMedicationOrder\TSubstitution;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class RMedicationOrder
 * @package common\components\PerfectParser
 *
 * @property null|string $medicationDirectionToPatient
 * @property null $medicationEndDate
 * @property null|string $medicationText
 * @property null|string $medicationRoute
 * @property null|mixed $medicationStatus
 * @property null|bool|string $medicationDuration
 * @property null|string $medicationDatePrescribed
 * @property null|string $medicationDoseUnit
 * @property null|mixed|int $medicationNumRefills
 * @property null|string $medicationStrange
 * @property null|bool|string $medicationDoseTiming
 */
class RMedicationOrder extends RResource
{
    use MedicationTrait;

    /** @var TString|null  */
    public $resourceType;

    /** @var RResource[]  */
    public $contained;

    /** @var TIdentifier[]|null An identifier for this patient */
    public $identifier;

    /** @var TDateTime|null When prescription was authorized */
    public $dateWritten;

    /** @var TCode|null active | on-hold | completed | entered-in-error | stopped | draft */
    public $status;

    /** @var TDateTime|null When prescription was stopped */
    public $dateEnded;

    /** @var TCodeableConcept|null Why prescription was stopped */
    public $reasonEnded;

    /** @var TReference|null Who prescription is for */
    public $patient;

    /** @var TReference|null Created during encounter/admission/stay */
    public $encounter;

    /** @var TReference|null Who ordered the medication(s) */
    public $prescriber;

    /** @var TCodeableConcept|null Reason or indication for writing the prescription */
    public $reasonCodeableConcept;

    /** @var TReference|null Reason or indication for writing the prescription */
    public $reasonReference;

    /** @var TString|null Information about the prescription */
    public $note;

    /** @var TCodeableConcept|null Medication to be taken */
    public $medicationCodeableConcept;

    /** @var TReference|null Medication to be taken */
    public $medicationReference;

    /** @var TDosageInstruction[]|TArray|null How medication should be taken */
    public $dosageInstruction;

    /** @var TDispenseRequest|null Medication supply authorization */
    public $dispenseRequest;

    /** @var TSubstitution|null Any restrictions on medication substitution */
    public $substitution;

    /** @var TReference|null An order/prescription that this supersedes */
    public $priorPrescription;

    /** @var TCodeableConcept  */
    public $code;

    /**
     * @inheritdoc
     */
    public function structure()
    {
        return [
            [['resourceType', 'note'], TString::class],
            ['contained', [RResource::class]],
            ['identifier', [TIdentifier::class]],
            [['dateWritten', 'dateEnded'], TDateTime::class],
            ['status', TCode::class],
            [['reasonEnded', 'reasonCodeableConcept', 'medicationCodeableConcept'], TCodeableConcept::class],
            [['patient', 'prescriber', 'encounter', 'reasonReference', 'medicationReference', 'priorPrescription'], TReference::class],
            ['dosageInstruction', [TDosageInstruction::class]],
            ['dispenseRequest', TDispenseRequest::class],
            ['substitution', TSubstitution::class],
            ['code', TCodeableConcept::class],
        ];
    }

    /**
     * @return bool|null|string
     */
    public function getMedicationDuration()
    {
        if ($this->dispenseRequest){
            if ($this->dispenseRequest->expectedSupplyDuration){
                return $this->dispenseRequest->expectedSupplyDuration->format();
            }
        }
        return null;
    }

    /**
     * @return int|mixed|null
     */
    public function getMedicationNumRefills()
    {
        if ($this->dispenseRequest){
            if ($this->dispenseRequest->numberOfRepeatsAllowed){
                return $this->dispenseRequest->numberOfRepeatsAllowed->getValue();
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationDatePrescribed()
    {
        if ($this->dateWritten) return $this->dateWritten->asDate();
        return null;
    }

}
