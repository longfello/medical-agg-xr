<?php
namespace common\components\PerfectParser\DataSources\MedFusion\resources;

use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\MedicationDispense\TDosageInstruction;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;

/**
 * Trait MedicationTrait
 * @package common\components\PerfectParser\DataSources\MedFusion\resources
 * @property TCode $status
 * @property TArray|TDosageInstruction[]|null $dosageInstruction  Medicine administration instructions to the patient/caregiver
 * @property RMedicationAdministration|RMedicationDispense|RMedicationOrder|RMedicationStatement $medicationCodeableConcept
 */
trait MedicationTrait
{
    /**
     * @return mixed|null
     */
    public function getStatus(){
        return $this->status ? $this->status->getValue() : null;
    }

    /**
     * @return mixed|null
     */
    public function getMedicationStatus(){
        if ($this->status){
            $status = $this->status->getValue();
            if ($status != 'entered-in-error'){
                return $status;
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationDirectionToPatient(){
        if ($this->dosageInstruction){
            $dosage = $this->dosageInstruction->first();
            if ($dosage){
                $result = [];
                if ($dosage->text && $value = $dosage->text->getValue()) $result[] = $value;
                if ($dosage->additionalInstructions && $value = $dosage->additionalInstructions->getValue()) $result[] = $value;
                if ($result){
                    return implode(', ', $result);
                }
            }
        }
        return null;
    }

    /**
     * @return bool|null|string
     */
    public function getMedicationDoseTiming(){
        if ($this->dosageInstruction){
            $dosage = $this->dosageInstruction->first();
            if ($dosage){
                return $dosage->format(TDosageInstruction::FORMAT_TIMING);
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationDoseUnit(){
        if ($this->medicationReference){
            $data = $this->getReferencedResource($this->medicationReference);
            if ($data){
                /** @var $data RMedication */
                return $data->getDoseUnit();
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationRoute(){
        if ($this->dosageInstruction){
            $dosage = $this->dosageInstruction->first();
            if ($dosage){
                /** @var $dosage TDosageInstruction */
                if ($dosage->route){
                    return $dosage->route->getValue();
                }
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationStrange(){
        if ($this->medicationReference){
            $data = $this->getReferencedResource($this->medicationReference);
            if ($data){
                /** @var $data RMedication */
                return $data->getStrange();
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationText()
    {
        $text = null;
        if ($this->medicationCodeableConcept){
            $text = $this->medicationCodeableConcept->getValue();
        }

        if (!$text) {
            if ($this->medicationReference){
                $resource = $this->getReferencedResource($this->medicationReference);
                /** @var RMedicationAdministration|RMedicationDispense|RMedicationOrder|RMedicationStatement $resource */
                if (isset($resource->code)){
                    $text = $resource->code->getValue();
                }
            }
        }

        return $text;
    }

    /**
     * @return null
     */
    public function getMedicationEndDate(){
        return null;
    }

    /**
     * @return null
     */
    public function getMedicationNumRefills(){
        return null;
    }

    /**
     * @return null
     */
    public function getMedicationDuration(){
        return null;
    }

}
