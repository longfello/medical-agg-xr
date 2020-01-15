<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:01
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\Common\ItemCollection;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\AllergyIntolerance\TReaction;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\AllergyIntolerance\TSubstance;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAnnotation;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\Resources\RAllergies;

/**
 * Allergy or Intolerance (generally: Risk of adverse reaction to a substance)
 *
 *
 * @property string $allergieText
 * @property string $reactionText
 * @property null|string $severityText
 * @property string $dateOnSet
 */

class RAllergyIntolerance extends RResource
{
    /**
     *
     */
    const CRITICALITY_HIGH_RISK = 'CRITH';
    /**
     *
     */
    const CRITICALITY_LOW_RISK  = 'CRITL';
    /**
     *
     */
    const CRITICALITY_IGNORE    = 'CRITU';


    /** @var TIdentifier External ids for this item */
    public $identifier;

    /** @var TCode active | inactive | resolved */
    public $clinicalStatus;

    /** @var TCode unconfirmed | confirmed | refuted | entered-in-error */
    public $verificationStatus;

    /** @var TCode allergy | intolerance - Underlying mechanism (if known) */
    public $type;

    /** @var TCode food | medication | environment | biologic */
    public $category;

    /** @var TCode low | high | unable-to-assess */
    public $criticality;

    /** @var TCodeableConcept Code that identifies the allergy or intolerance */
    public $code;

    /** @var TReference 	Who the sensitivity is for */
    public $patient;

    /** @var TDateTime When allergy or intolerance was identified */
    public $onset;

    /** @var TDateTime Date(/time) of last known occurrence of a reaction */
    public $lastOccurrence;

    /** @var TAnnotation Additional text not captured in other fields */
    public $note;

    /** @var TReaction Additional text not captured in other fields */
    public $reaction;

    /** @var TDateTime recorded Date */
    public $recordedDate;

    /** @var TSubstance substance */
    public $substance;

    /** @var TString|null  */
    public $resourceType;

    /** @var TString|null  */
    public $status;


    /**
     * @inheritdoc
     * @return array
     */
    public function structure()
    {
        return [
            ['identifier', [TIdentifier::class]],
            [['clinicalStatus', 'verificationStatus', 'type', 'category', 'criticality'], TCode::class, self::REQUIRED],
            ['code', [TCodeableConcept::class]],
            ['patient', TReference::class],
            ['onset', TDateTime::class],
            ['lastOccurrence', TDateTime::class],
            ['note', TAnnotation::class],
            ['reaction', [TReaction::class]],
            ['recordedDate', TDateTime::class],
            ['substance', TSubstance::class],
            ['resourceType', TString::class],
            ['status', TString::class]
        ];
    }

