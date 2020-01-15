<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 22.10.18
 * Time: 15:28
 */

namespace common\components\PerfectParser\DataSources\CCDA\resources\labs;

use common\helpers\Convert;
use SimpleXMLElement;

/**
 * Class RObservation
 * @package common\components\PerfectParser\DataSources\CCDA\resources\labs
 */
class RObservation extends SubResource
{
    /** @var string  */
    const INVALID_NULL_FLAVOR = 'NI';

    /** @var string  */
    const NTES_CODE_IDENTITY = '48767-8';

    /** @var SimpleXMLElement */
    public $code;
    /** @var SimpleXMLElement */
    public $effectiveTime;
    /** @var SimpleXMLElement */
    public $value;
    /** @var SimpleXMLElement */
    public $referenceRange;
    /** @var SimpleXMLElement */
    public $interpretationCode;
    /** @var SimpleXMLElement */
    public $entryRelationship;
    /**
     * @return null|string
     */
    public function getObservationIdentifier()
    {
        $result = null;

        if (!empty($this->code['displayName'])) {
            $result = (string)$this->code['displayName'];
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationIdCoding()
    {
        $result = null;

        if (!empty($this->code['code'])) {
            $result = (string)$this->code['code'];
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationIdSystem()
    {
        $result = null;

        if (!empty($this->code['codeSystemName'])) {
            $result = (string)$this->code['codeSystemName'];
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationIdText()
    {
        $result = null;

        if (isset($this->code->originalText->reference['value'])) {
            $referenceId = (string) $this->code->originalText->reference['value'];
            $result = (string) $this->getReferencedData($referenceId);
        } elseif (!empty(strval($this->code->originalText))) {
            $result = strval($this->code->originalText);
        }

        return $result;
    }


    /**
     * @return null|string
     */
    public function getObservationDate()
    {
        $result = null;

        // first try
        if (isset($this->effectiveTime['value'])) {
            $resultRaw = (string)$this->effectiveTime['value'];

            $result = Convert::optimalFormatDate($resultRaw);
        }

        // second try
        if (empty($result) && isset($this->effectiveTime->low['value'])) {
            $resultRaw = (string)$this->effectiveTime->low['value'];

            $result = Convert::optimalFormatDate($resultRaw);
        }

        return $result;
    }


    /**
     * @return null|string
     */
    public function getObservationValue()
    {
        $result = null;

        if (isset($this->value)) {
            // first try
            if (isset($this->value['value']) && isset($this->value['unit'])) {
                $result = (string)$this->value['value'];
            } else {
                $value = $this->value;

                if (!empty($value)) {
                    $value = is_array($value) ? $value[0] : $value;
                    $result = (string)$value;
                }
            }
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationUnits()
    {
        $result = null;

        if (isset($this->value['value']) && isset($this->value['unit'])) {
            $result = (string)$this->value['unit'];
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationRange()
    {
        $result = null;

        if (isset($this->referenceRange->observationRange->text)) {
            $text = strval($this->referenceRange->observationRange->text);

            if ($text != '') {
                $result = $text;
            }
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationResultStatus()
    {
        return null;
    }

    /**
     * @return null|string
     */
    public function getObservationFlagCode()
    {
        $result = null;

        if (isset($this->interpretationCode['code'])) {
            if (
                !isset($this->interpretationCode['nullFlavor']) ||
                (isset($this->interpretationCode['nullFlavor']) && $this->interpretationCode['nullFlavor'] != self::INVALID_NULL_FLAVOR)
            ) {
                $result = (string) $this->interpretationCode['code'];
            }
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getObservationFlagDescription()
    {
        $result = null;

        if (isset($this->interpretationCode['code'])) {
            $result = (string) $this->interpretationCode['code'];
        }

        return $result;
    }

    /**
     * Get external observations resources
     * @return array
     */
    public function getNtes()
    {
        $result = [];

        if (!empty($this->entryRelationship)) {
            $entryRelationshipArray = !is_array($this->entryRelationship) ? [$this->entryRelationship] : $this->entryRelationship;

            foreach ($entryRelationshipArray as $entryRelationship) {
                if (
                    isset($entryRelationship->act->code['code']) &&
                    $entryRelationship->act->code['code'] == self::NTES_CODE_IDENTITY &&
                    (!empty($entryRelationship->act->text) || strval($entryRelationship->act->text) === '0')
                ) {
                    $nte = new RNte($entryRelationship);
                    $nte->references = $this->references;
                    $result[] = $nte;
                }
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isInvalid()
    {
        if(isset($this->contentRaw['nullFlavor']) && $this->contentRaw['nullFlavor'] == self::INVALID_NULL_FLAVOR) {
            return true;
        }

        return false;
    }
}