<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:01
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;

use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\MedicationStatement\TDosage;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TExtension;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TNarrative;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class RMedicationStatement
 * @package common\components\PerfectParser
 *
 * @property null|string|mixed $medicationDirectionToPatient
 * @property null|string $medicationEndDate
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
class RMedicationStatement extends RResource
{
    use MedicationTrait;

    /** @var TString|null  */
    public $resourceType;

    /** @var RResource[]  */
    public $contained;

    /** @var TIdentifier[]|null An identifier for this patient */
    public $identifier;

    /** @var TDateTime When the statement was asserted? */
    public $dateAsserted;

    /** @var TCode active | completed | entered-in-error | intended */
    public $status;

    /** @var TBoolean True if medication is/was not being taken */
    public $wasNotTaken;

    /** @var TCodeableConcept True if asserting medication was not given */
    public $reasonNotTaken;

    /** @var TCodeableConcept Condition/Problem/Diagnosis Codes */
    public $reasonForUseCodeableConcept;

    /** @var TCodeableConcept  */
    public $code;

    /** @var TReference Condition/Problem/Diagnosis Codes */
    public $reasonForUseReference;

    /** @var TDateTime Over what period was medication consumed? */
    public $effectiveDateTime;

    /** @var TPeriod Over what period was medication consumed? */
    public $effectivePeriod;

    /** @var TString Further information about the statement */
    public $note;

    /** @var TReference Additional supporting information */
    public $supportingInformation;

    /** @var TCodeableConcept What medication was taken */
    public $medicationCodeableConcept;

    /** @var TReference What medication was taken */
    public $medicationReference;

    /** @var TDosage[]|TArray|null Details of how medication was taken */
    public $dosage;

    /** @var TExtension[]  */
    public $extension;

    /** @var TNarrative  */
    public $text;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['resourceType', TString::class],
            ['contained', [RResource::class]],
            ['identifier', [TIdentifier::class]],
            ['dateAsserted', TDateTime::class],
            ['status', TCode::class],
            ['wasNotTaken', TBoolean::class],
            ['reasonNotTaken', TCodeableConcept::class],
            ['reasonForUseCodeableConcept', TCodeableConcept::class],
            ['reasonForUseReference', TReference::class],
            ['effectiveDateTime', TDateTime::class],
            ['effectivePeriod', TPeriod::class],
            ['note', TString::class],
            ['supportingInformation', TReference::class],
            ['medicationCodeableConcept', TCodeableConcept::class],
            ['medicationReference', TReference::class],
            ['dosage', [TDosage::class]],
            ['extension', [TExtension::class]],
            ['text', [TNarrative::class]],
            ['code', TCodeableConcept::class],
        ];
    }

    /**
     * @return null
     */
    public function getMedicationRoute()
    {
        if ($this->dosage){
            $dosage = $this->dosage->first();
            if ($dosage){
                if ($dosage->route){
                    return $dosage->route->getValue();
                }
            }
        }
        return null;
    }

    /**
     * @return null
     */
    public function getMedicationDoseTiming()
    {
        if ($this->dosage){
            $dosage = $this->dosage->first();
            if ($dosage){
                return $dosage->format(TDosage::FORMAT_TIMING);
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationDatePrescribed()
    {
        if ($this->effectiveDateTime) return $this->effectiveDateTime->asDate();
        if ($this->effectivePeriod){
            if ($this->effectivePeriod->start){
                return $this->effectivePeriod->start->asDate();
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationEndDate()
    {
        if ($this->effectivePeriod){
            if ($this->effectivePeriod->end){
                return $this->effectivePeriod->end->asDate();
            }
        }
        return null;
    }

    /**
     * @return mixed|null|string
     */
    public function getMedicationDirectionToPatient()
    {
        if ($this->dosage){
            $dosage = $this->dosage->first();
            if ($dosage){
                /** @var $dosage TDosage */
                if ($dosage->text){
                    return $dosage->text->getValue();
                }
            }
        }
        return null;
    }

}
