<?php
namespace common\components\PerfectParser\DataSources\CCDA\resources;

use common\helpers\Convert;

/**
 * Class RMedication
 * @package common\components\PerfectParser\DataSources\CCDA\resources
 */
class RMedication extends RResource
{
    /** @const string */
    const HL7_DATA_TYPE_IVL_TS = 'IVL_TS';
    /** @const string */
    const XSI_NAMESPACE = 'http://www.w3.org/2001/XMLSchema-instance';
    const FIRST_TEMPLATE_ID_ROOT_DIRECTION_TO_PATIENT = '2.16.840.1.113883.10.20.22.4.20';
    /** @const string */
    const SECOND_TEMPLATE_ID_ROOT_DIRECTION_TO_PATIENT = '2.16.840.1.113883.10.20.22.4.147';
    /** @const string */
    const FIRST_CODE_DIRECTION_TO_PATIENT = '423564006';
    /** @const string */
    const SECOND_CODE_DIRECTION_TO_PATIENT = '76662-6';
    /** @const string */
    const FIRST_CODE_SYSTEM_DIRECTION_TO_PATIENT = '2.16.840.1.113883.6.96';
    /** @const string */
    const SECOND_CODE_SYSTEM_DIRECTION_TO_PATIENT = '2.16.840.1.113883.6.1';


    /** @property string $identity */

    /** @var string Name of the property that contains resource data in the resource entries */
    public $containerName = 'substanceAdministration';

    /** @var SimpleXMLElement Instance identifier */
    public $templateId;

    /** @var SimpleXMLElement Instance identifier */
    public $id;

    /** @var SimpleXMLElement Reference to Medication name */
    public $text;

    /** @var SimpleXMLElement Medication's status */
    public $statusCode;

    /** @var SimpleXMLElement Start and end time of medication's administration */
    public $effectiveTime;

    /** @var SimpleXMLElement Details of how medication was taken */
    public $routeCode;

    /** @var SimpleXMLElement Details of how medication was taken */
    public $doseQuantity;

    /** @var SimpleXMLElement */
    public $consumable;

    /** @var SimpleXMLElement */
    public $entryRelationship;


    /**
     *
     * @return string
     */
    public function getIdentity()
    {
        $rootId = (string) $this->id['root'];
        $extId = (isset($this->id['extension']) ? '-'.$this->id['extension'] : '');
        return $rootId.$extId;
    }

    /**
     *
     * @return string|null
     */
    public function getMedicationText()
    {
        if (isset($this->consumable->manufacturedProduct->manufacturedMaterial->code->originalText->reference['value'])) {
            $reference = (string) $this->consumable->manufacturedProduct->manufacturedMaterial->code->originalText->reference['value'];
            return (string) $this->getReferencedData($reference);
        }
        return null;
    }

    /**
     *
     * @return string|null
     */
    public function getMedicationStrange()
    {
        if (isset($this->doseQuantity['value']) && isset($this->doseQuantity['unit'])) {
            return $this->doseQuantity['value'].' '.$this->doseQuantity['unit'];
        }
        return null;
    }

    /**
     *
     * @return string|null
     */
    public function getMedicationRoute()
    {
        if (isset($this->routeCode['displayName'])) {
            return (string) $this->routeCode['displayName'];
        }
        return null;
    }

    /**
     *
     * @return string|null
     */
    public function getMedicationDuration()
    {
        // What is related resource property?
        return null;
    }

    /**
     *
     * @return string|null
     */
    public function getMedicationDoseUnit()
    {
        if (isset($this->doseQuantity['unit'])) {
            return (string) $this->doseQuantity['unit'];
        }
        return null;
    }

    /**
     *
     * @return string|null
     */
    public function getMedicationDoseTiming()
    {
        if (isset($this->rateQuantity['unit'])) {
            return (string) $this->rateQuantity['unit'];
        }
        return null;
    }

    /**
     *
     * @return string|null
     */
    public function getMedicationNumRefills()
    {
        // What is related resource property?
        return null;
    }

    /**
     *
     * @return string|null
     */
    public function getMedicationDatePrescribed()
    {
        // in property not array - convert in array
        if (!is_array($this->effectiveTime)) {
            $this->effectiveTime = [$this->effectiveTime];
        }

        foreach($this->effectiveTime as $effectiveTime) {
            if (!empty($effectiveTime->attributes(self::XSI_NAMESPACE)->type) && $effectiveTime->attributes(self::XSI_NAMESPACE)->type == self::HL7_DATA_TYPE_IVL_TS) {
                if(isset($effectiveTime->low['value'])) {
                    $datetimeRaw = (string)$effectiveTime->low['value'];
                    return Convert::optimalFormatDate($datetimeRaw);
                }
            }
        }

        return null;
    }

