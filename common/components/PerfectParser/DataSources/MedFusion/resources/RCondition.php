<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:02
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAge;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAnnotation;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TNarrative;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRange;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class RCondition
 * @package common\components\PerfectParser
 *
 * @property \common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod|\common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString|\common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime|\common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAge|\common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRange $anyOnset
 * @property null|string $activeDate
 * @property \common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod|\common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString|\common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime|\common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAge|\common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRange $anyAbatement
 */
class RCondition extends RResource
{
    /**
     * unconfirmed status
     */
    const STATUS_UNCONFIRMED = 'unconfirmed';
    /**
     * unconfirmed status
     */
    const STATUS_ACTIVE = 'active';

    /** @var TIdentifier[] External ids for this item */
    public $identifier;

    /** @var TString|null  */
    public $resourceType;

    /** @var TCode active | recurrence | inactive | remission | resolved */
    public $clinicalStatus;

    /** @var TCode provisional | differential | confirmed | refuted | entered-in-error | unknown */
    public $verificationStatus;

    /** @var TCode problem-list-item | encounter-diagnosis */
    public $category;

    /** @var TCodeableConcept Subjective severity of condition */
    public $severity;

    /** @var TCodeableConcept Identification of the condition, problem or diagnosis */
    public $code;

    /** @var TCodeableConcept Anatomical location, if relevant */
    public $bodySite;

    /** @var TString Estimated or actual date, date-time, or age */
    public $onset;

    /** @var TDateTime Estimated or actual date, date-time, or age */
    public $onsetDateTime;

    /** @var TAge Estimated or actual date, date-time, or age */
    public $onsetAge;

    /** @var TPeriod Estimated or actual date, date-time, or age */
    public $onsetPeriod;

    /** @var TRange Estimated or actual date, date-time, or age */
    public $onsetRange;

    /** @var TString Estimated or actual date, date-time, or age */
    public $onsetString;

    /** @var TString If/when in resolution/remission */
    public $abatement;

    /** @var TDateTime If/when in resolution/remission */
    public $abatementDateTime;

    /** @var TAge If/when in resolution/remission */
    public $abatementAge;

    /** @var TPeriod If/when in resolution/remission */
    public $abatementPeriod;

    /** @var TRange If/when in resolution/remission */
    public $abatementRange;

    /** @var TString If/when in resolution/remission */
    public $abatementString;

    /** @var TDateTime Date record was believed accurate */
    public $assertedDate;

    /** @var TAnnotation Additional text not captured in other fields */
    public $note;

    /** @var TNarrative */
    public $text;

    /** @var RResource[]|null  */
    public $contained;

