<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 23.08.18
 * Time: 21:24
 */

namespace common\components\PerfectParser\DataSources\CCDA\resources;

use common\components\PerfectParser\Resources\RAllergies;
use common\helpers\Convert;
use SimpleXMLElement;

/**
 * Class RAllergy
 * @package common\components\PerfectParser\DataSources\CCDA\resources
 */
class RAllergy extends RResource
{
    /** @var string  */
    const FIRST_RESOURCE_CODE = '48765-2';
    /** @var string  */
    const FIRST_RESOURCE_CODE_SYSTEM = '2.16.840.1.113883.6.1';
    /** @var string  */
    const SECOND_RESOURCE_CODE = 'CONC';
    /** @var string  */
    const SECOND_RESOURCE_CODE_SYSTEM = '2.16.840.1.113883.5.6';
    /** @var string  */
    const THIRD_RESOURCE_CODE = '106190000';
    /** @var string  */
    const THIRD_RESOURCE_CODE_SYSTEM = '2.16.840.1.113883.6.1';
    /** @var string  */
    const ENTRY_RELATIONSHIP_TEMPLATE_ID = '2.16.840.1.113883.10.20.22.4.7';
    /** @var string  */
    const ALLERGY_REACTION_TEMPLATE_ID = '2.16.840.1.113883.10.20.22.4.9';
    /** @var string  */
    const ALLERGY_SEVERITY_TEMPLATE_ID = '2.16.840.1.113883.10.20.22.4.8';
    /** @var string  */
    const ALLERGY_SEVERITY_CODE = 'SEV';
    /** @var string  */
    const INVALID_ALLERGY_CODE_NULL_FLAVOR = 'NA';
    /** @var string  */
    const INVALID_ALLERGY_ACT_NULL_FLAVOR = 'NI';
    /** @var array  */
    const ALLERGIES_TYPES = [
        '414285001' => 'food',
        '416098002' => 'drug',
        '419199007' => 'substance'
    ];
    /**
     *
     */
    const ALLERGY_STATUSES = [
        '5561003' => RAllergies::STATUS_ACTIVE,
        '73425007' => RAllergies::STATUS_INACTIVE
    ];
    /** @var string  */
    const ALLERGY_STATUS_CODE = '33999-4';
    /** @var string  */
    const ALLERGY_STATUS_CODE_SYSTEM = '2.16.840.1.113883.6.1';

    /** @var string Name of the property that contains resource data in the resource entries */
    public $containerName = 'act';

    /** @var SimpleXMLElement */
    public $templateId;

    /** @var SimpleXMLElement */
    public $id;

    /** @var SimpleXMLElement */
    public $code;

    /** @var SimpleXMLElement */
    public $statusCode;

    /** @var SimpleXMLElement */
    public $effectiveTime;

    /** @var SimpleXMLElement */
    public $entryRelationship;


    /**
     * Return date of onset
     * @return null|string
     */
    public function getDateOnSet()
    {
        $result = null;

        if (isset($this->effectiveTime->low['value'])) {
            $resultRaw = (string)$this->effectiveTime->low['value'];

            $result = Convert::optimalFormatDate($resultRaw);
        }

        return $result;
    }

    /**
     * Is this resource valid
     * @return bool
     */
    public function isValid()
    {
        // checking allergy code
        if (
            isset($this->code['code']) &&
            isset($this->code['codeSystem']) &&
            ($this->code['code'] == self::FIRST_RESOURCE_CODE && $this->code['codeSystem'] == self::FIRST_RESOURCE_CODE_SYSTEM) ||
            ($this->code['code'] == self::SECOND_RESOURCE_CODE && $this->code['codeSystem'] == self::SECOND_RESOURCE_CODE_SYSTEM) ||
            ($this->code['code'] == self::THIRD_RESOURCE_CODE && $this->code['codeSystem'] == self::THIRD_RESOURCE_CODE_SYSTEM)
        ) {
            // checking invalid params
            if (
                (!isset($this->entryRelationship->observation['negationInd']) || $this->entryRelationship->observation['negationInd'] != 'true') &&
                (!isset($this->code['nullFlavor']) || $this->code['nullFlavor'] != self::INVALID_ALLERGY_CODE_NULL_FLAVOR) &&
                (!isset($this->containerElement['nullFlavor']) || $this->containerElement['nullFlavor'] != self::INVALID_ALLERGY_ACT_NULL_FLAVOR)
            ) {
                return true;
            }
        }



        return false;
    }

