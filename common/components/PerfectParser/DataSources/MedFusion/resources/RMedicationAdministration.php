<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:02
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\Common\Helper;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\MedicationAdministration\TDosage;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCoding;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;

/**
 * Class RMedicationAdministration
 * @package common\components\PerfectParser
 *
 * @property null|string|mixed $medicationDirectionToPatient
 * @property null|string $medicationEndDate
 * @property null|string|false $medicationText
 * @property null|string $medicationRoute
 * @property null|mixed $medicationStatus
 * @property null $medicationDuration
 * @property null|string $medicationDatePrescribed
 * @property null|string $medicationDoseUnit
 * @property null $medicationNumRefills
 * @property null|string $medicationStrange
 * @property null|bool|string $medicationDoseTiming
 */
class RMedicationAdministration extends RResource
{
    use MedicationTrait;

    /** @var TString|null  */
    public $resourceType;

    /** @var RResource[]  */
    public $contained;

    /** @var TIdentifier[]|null An identifier for this patient */
    public $identifier;

    /** @var TCode in-progress | on-hold | completed | entered-in-error | stopped */
    public $status;

    /** @var TReference Who received medication */
    public $patient;

    /** @var TReference | null Who administered substance */
    public $practitioner;

    /** @var TReference | null Encounter administered as part of */
    public $encounter;

    /** @var TReference | null Order administration performed against */
    public $prescription;

    /** @var TBoolean | null True if medication not administered */
    public $wasNotGiven;

    /** @var TCodeableConcept[] | null Reason administration not performed */
    public $reasonNotGiven;

    /** @var TCodeableConcept[] | null Reason administration performed */
    public $reasonGiven;

    /** @var TDateTime Start and end time of administration */
    public $effectiveTimeDateTime;

    /** @var TPeriod Start and end time of administration */
    public $effectiveTimePeriod;

    /** @var TCodeableConcept What was administered */
    public $medicationCodeableConcept;

    /** @var TReference What was administered */
    public $medicationReference;

    /** @var TReference[] | null Device used to administer */
    public $device;

    /** @var TString | null Information about the administration */
    public $note;

    /** @var TDosage | null Details of how medication was taken */
    public $dosage;

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
            ['identifier', [TIdentifier::class]],
            ['status', TCode::class, self::REQUIRED],
            ['patient', TReference::class, self::REQUIRED],
            ['practitioner', TReference::class],
            ['encounter', TReference::class],
            ['prescription', TReference::class],
            ['wasNotGiven', TBoolean::class],
            ['reasonNotGiven', [TCodeableConcept::class]],
            ['reasonGiven', [TCodeableConcept::class]],
            ['effectiveTimeDateTime', TDateTime::class],
            ['effectiveTimePeriod', TPeriod::class],
            ['medicationCodeableConcept', TCodeableConcept::class],
            ['medicationReference', TReference::class],
            ['device', [TReference::class]],
            ['note', TString::class],
            ['dosage', TDosage::class],
            ['code', TCodeableConcept::class],
        ];
    }

    /**
     * @return null|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function getMedicationText()
    {
        $text = null;
        if ($this->medicationCodeableConcept){
            $text = $this->medicationCodeableConcept->getValue();
            if (!$text){
                if ($this->medicationCodeableConcept->coding){
                    foreach ($this->medicationCodeableConcept->coding as $coding){
                        /** @var $coding TCoding */
                        if ($coding->system && $coding->system->getValue() === TCoding::SYSTEM_RXNORM){
                            if ($coding->code && is_numeric($coding->code->getValue())){
                                $this->log("Getting name by RxCode: ".$coding->code->getValue());
                                $text = Helper::getNameByRxCode($coding->code->getValue());
                            }
                        }
                    }

                }
            }
        }

        if (!$text) {
            if ($this->medicationReference){
                $resource = $this->getReferencedResource($this->medicationReference);
                if (isset($resource->code)){
                    /** @var $code TCodeableConcept */
                    $code = $resource->code;
                    $text = $code->getValue();
                }
            }
        }

        return $text;
    }

    /**
     * @return null|string
     */
    public function getMedicationRoute()
    {
        if ($this->dosage){
            if ($this->dosage->route){
                return $this->dosage->route->getValue();
            }
        }
        return null;
    }

    /**
     * @return bool|null|string
     */
    public function getMedicationDoseTiming()
    {
        if ($this->dosage){
            return $this->dosage->format(TDosage::FORMAT_TIMING);
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationDatePrescribed()
    {
        if ($this->effectiveTimeDateTime) return $this->effectiveTimeDateTime->asDate();
        if ($this->effectiveTimePeriod){
            if ($this->effectiveTimePeriod->start){
                return $this->effectiveTimePeriod->start->asDate();
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationEndDate()
    {
        if ($this->effectiveTimePeriod){
            if ($this->effectiveTimePeriod->end){
                return $this->effectiveTimePeriod->end->asDate();
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
            if ($this->dosage->text){
                return $this->dosage->text->getValue();
            }
        }
        return null;
    }

}
