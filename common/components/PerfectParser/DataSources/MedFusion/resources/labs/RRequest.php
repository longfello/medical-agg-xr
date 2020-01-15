<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 21.10.18
 * Time: 16:03
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\labs;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Observation\TRelated;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;

/**
 * Class RRequest
 * @package common\components\PerfectParser\DataSources\CCDA\resources\labs
 */
class RRequest extends SubResource
{
    /** @var TPeriod Clinically relevant time/time-period for observation */
    public $effectivePeriod;
    /** @var TCodeableConcept Type of observation (code / type) http://hl7.org/fhir/DSTU2/valueset-observation-codes.html  */
    public $code;
    /** @var TArray|TRelated[]|null Resource related to this observation */
    public $related;

    /**
     * @return null|string
     */
    public function getRequestObservationDate()
    {
        $result = null;

        if (isset($this->effectiveDateTime)) {
            $result = $this->effectiveDateTime->asDatetime();
        }

        if (empty($result) && isset($this->effectivePeriod->start)) {
            $result = $this->effectivePeriod->start->asDatetime();
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getRequestObservationEndDate()
    {
        $result = null;

        if (isset($this->effectivePeriod->end)) {
            $result = $this->effectivePeriod->end->asDatetime();
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getRequestIdCoding()
    {
        $result = null;

        if (isset($this->code)) {
            $result = $this->code->getCoding();
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getRequestIdSystem()
    {
        $result = null;

        if (isset($this->code)) {
            $result = $this->code->getSystem();
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getRequestIdentifier()
    {
        $result = null;

        if (isset($this->code)) { // code is required (http://hl7.org/fhir/DSTU2/observation.html), but in some test files it is absent
            $result = $this->code->getIdentifier();
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getRequestIdText()
    {
        $result = null;

        if (isset($this->code->text)) {
            $result = $this->code->text->getValue();
        }

        return $result;
    }


    /**
     * Get external observations resources
     * @return array
     */
    public function getObservations()
    {
        $result = [];

        if (isset($this->related)) {
            foreach ($this->related as $member) {
                if ($member->type->getValue() == 'has-member') {
                    $source = $this->getReferencedResource($member->target);
                    if ($source instanceof \common\components\PerfectParser\DataSources\MedFusion\resources\RObservation) {
                        $result[] = new RObservation($source);
                    }
                }
            }
        } else {
            $result[] = new RObservation($this->contentRaw);
        }

        return $result;
    }

    /**
     * Get referenced resource, contained in current resource and described in given reference
     * @param TReference $reference
     * @return RResource | null
     */
    public function getReferencedResource(TReference $reference)
    {
        if (substr($reference->reference->getValue(), 0, 1) == '#') { // this reference is related to contained resource
            $referenceId = substr($reference->reference->getValue(), 1);
            if (isset($this->contained)) {
                foreach ($this->contained as $referenceItem) {
                    if ($referenceItem->id){
                        $ids = ($referenceItem->id instanceof TArray)?$referenceItem->id:new TArray($referenceItem->id);
                        foreach ($ids as $id) {
                            $idValue = is_object($id)?$id->getValue():$id;
                            if ($idValue == $referenceId) {
                                return $referenceItem;
                            }
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    public function IsSkipReport()
    {
        return false;
    }
}