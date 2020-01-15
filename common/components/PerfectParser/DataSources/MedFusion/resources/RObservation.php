<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:02
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Observation\TReferenceRange;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Observation\TRelated;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAttachment;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TNarrative;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TQuantity;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRange;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRatio;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TSampledData;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TInstant;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\labs\RRequest;

/**
 * Class RObservation
 * @package common\components\PerfectParser
 *
 * @property string $jsonValue
 */
class RObservation extends RResource
{
    /**
     *
     */
    const DEFAULT_VALUE_ORDER = ['valueQuantity', 'valueCodeableConcept', 'valueString', 'valueRange'];
    /**
     *
     */
    const DEFAULT_TIME_ORDER = ['effectiveDateTime', 'effectivePeriod'];

    // Observation statuses, see http://hl7.org/fhir/DSTU2/valueset-observation-status.html

    /** @var string */
    const STATUS_REGISTERED = 'registered';

    /** @var string */
    const STATUS_PRELIMINARY = 'preliminary';

    /** @var string */
    const STATUS_FINAL = 'final';

    /** @var string */
    const STATUS_AMENDED = 'amended';

    /** @var string */
    const STATUS_CANCELLED = 'cancelled';

    /** @var string */
    const STATUS_ENTERED_IN_ERROR = 'entered-in-error';

    /** @var string */
    const STATUS_UNKNOWN = 'unknown';

    /** @var TIdentifier Unique Id for this particular observation */
    public $identifier;

    /** @var TCode registered | preliminary | final | amended | cancelled | entered-in-error | unknown - http://hl7.org/fhir/DSTU2/valueset-observation-status.html */
    public $status;

    /** @var TCodeableConcept Classification of type of observation http://hl7.org/fhir/DSTU2/valueset-observation-category.html */
    public $category;

    /** @var TCodeableConcept Type of observation (code / type) http://hl7.org/fhir/DSTU2/valueset-observation-codes.html  */
    public $code;

    /** @var TReference | null Who and/or what this is about */
    public $subject;

    /** @var TReference | null Healthcare event during which this observation is made */
    public $encounter;

    /** @var TDateTime Clinically relevant time/time-period for observation */
    public $effectiveDateTime;

    /** @var TPeriod Clinically relevant time/time-period for observation */
    public $effectivePeriod;

    /** @var TInstant Date/Time this was made available */
    public $issued;

    /** @var TReference[] | TArray | null Who is responsible for the observation */
    public $performer;

    /** @var TQuantity Actual result */
    public $valueQuantity;

    /** @var TString Actual result */
    public $valueString;

    /** @var TCodeableConcept Actual result */
    public $valueCodeableConcept;

    /** @var TRange Actual result */
    public $valueRange;

    /** @var TRatio Actual result */
    public $valueRatio;

    /** @var TSampledData Actual result */
    public $valueSampledData;

    /** @var TAttachment Actual result */
    public $valueAttachment;

    /** @var TTime Actual result */
    public $valueTime;

    /** @var TDateTime Actual result */
    public $valueDateTime;

    /** @var TPeriod Actual result */
    public $valuePeriod;

    /** @var TCodeableConcept Why the result is missing http://hl7.org/fhir/DSTU2/valueset-observation-valueabsentreason.html */
    public $dataAbsentReason;

    /** @var TCodeableConcept High, low, normal, etc. */
    public $interpretation;

    /** @var TString Comments about result */
    public $comments;

    /** @var TCodeableConcept Observed body part */
    public $bodySite;

    /** @var TCodeableConcept How it was done */
    public $method;

    /** @var TReference | null Specimen used for this observation */
    public $specimen;

    /** @var TReference | null (Measurement) Device */
    public $device;

    /** @var TArray|TRelated[]|null Resource related to this observation */
    public $related;

    /** @var TArray|TReferenceRange[]|null Provides guide for interpretation. Must have at least a low or a high or text */
    public $referenceRange;

    /** @var TString|null  */
    public $resourceType;

    /** @var TString[]|TArray|null  */
    public $id;

    /** @var TNarrative|null  */
    public $text;

