<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:51
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;

use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class TCodeableConcept
 * @package common\components\PerfectParser
 *
 * @property null|string $identifier
 * @property null|string $system
 */
class TCodeableConcept extends TComplex
{
    /** @var TCoding Code defined by a terminology system */
    public $coding;

    /** @var TString Plain text representation of the concept */
    public $text;

    /**
     * @inheritdoc
     * @return array
     */
    public function structure()
    {
        return [
            ['coding', [TCoding::class]],
            ['text', TString::class]
        ];
    }

    /**
     * Return String representation of Codeable Concept Value
     * according to https://commlifesolutions.atlassian.net/wiki/spaces/MT/pages/9535543/MedFusion+Integration#MedFusionIntegration-CodeableConcept
     * @return string
     */
    public function getValue()
    {
        // 1. iterate over all "coding" entries, and use the "display" value from the entry that has "userSelected" set to true (1). If there isn't one then...
        if ($this->coding){
            foreach ($this->coding as $coding){
                /** @var $coding TCoding */
                if ($coding->userSelected && $coding->userSelected->getValue()){
                    if ($coding->display && $coding->display->getValue()){
                        return $coding->display->getValue();
                    }
                }
            }

        }

        // 2. Use the "codeable_concept" elements "text" child.  If there isn't one then...
        if ($this->text && $this->text->getValue()){
            return $this->text->getValue();
        }

        // 3. Take any "display" value from any "coding" element, probably the first one that has one.
        if ($this->coding){
            foreach ($this->coding as $coding){
                /** @var $coding TCoding */
                if ($coding->display && $coding->display->getValue()){
                    return $coding->display->getValue();
                }
            }

        }

        return '';
    }

    /**
     * Gets CodeableConcept identifier, based on all existing attributes
     * @return string|null
     */
    public function getIdentifier()
    {
        if (isset($this->coding)) {
            // prefer to choose entry that has both code and text
            foreach ($this->coding as $coding) {
                if (isset($coding->code) && $coding->code->getValue() && isset($coding->display) && $coding->display->getValue()) {
                    return $coding->display->getValue();
                }
            }

            // otherwise, choose first entry that has text and live with no code
            foreach ($this->coding as $coding) {
                if (isset($coding->display) && $coding->display->getValue()) {
                    return $coding->display->getValue();
                }
            }

            // if only code is available, then pick the first one and leave lab_request_id_text blank.
            foreach ($this->coding as $coding) {
                if (isset($coding->code) && $coding->code->getValue()) {
                    return null;
                }
            }
        }

        if (isset($this->text) && $this->text->getValue()) {
            return $this->text->getValue();
        }

        return null;
    }

    /**
     * Gets CodeableConcept Coding value
     * @return string|null
     */
    public function getCoding()
    {
        if (isset($this->coding)) {
            foreach ($this->coding as $coding) {
                if (isset($coding->code) && $coding->code->getValue() && isset($coding->display) && $coding->display->getValue()) {
                    return $coding->code->getValue();
                }
            }

            foreach ($this->coding as $coding) {
                if (isset($coding->display) && $coding->display->getValue()) {
                    if (isset($coding->code)) {
                        return $coding->code->getValue();
                    }
                }
            }
        }

        if (isset($this->text) && $this->text->getValue()) {
            return $this->text->getValue();
        }

        return null;
    }

    /**
     * Gets CodeableConcept System value
     * @return string|null
     */
    public function getSystem()
    {
        if (isset($this->coding)) {
            foreach ($this->coding as $coding) {
                if (isset($coding->code) && $coding->code->getValue() && isset($coding->display) && $coding->display->getValue() && isset($coding->system) && $coding->system->getValue()) {
                    return $coding->system->getValue();
                }
            }

            foreach ($this->coding as $coding) {
                if (isset($coding->system) && $coding->system->getValue()) {
                    return $coding->system->getValue();
                }
            }
        }

        if (isset($this->text) && $this->text->getValue()) {
            return $this->text->getValue();
        }

        return null;
    }

}