    /** @var TReference[]|null  */
    public $asserter;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['asserter', [TReference::class]],
            ['contained', [RResource::class]],
            ['identifier', [TIdentifier::class]],
            ['resourceType', TString::class],
            [['clinicalStatus', 'verificationStatus'], TCode::class, self::REQUIRED],
            ['category', TCode::class],
            [['severity', 'code'], TCodeableConcept::class],
            ['bodySite', TCodeableConcept::class],
            ['onset', TString::class],
            ['onsetDateTime', TDateTime::class],
            ['onsetAge', TAge::class],
            ['onsetPeriod', TPeriod::class],
            ['onsetRange', TRange::class],
            ['onsetString', TString::class],
            ['abatement', TString::class],
            ['abatementDateTime', TDateTime::class],
            ['abatementAge', TAge::class],
            ['abatementPeriod', TPeriod::class],
            ['abatementRange', TRange::class],
            ['abatementString', TString::class],
            ['assertedDate', TDateTime::class],
            ['note', TAnnotation::class],
            ['text', TNarrative::class],
        ];
    }


    /**
     * Return any given onset
     * @return TString | TDateTime | TAge | TPeriod | TRange Onset
     */
    public function getAnyOnset(){
        $properties = ['onset', 'onsetString', 'onsetDateTime', 'onsetAge', 'onsetPeriod', 'onsetRange'];
        foreach ($properties as $property){
            if ($this->$property) {
                return $this->$property;
            }
        }
        return null;
    }

    /**
     * Return any given abatement
     * @return TString | TDateTime | TAge | TPeriod | TRange Onset
     */
    public function getAnyAbatement(){
        $properties = ['abatement', 'abatementString', 'abatementDateTime', 'abatementAge', 'abatementPeriod', 'abatementRange'];
        foreach ($properties as $property){
            if ($this->$property) {
                return $this->$property;
            }
        }
        return null;
    }


    /**
     * Return true if clinicalStatus == active
     *
     * @return bool
     */
    public function isActive()
    {
        if ($this->clinicalStatus && $this->clinicalStatus->getValue() == self::STATUS_ACTIVE) {
            return true;
        }

        if (is_null($this->clinicalStatus) || empty($this->clinicalStatus->getValue())){
            if ($this->onsetPeriod){
                if (!$this->onsetPeriod->end){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Return status
     *
     * @return mixed|null
     */
    public function getStatus()
    {
        $result = null;

        if (!empty($this->verificationStatus)) {
            $result = $this->verificationStatus->getValue();
        }

        return $result;
    }

    /**
     * Return problem text
     *
     * @return null|string
     */
    public function getProblemText()
    {
        $result = null;

        if (!empty($this->code)) {
            $result = $this->code->getValue();
        }

        return $result;
    }

    /**
     * Return problem list status
     *
     * @return null|string
     */
    public function getProblemListStatus()
    {
        $result = null;

        if (isset($this->clinicalStatus)) {
            $result = (string) $this->clinicalStatus->getValue();
        }

        return $result;
    }

    /**
     * Return problem active date
     *
     * @return null|string
     */
    public function getProblemActiveDate()
    {
        $format = TDateTime::FORMAT_MMM_DD_YYYY;

        if (isset($this->onsetDateTime)) {
            return $this->onsetDateTime->asDatetime($format);
        }

        if (isset($this->onsetPeriod)) {
            $start = (isset($this->onsetPeriod->start) ? $this->onsetPeriod->start->asDatetime($format) : null);
            $end = (isset($this->onsetPeriod->end) ? $this->onsetPeriod->end->asDatetime($format) : null);
            if ($start && $end) {
                $result = $start.' - '.$end;
            } else {
                $result = ($start ? $start : $end);
            }
            if ($result) {
                return $result;
            }
        }

        if (isset($this->onsetRange)) {
            $low = (isset($this->onsetRange->low) ? $this->onsetRange->low->getValue() : null);
            $high = (isset($this->onsetRange->high) ? $this->onsetRange->high->getValue() : null);
            if ($low && $high) {
                $result = $low.' - '.$high;
            } else {
                $result = ($low ? $low : $high);
            }
            if ($result) {
                return $result;
            }
        }

        if (isset($this->onsetQuantity)) {
            if (isset($this->onsetQuantity->value)) {
                $value = $this->onsetQuantity->value->getValue();
                $unit = ($this->onsetQuantity->unit ? $this->onsetQuantity->unit->getValue() : null);

                return 'Age: '.$value.($unit ? ' '.$unit : '');
            }
        }

        if (isset($this->onsetString)) {
            return $this->onsetString->getValue();
        }

        return null;
    }

    /**
     * Return problem end date
     *
     * @return null|string
     */
    public function getProblemEndDate()
    {
        $format = TDateTime::FORMAT_MMM_DD_YYYY;

        if (isset($this->abatementDateTime)) {
            return $this->abatementDateTime->asDatetime($format);
        }

        if (isset($this->abatementPeriod)) {
            $start = (isset($this->abatementPeriod->start) ? $this->abatementPeriod->start->asDatetime($format) : null);
            $end = (isset($this->abatementPeriod->end) ? $this->abatementPeriod->end->asDatetime($format) : null);
            if ($start && $end) {
                $result = $start.' - '.$end;
            } else {
                $result = ($start ? $start : $end);
            }
            if ($result) {
                return $result;
            }
        }

        if (isset($this->abatementRange)) {
            $low = (isset($this->abatementRange->low) ? $this->abatementRange->low->getValue() : null);
            $high = (isset($this->abatementRange->high) ? $this->abatementRange->high->getValue() : null);
            if ($low && $high) {
                $result = $low.' - '.$high;
            } else {
                $result = ($low ? $low : $high);
            }
            if ($result) {
                return $result;
            }
        }

        if (isset($this->abatementQuantity)) {
            if (isset($this->abatementQuantity->value)) {
                $value = $this->abatementQuantity->value->getValue();
                $unit = ($this->abatementQuantity->unit ? $this->abatementQuantity->unit->getValue() : null);

                return 'Age: '.$value.($unit ? ' '.$unit : '');
            }
        }

        if (isset($this->abatementString)) {
            return $this->abatementString->getValue();
        }

        if (isset($this->abatementBoolean)) {
            if ($this->abatementBoolean->getValue()) {
                return 'Ended, date unknown';
            }
        }

        return null;
    }

    /**
     * Return problem icd-10
     *
     * @return null|string
     */
    public function getProblemIcd10()
    {
        return null;
    }
}