    /** @var RResource[]|RProcedure[]|RPractitioner[]|RPatient[]|ROrganization[]|RObservation|RMedicationStatement[]|RMedicationOrder[]|RMedicationDispense[]|RMedicationAdministration[]|RMedication[]|RImmunization[]|REmergencyContact[]|RDevice[]|RCondition[]|RBundle[]|RAppointment[]|RAllergyIntolerance[] sub-resources */
    public $contained;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['id', [TString::class]],
            ['identifier', [TIdentifier::class]],
            ['status', TCode::class],
            ['category', TCodeableConcept::class],
            ['code', TCodeableConcept::class],
            ['subject', TReference::class],
            ['encounter', TReference::class],
            ['effectiveDateTime', TDateTime::class],
            ['effectivePeriod', TPeriod::class],
            ['issued', TInstant::class],
            ['performer', [TReference::class]],
            ['valueQuantity', TQuantity::class],
            ['valueString', TString::class],
            ['valueCodeableConcept', TCodeableConcept::class],
            ['valueRange', TRange::class],
            ['valueRatio', TRatio::class],
            ['valueSampledData', TSampledData::class],
            ['valueAttachment', TAttachment::class],
            ['valueTime', TTime::class],
            ['valueDateTime', TDateTime::class],
            ['valuePeriod', TPeriod::class],
            ['dataAbsentReason', TCodeableConcept::class],
            ['interpretation', TCodeableConcept::class],
            ['comments', TString::class],
            ['bodySite', TCodeableConcept::class],
            ['method', TCodeableConcept::class],
            ['specimen', TReference::class],
            ['device', TReference::class],
            ['related', [TRelated::class]],
            ['referenceRange', [TReferenceRange::class]],
            ['resourceType', TString::class],
            ['text', TNarrative::class],
            ['contained', [RResource::class]],
        ];
    }


    /**
     * @param $code
     * @param null $orContainString
     *
     * @return bool
     */
    public function hasCode($code, $orContainString = null){
        if ($this->code && $this->code->coding) {
            foreach ($this->code->coding as $coding){
                if ($coding->code){
                    if ($coding->code->getValue() == $code){
                        return true;
                    }
                }
            }
        }
        if (!is_null($orContainString)){
            if ($this->code && $this->code->coding) {
                foreach ($this->code->coding as $coding){
                    if ($coding->code){
                        if (stripos($orContainString, $coding->code->getValue()) !== false){
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param null $order
     *
     * @return bool|mixed|null|string
     */
    public function getResourceValue($order = null){
        $order = $order?$order:self::DEFAULT_VALUE_ORDER;
        foreach ($order as $field){
            if ($this->$field){
                $value = $this->$field;
                switch (get_class($value)){
                    case TQuantity::class:
                        /** @var $value TQuantity */
                        return $value->format("{property('value', 'N/A')} {property('unit')}");
                    case TCodeableConcept::class:
                        /** @var $value TCodeableConcept */
                        return $value->format("{property('text')}");
                    case TString::class:
                        /** @var $value TString */
                        return $value->getValue();
                    case TRange::class:
                        /** @var $value TRange */
                        return $value->format("{asText()}");
                    default:
                        /** @var $value TComplex */
                        return $value->format();
                }
            }
        }
        return null;
    }

    /**
     * @param null $order
     *
     * @return int|null|string
     */
    public function getResourceTime($order = null){
        $order = $order?$order:self::DEFAULT_TIME_ORDER;
        foreach ($order as $field){
            if ($this->$field){
                $value = $this->$field;
                /** @var $value TDateTime|TPeriod */
                return $value->getTimestamp();
            }
        }
        return 0;
    }

    /**
     * @return string
     */
    public function getJsonValue()
    {
        return json_encode($this->originalValue);
    }

    /**
     * @return string
     */
    public function getResourceContent()
    {
        return $this->getJsonValue();
    }

    /**
     * @param $resourceContent
     * @return string
     */
    public function getLabSource($resourceContent)
    {
        return md5($resourceContent).'.json';
    }

    /**
     * @return null|string
     */
    public function getReportDate()
    {
        $result = null;

        if (isset($this->effectiveDateTime) && $value = $this->effectiveDateTime->asDatetime()) {
            $result = $value;
        } elseif (isset($this->effectivePeriod)) {
            if (isset($this->effectivePeriod->start) && $value = $this->effectivePeriod->start->asDatetime()) {
                $result = $value;
            }
        }

        return $result;
    }


    /**
     * @return null|string
     */
    public function getOrderedBy()
    {
        if ($this->performer) {
            foreach ($this->performer as $performer) {
                $reference = $this->getReferencedResource($performer);
                if ($reference instanceof RPractitioner) {
                    if ($name = $reference->getName()) {
                        return $name;
                    }

                    if (isset($reference->practitionerRole)) {
                        foreach ($reference->practitionerRole as $role) {
                            if ($name = $role->getManagingOrganizationName($this)) {
                                return $name;
                            }
                        }
                    }
                }
            }

            foreach ($this->performer as $performer) {
                $reference = $this->getReferencedResource($performer);
                if ($reference instanceof ROrganization) {
                    if ($name = $reference->getName()){
                        return $name;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Get external requests resources
     * @return array
     */
    public function getRequests()
    {
        $result = [];

        $result[] = new RRequest($this); // creating organizer external entity

        return $result;
    }

    /**
     * @return bool
     */
    public function checkIsLab()
    {
        if (isset($this->category) && isset($this->category->coding)) {
            foreach ($this->category->coding as $code) {
                if (isset($code->code) && strtolower($code->code->getValue()) == 'laboratory') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return $this->identity;
    }
}
