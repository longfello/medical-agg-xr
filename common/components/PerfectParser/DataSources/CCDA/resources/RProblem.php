<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 23.08.18
 * Time: 21:24
 */

namespace common\components\PerfectParser\DataSources\CCDA\resources;


use common\helpers\Convert;
use common\models\Problems;
use SimpleXMLElement;

/**
 * Class RProblem
 * @package common\components\PerfectParser\DataSources\CCDA\resources
 */
class RProblem extends RResource
{
    /** @var string  */
    const PROBLEM_TEXT_CODE = '55607006';
    /** @var string  */
    const PROBLEM_TEXT_CODE_SYSTEM = '2.16.840.1.113883.6.96';
    /** @var string  */
    const PROBLEM_STATUS_BLOCK_CODE = '33999-4';
    /** @var string  */
    const PROBLEM_STATUS_BLOCK_CODE_SYSTEM = '2.16.840.1.113883.6.1';
    /** @var array  */
    const PROBLEMS_STATUS_CODES = [
        '55561003' => Problems::STATUS_ACTIVE,
        '73425007' => Problems::STATUS_INACTIVE,
        '413322009' => Problems::STATUS_RESOLVED
    ];
    /** @var string  */
    const PROBLEM_ICD10_CODE_SYSTEM = '2.16.840.1.113883.6.90';

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

    /** @var SimpleXMLElement */
    public $value;

    /**
     * Return problem text
     *
     * @return null|string
     */
    public function getProblemText()
    {
        $result = null;

        // checking required problem text code data
        if (
            isset($this->entryRelationship->observation->code['code']) &&
            isset($this->entryRelationship->observation->code['codeSystem']) &&
            $this->entryRelationship->observation->code['code'] == self::PROBLEM_TEXT_CODE &&
            $this->entryRelationship->observation->code['codeSystem'] == self::PROBLEM_TEXT_CODE_SYSTEM
        ) {
            // get problem text reference
            if ($this->entryRelationship->observation->text->reference['value']) {
                $reference = (string) $this->entryRelationship->observation->text->reference['value'];
                $result = (string) $this->getReferencedData($reference);
            }
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getProblemListStatus()
    {
        $result = null;

        // checking problem status code data
        if (
            isset($this->entryRelationship->observation->entryRelationship->observation->code['code']) &&
            isset($this->entryRelationship->observation->entryRelationship->observation->code['codeSystem']) &&
            $this->entryRelationship->observation->entryRelationship->observation->code['code'] == self::PROBLEM_STATUS_BLOCK_CODE &&
            $this->entryRelationship->observation->entryRelationship->observation->code['codeSystem'] == self::PROBLEM_STATUS_BLOCK_CODE_SYSTEM
        ) {
            $statusBlock = $this->entryRelationship->observation->entryRelationship->observation;

            // first try fetch status
            if (isset($statusBlock->text)) {
                if (isset($statusBlock->text->reference['value'])) {
                    $reference = (string) $statusBlock->text->reference['value'];
                    $result = (string) $this->getReferencedData($reference);
                } else {
                    $result = ucfirst(trim(strval($statusBlock->text)));
                }

            // second try fetch status (from value element)
            } elseif (isset($statusBlock->value['code'])) {

                // get problem status from value code
                $valueCode = strval($statusBlock->value['code']);
                if (isset(self::PROBLEMS_STATUS_CODES[$valueCode])) {
                    $result = ucfirst(trim(self::PROBLEMS_STATUS_CODES[$valueCode]));

                // else get problem status from displayName
                } elseif (isset($statusBlock->value['displayName'])) {
                    $result = ucfirst(trim(strval($statusBlock->value['displayName'])));
                }
            }
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getStatus()
    {
        return null;
    }

    /**
     * Return problem active date
     *
     * @return null|string
     */
    public function getProblemActiveDate()
    {
        $result = null;

        if (!empty($this->effectiveTime->low['value'])) {
            $activeDataRaw = (string)$this->effectiveTime->low['value'];

            $result = Convert::optimalFormatDate($activeDataRaw);
        }

        return $result;
    }

    /**
     * Return problem end date
     *
     * @return null|string
     */
    public function getProblemEndDate()
    {
        $result = null;

        if (!empty($this->effectiveTime->high['value'])) {
            $endDateRaw = (string)$this->effectiveTime->high['value'];
            $result = Convert::optimalFormatDate($endDateRaw);
        }

        return $result;
    }

    /**
     * Return problem icd-10
     *
     * @return null|string
     */
    public function getProblemIcd10()
    {
        $result = null;

        // checking code data of problem icd-10
        if (isset($this->entryRelationship->observation->value->translation['codeSystem'])) {
            $codeSystem = trim($this->entryRelationship->observation->value->translation['codeSystem'], " \"\'");
            if ($codeSystem == self::PROBLEM_ICD10_CODE_SYSTEM) {
                $result = strval($this->entryRelationship->observation->value->translation['code']);
            }
        }
        return $result;
    }
}