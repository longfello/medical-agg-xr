<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 23.10.18
 * Time: 16:24
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\labs;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Observation\TReferenceRange;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TQuantity;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRange;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRatio;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TElement;

/**
 * Class RObservation
 * @package common\components\PerfectParser\DataSources\MedFusion\resources\labs
 */
class RObservation extends SubResource
{
    /** @var TCodeableConcept Actual result */
    public $valueCodeableConcept;
    /** @var TQuantity Actual result */
    public $valueQuantity;
    /** @var TCodeableConcept High, low, normal, etc. */
    public $interpretation;
    /** @var TDateTime Clinically relevant time/time-period for observation */
    public $effectiveDateTime;
    /** @var TPeriod Clinically relevant time/time-period for observation */
    public $effectivePeriod;
    /** @var TCodeableConcept Type of observation (code / type) http://hl7.org/fhir/DSTU2/valueset-observation-codes.html  */
    public $code;

    /**
     * @return null|string
     */
    public function getObservationIdentifier()
    {
        $result = null;

        if (isset($this->code)) {
            $result = $this->code->getIdentifier();
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationIdCoding()
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
    public function getObservationIdSystem()
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
    public function getObservationValue()
    {
        $result = null;

        if ($this->valueCodeableConcept) {
            $result = $this->valueCodeableConcept->getValue();
        } elseif (isset($this->valueQuantity->value)) {
            $result = (string) $this->valueQuantity->value->getValue();
        } elseif (isset($this->valueString)) {
            $result = $this->valueString->getValue();
        } elseif (isset($this->valueRange)) {
            $result = $this->valueRange->format(TRange::FORMAT_FULL_RANGE);
        } elseif (isset($this->valueRatio)) {
            $result = $this->valueRatio->format(TRatio::FORMAT_VALUE);
        } elseif (isset($this->valueTime)) {
            $result = $this->valueTime->format();
        } elseif (isset($this->valueDateTime)) {
            $result = $this->valueDateTime->asDatetime();
        } elseif (isset($this->valuePeriod)) {
            $result = $this->valuePeriod->asDateTimePeriod('php:Y-m-d H:i:s', '-');
        } elseif (isset($this->valueSampledData)) {
            $result = $this->valueSampledData->format(TElement::FORMAT_JSON);
        } elseif (isset($this->valueAttachment)) {
            $result = 'attachment not available';
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationUnits()
    {
        $result = null;

        if ($this->valueCodeableConcept) {
            $result = null;
        } elseif (isset($this->valueQuantity->value)) {
            $result = isset($this->valueQuantity->unit) ? $this->valueQuantity->unit->getValue() : null;
        } elseif (isset($this->valueString)) {
            $result = null;
        } elseif (isset($this->valueRange)) {
            $result = $this->valueRange->getUnits();
        } elseif (isset($this->valueRatio)) {
            $result = $this->valueRatio->format(TRatio::FORMAT_UNITS);
        } elseif (isset($this->valueTime)) {
            $result = null;
        } elseif (isset($this->valueDateTime)) {
            $result = null;
        } elseif (isset($this->valuePeriod)) {
            $result = null;
        } elseif (isset($this->valueSampledData)) {
            $result = null;
        } elseif (isset($this->valueAttachment)) {
            $result = null;
        }

        $units = $result;
        if (preg_match('/^\[.+\]$/', $units)) {
            $items = explode('_', trim($units, '[]'));
            $result = $items[0];
        } elseif ($units == 'mm[Hg]') {
            $result = 'mm';
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationRange()
    {
        $result = null;

        if (isset($this->referenceRange)) {
            foreach ($this->referenceRange as $item) {
                /** @var $item TReferenceRange */
                $resultRaw = $item->format(TReferenceRange::FORMAT_RANGE);
                if ($resultRaw) {
                    $result = $resultRaw;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationResultStatus()
    {
        return isset($this->status) ? $this->status->getValue() : null;
    }

    /**
     * @return null|string
     */
    public function getObservationDate()
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
    public function getObservationIdText()
    {
        $result = null;

        if (isset($this->code->text)) {
            $result = $this->code->text->getValue();
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationFlagCode()
    {
        $result = null;

        if ($this->interpretation) {
            $result = $this->interpretation->getCoding();
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationFlagDescription()
    {
        $result = null;

        if ($this->interpretation) {
            $result = $this->interpretation->getValue();
        }

        return $result;
    }

    /**
     * Get external ntes resources
     * @return array
     */
    public function getNtes()
    {
        $result[] = new RNte($this->contentRaw);

        return $result;
    }
}