    /**
     * Return no non-blank data[substance] or data[reaction][i][substance] is found
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function isSubstancePresent(){
        if ($this->substance && $this->substance->getValue()) return true;
        if ($this->reaction){
            foreach($this->reaction as $reaction){
                /** @var $reaction TReaction */
                if ($reaction->substance){
                    foreach($reaction->substance as $substance){
                        if ($substance->getValue()) return true;
                    }
                }
            }
        }
        if ($this->substance && $this->substance->getValueFromRxCode()) return true;
        return false;
    }

    /**
     * Return Allergie Text
     * 1. text can come from several possible spots in the structure, listed here in order of preference:
     *   1.1. data[substance] as a CodeableConcept.
     *   1.2. data[reaction][i][substance] as a CodeableConcept, if option 1 is not available.
     *     1.2.a  Note: data[reaction] is an array.  Take the substance of the first reaction entry that has a useable substance.
     * 2.text can have a modifier appended at the end
     *   2.1. if data[status] = "unconfirmed" append (unconfirmed at the end)
     *   2.2. example: Aspirin (unconfirmed)
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function getAllergyText(){
        $text = '';
        if ($this->substance && $this->substance->getValue()) {
            $text = $this->substance->getValue();
        } else {
            if ($this->reaction){
                foreach ($this->reaction as $reaction){
                    /** @var $reaction TReaction */
                    if ($reaction->substance){
                        foreach($reaction->substance as $substance){
                            if ($substance->getValue()) {
                                $text = $substance->getValue();
                            }
                        }
                    }
                }
            }
        }

        if (!$text && $this->substance){
            $text = $this->substance->getValueFromRxCode();
        }

        if ($this->status){
            switch($this->status->getValue()){
                case RAllergies::STATUS_UNCONFIRMED:
                    $text .= " (unconfirmed)";
            }
        }

        return $text;
    }

    /**
     * Return Reaction Text
     *  1. text found from evaluating manifestation as a CodeableConcept in data[reaction] entries (0 or more)
     *  2. If there is no reaction then skip this
     *  3. If there is more than one reaction, join them with commas and "and" (Hives, Rash and Nausea) or (Hives and Rash)
     *  4. If any reaction includes "severity" field and it is "severe" or "moderate" then prefix that reaction with the severity.
     *
     * @return string
     */
    public function getReactionText(){
        $countLimiter = 20;
        $text = [];
        if ($this->reaction){
            foreach($this->reaction as $reaction){
                /** @var $reaction TReaction */
                if ($reaction->manifestation){
                    foreach ($reaction->manifestation as $manifestation){
                        /** @var $manifestation TCodeableConcept */
                        if ($manifestation->getValue()){
                            $countLimiter--;
                            if ($countLimiter > 0){
                                $prefix = '';
                                if ($reaction->severity){
                                    switch ($reaction->severity->getValue()){
                                        case TReaction::SEVERITY_MODERATE:
                                        case TReaction::SEVERITY_SEVERE:
                                            $prefix = $reaction->severity->getValue().' ';
                                    }
                                }
                                $text[] = $prefix . $manifestation->getValue();
                            } else {
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        if ($text){
            $reaction_list = '';
            $last          = (count($text) - 1);
            foreach ($text as $index => $reaction) {
                if ($index and $index == $last) {
                    $reaction_list .= " and ";
                } elseif ($index > 0) {
                    $reaction_list .= ", ";
                }
                $reaction_list .= $reaction;
            }
            return substr($reaction_list, 0, 255);
        }

        return null;
    }

    /**
     * Return Severity text
     * data[criticality] treated as a "coded" entry – a list of fixed possible values with special translations:
     *   1. if it contains "CRITH" enter "High-Risk"
     *   2. if it contains "CRITL" enter "Low-Risk"
     *   3. if it contains "CRITU" then ignore the severity and enter nothing.
     *   4. otherwise enter whatever the text value
     * @return string|null
     */
    public function getSeverityText(){
        if ($this->criticality){
            switch ($this->criticality->getValue()){
                case self::CRITICALITY_HIGH_RISK:
                    return "High-Risk";
                case self::CRITICALITY_LOW_RISK:
                    return "Low-Risk";
                case self::CRITICALITY_IGNORE:
                    return null;
                default:
                    return $this->criticality->getValue();
            }
        }
        return null;
    }

    /**
     * Return Date On Set
     * 1. can appear several places. Take the earliest of all onset dates you find at:
     * 1.1. date/time string found at data[onset]
     * 1.2. date/time string found as each data[reaction][i][onset] entry (possibly multiple reactions, check each)
     * 2. Trim the date/time to just a date in format Mmm dd, yyyy (strip the time) if you recogize the format.
     * 2.1. Otherwise just keep the entire field as literal text.
     * 2.2. dd in the date format is zero-padded: eg.  Mar 03, 2007 instead of Mar 3, 2007
     *
     * @return string
     */
    public function getDateOnSet(){
        $onSet = null;
        try {
            $variants = new ItemCollection();
            if ($this->onset) {
                $onSet = $this->onset->getValue();
                if ($this->onset->getTimestamp()){
                    $variants->add($this->onset->asDatetime(TDateTime::FORMAT_MMM_DD_YYYY), $this->onset->getTimestamp());
                }
            }

            if ($this->reaction){
                foreach($this->reaction as $reaction){
                    /** @var $reaction TReaction */
                    if (is_null($onSet)){
                        if ($reaction->onset){
                            $onSet = $reaction->onset->getValue();
                        }
                    }
                    if ($reaction->onset){
                        if ($reaction->onset->getTimestamp()){
                            $variants->add($reaction->onset->asDatetime(TDateTime::FORMAT_MMM_DD_YYYY), $reaction->onset->getTimestamp());
                        }
                    }
                }
            }

            $onSet =  $variants->getFirstByTime();
        } catch (\Exception $e){}

        return $onSet;
    }

    /**
     * Return allergy status
     * @return mixed|null|string
     */
    public function getStatus()
    {
        $result = null;
        if(!is_null($this->status)){
            $result = $this->status->getValue();
        }
        return $result;
    }

    /**
     * Is this resource valid
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     * Return is no allergy flag
     * @return bool
     */
    public function isNoAllergy()
    {
        return false;
    }
}