    /**
     * Return allergy type
     * @return null|string
     */
    public function getType()
    {
        $result = null;

        // entryRelationship templateId is valid
        if (
            isset($this->entryRelationship->observation->templateId['root']) &&
            $this->entryRelationship->observation->templateId['root'] == self::ENTRY_RELATIONSHIP_TEMPLATE_ID
        ) {
            // value code is exist
            if (isset($this->entryRelationship->observation->value['code'])) {
                $code = trim(strval($this->entryRelationship->observation->value['code']));

                if (isset(self::ALLERGIES_TYPES[$code])) {
                    $result = self::ALLERGIES_TYPES[$code];
                } else {
                    \Yii::$app->perfectParser->log("Skip unknown allergy status: $code");
                }
            }
        }

        return $result;
    }

    /**
     * Return allergy text
     * @return null|string
     */
    public function getAllergyText()
    {
        $result = null;

        // entryRelationship templateId is valid
        if (
            isset($this->entryRelationship->observation->templateId) &&
            $this->entryRelationship->observation->templateId['root'] == self::ENTRY_RELATIONSHIP_TEMPLATE_ID
        ) {
            // First fetch try (from value->originalText)
            // is originalText exist
            if (!empty($this->entryRelationship->observation->value->originalText)) {
                $originalTextElement = $this->entryRelationship->observation->value->originalText;

                // first try get allergy text from originalText value
                $text = trim(strval($originalTextElement));
                if (!empty($text)) {
                    $result = $text;

                    return $result;
                // second try get allergy text from originalText reference
                } elseif (isset($originalTextElement->reference['value'])) {
                    $referenceId = (string) $originalTextElement->reference['value'];
                    $result = (string) $this->getReferencedData($referenceId);

                    if ($result) {
                        return $result;
                    }
                }
            }

            // Second fetch try (from participant data)
            if ($this->entryRelationship->observation->participant->participantRole->playingEntity->code) {
                $code = $this->entryRelationship->observation->participant->participantRole->playingEntity->code;

                if (!empty($code->originalText)) {
                    $originalTextElement = $code->originalText;

                    // first try get allergy text from originalText value
                    $text = trim(strval($originalTextElement));
                    if (!empty($text)) {
                        $result = $text;

                        return $result;

                        // second try get allergy text from originalText reference
                    } elseif (isset($originalTextElement->reference['value'])) {
                        $referenceId = (string) $originalTextElement->reference['value'];
                        $result = (string) $this->getReferencedData($referenceId);

                        if ($result) {
                            return $result;
                        }
                    }
                }

                if (!empty($code['displayName'])) {
                    $result = $code['displayName'];

                    return $result;
                }
            }

        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isSubstancePresent()
    {
        return true;
    }


    /**
     * Return allergy reaction text
     *
     * @return null|string
     */
    public function getReactionText() {
        $result = null;

        // searching entryRelationship with desired template id
        if (!empty($this->entryRelationship->observation->entryRelationship)) {
            $entryRelationships = $this->entryRelationship->observation->entryRelationship;

            foreach ($entryRelationships as $entryRelationship) {
                $observation = $entryRelationship->observation;

                if (isset($observation->templateId['root']) && $observation->templateId['root'] == self::ALLERGY_REACTION_TEMPLATE_ID)  {

                    // First Try - get allergy reaction from text element
                    if (isset($observation->text)) {

                        // get from plain text
                        if (!empty($text = trim(strval($observation->text)))) {
                            $result = $text;

                        // get from reference
                        } elseif (isset($observation->text->reference['value'])) {
                            $referenceId = (string) $observation->text->reference['value'];
                            $result = (string) $this->getReferencedData($referenceId);
                        }

                    // Second Try - get allergy reaction from originalText element
                    } elseif (isset($observation->originalText)) {

                        // get from plain text
                        if (!empty($text = trim(strval($observation->originalText)))) {
                            $result = $text;

                        // get from reference
                        } elseif (isset($observation->originalText->reference['value'])) {
                            $referenceId = (string) $observation->originalText->reference['value'];
                            $result = (string) $this->getReferencedData($referenceId);
                        }
                    }
                }

                if ($result) {
                    break;
                }
            }
        }

        return $result;
    }
    /**
     * Return allergy severity text
     *
     * @return null|string
     */
    public function getSeverityText() {
        $result = null;

        // Checking third level entryRelationship
        if (isset($this->entryRelationship->observation->entryRelationship->observation->entryRelationship->observation)) {
            $observation = $this->entryRelationship->observation->entryRelationship->observation->entryRelationship->observation;

            // validate this entryRelationship
            if (
                (isset($observation->templateId['root']) && $observation->templateId['root'] == self::ALLERGY_SEVERITY_TEMPLATE_ID) ||
                (isset($observation->code['code']) && $observation->code['code'] == self::ALLERGY_SEVERITY_CODE)
            ) {
                    // get allergy severity from text element
                    if (isset($observation->text)) {

                        // get from plain text
                        if (!empty($text = trim(strval($observation->text)))) {
                            $result = $text;

                        // get from reference
                        } elseif (isset($observation->text->reference['value'])) {
                            $referenceId = (string)$observation->text->reference['value'];
                            $result = (string)$this->getReferencedData($referenceId);
                        }

                        if ($result) {
                            return $result;
                        }
                    }
            }
        }


        // search desired second level entryRelationship
        if (isset($this->entryRelationship->observation->entryRelationship)) {
            $entryRelationships = $this->entryRelationship->observation->entryRelationship;

            foreach ($entryRelationships as $entryRelationship) {
                $observation = $entryRelationship->observation;

                // validate this entryRelationship
                if (
                    (isset($observation->templateId['root']) && $observation->templateId['root'] == self::ALLERGY_SEVERITY_TEMPLATE_ID) ||
                    (isset($observation->code['code']) && $observation->code['code'] == self::ALLERGY_SEVERITY_CODE)
                ) {
                    // get allergy severity from text element
                    if (isset($observation->text)) {

                        // get from plain text
                        if (!empty($text = trim(strval($observation->text)))) {
                            $result = $text;

                            // get from reference
                        } elseif (isset($observation->text->reference['value'])) {
                            $referenceId = (string)$observation->text->reference['value'];
                            $result = (string)$this->getReferencedData($referenceId);
                        }

                        if ($result) {
                            return $result;
                        }
                    }
                }
            }
        }
        return $result;
    }


    /**
     * Return allergy status
     *
     * @return null|string
     */
    public function getStatus()
    {
        // First Try get status
        // searching desired entryRelationship
        if (isset($this->entryRelationship->observation->entryRelationship)) {
            $entryRelationships = $this->entryRelationship->observation->entryRelationship;

            foreach ($entryRelationships as $entryRelationship) {
                $observation = $entryRelationship->observation;

                // validate this entryRelationship
                if (
                    (isset($observation->code['code']) && $observation->code['code'] == self::ALLERGY_STATUS_CODE) &&
                    (isset($observation->code['codeSystem']) && $observation->code['codeSystem'] == self::ALLERGY_STATUS_CODE_SYSTEM)
                ) {
                    // get from value code
                    if (isset($observation->value['code'])) {
                        $code = strval($observation->value['code']);

                        if (isset(self::ALLERGY_STATUSES[$code])) {
                            $resultRaw = self::ALLERGY_STATUSES[$code];
                            $result = ucfirst(trim(strval($resultRaw)));

                            return $result;
                        }
                    }
                    // get from value displayName
                    if (isset($observation->value['displayName'])) {
                        $resultRaw = $observation->value['displayName'];
                        $result = ucfirst(trim(strval($resultRaw)));

                        return $result;
                    }
                }
            }
        }

        // Second Try get status
        // checking allergy end date
        if (isset($this->effectiveTime->high['value'])) {
            $resultRaw = RAllergies::STATUS_INACTIVE;
            $result = ucfirst(trim(strval($resultRaw)));

            return $result;
        }

        // Third Try get status
        // checking from main status element
        if (isset($this->statusCode['code']) && $this->statusCode['code'] == RAllergies::STATUS_ACTIVE) {
            $resultRaw = $this->statusCode['code'];
            $result = ucfirst(trim(strval($resultRaw)));

            return $result;
        }

        return null;
    }

    /**
     * Return is no allergy flag
     * @return bool
     */
    public function isNoAllergy()
    {
        $result = false;

        if (
            isset($this->entryRelationship->observation['negationInd']) &&
            $this->entryRelationship->observation['negationInd'] == true
        ) {
            $result = true;
        }

        return $result;
    }
}