    /**
     *
     * @return string|null
     */
    public function getMedicationEndDate()
    {
        // in property not array - convert in array
        if (!is_array($this->effectiveTime)) {
            $this->effectiveTime = [$this->effectiveTime];
        }

        foreach($this->effectiveTime as $effectiveTime) {
            if (!empty($effectiveTime->attributes(self::XSI_NAMESPACE)->type) && $effectiveTime->attributes(self::XSI_NAMESPACE)->type == self::HL7_DATA_TYPE_IVL_TS) {
                if(isset($effectiveTime->high['value'])) {
                    $datetimeRaw = $effectiveTime->high['value'];
                    return Convert::optimalFormatDate($datetimeRaw);
                }
            }
        }

        return null;
    }

    /**
     *
     * @return string|null
     */
    public function getMedicationStatus()
    {
        return $this->getStatus();
    }

    /**
     * @return bool|null|string
     */
    public function getMedicationDirectionToPatient()
    {
        // First and Second fetch try
        if (!empty($this->entryRelationship)) {
            // if not array - convert in it
            if(!is_array($this->entryRelationship)) {
                $this->entryRelationship = [$this->entryRelationship];
            }

            // First fetch try
            foreach($this->entryRelationship as $entryRelationship){
                $directionToPatient = $this->firstFetchTryDirectionToPatient($entryRelationship);
                if ($directionToPatient) {
                    return $directionToPatient;
                }
            }

            // Second fetch try
            foreach($this->entryRelationship as $entryRelationship){
                $directionToPatient = $this->secondFetchTryDirectionToPatient($entryRelationship);
                if ($directionToPatient) {
                    return $directionToPatient;
                }
            }
        }

        // Third fetch try
        if (isset($this->text->reference)) {
            $reference = (string) $this->text->reference['value'];
            return (string) $this->getReferencedData($reference);
        }
        return null;
    }

    /**
     * First fetch try direction to patient
     * @param $entryRelationship
     * @return bool|string
     *
     * required <entryRelationship><act><templateId root="2.16.840.1.113883.10.20.22.4.20"/>
     * and required <code code="423564006" codeSystem="2.16.840.1.113883.6.96">
     */
    public function firstFetchTryDirectionToPatient($entryRelationship)
    {
        $result = false;

        if (
            isset($entryRelationship->act->templateId['root']) &&
            $entryRelationship->act->templateId['root'] == self::FIRST_TEMPLATE_ID_ROOT_DIRECTION_TO_PATIENT
        ) {
            if (
                isset($entryRelationship->act->code['code']) &&
                isset($entryRelationship->act->code['codeSystem']) &&
                $entryRelationship->act->code['code'] == self::FIRST_CODE_DIRECTION_TO_PATIENT &&
                $entryRelationship->act->code['codeSystem'] == self::FIRST_CODE_SYSTEM_DIRECTION_TO_PATIENT
            ) {
                if (isset($entryRelationship->act->text->reference['value'])) {
                    $reference = (string) $entryRelationship->act->text->reference['value'];
                    $result = (string) $this->getReferencedData($reference);
                }
            }
        }

        return $result;
    }

    /**
     * Second fetch try direction to patient
     * @param $entryRelationship
     * @return bool|string
     *
     * required <entryRelationship><templateId root="2.16.840.1.113883.10.20.22.4.20"/>
     * and required <code code="76662-6" codeSystem="2.16.840.1.113883.6.1">
     */
    public function secondFetchTryDirectionToPatient($entryRelationship)
    {
        $result = false;

        if (!empty($entryRelationship->substanceAdministration[0])) {
            $entryRelationship = $entryRelationship->substanceAdministration[0];
        }

        if (
            isset($entryRelationship->templateId['root']) &&
            $entryRelationship->templateId['root'] == self::SECOND_TEMPLATE_ID_ROOT_DIRECTION_TO_PATIENT
        ) {
            if (
                isset($entryRelationship->code['code']) &&
                isset($entryRelationship->code['codeSystem']) &&
                $entryRelationship->code['code'] == self::SECOND_CODE_DIRECTION_TO_PATIENT &&
                $entryRelationship->code['codeSystem'] == self::SECOND_CODE_SYSTEM_DIRECTION_TO_PATIENT
            ) {
                if (isset($entryRelationship->text->reference['value'])) {
                    $reference = (string) $entryRelationship->text->reference['value'];
                    $result = (string) $this->getReferencedData($reference);
                }
            }
        }

        return $result;
    }
}
