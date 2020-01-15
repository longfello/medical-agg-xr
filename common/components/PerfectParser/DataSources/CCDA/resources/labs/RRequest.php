<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 21.10.18
 * Time: 16:03
 */

namespace common\components\PerfectParser\DataSources\CCDA\resources\labs;


use common\helpers\Convert;
use SimpleXMLElement;

/**
 * Class RRequest
 * @package common\components\PerfectParser\DataSources\CCDA\resources\labs
 */
class RRequest extends SubResource
{
    /** @var string  */
    const CODE_NULL_FLAVOR_INVALID_REPORT = 'NI';
    /** @var string  */
    const CODE_NULL_FLAVOR_INVALID_CODE_PARAMS = 'NA';

    /** @var SimpleXMLElement */
    public $effectiveTime;

    /** @var SimpleXMLElement */
    public $code;

    /** @var SimpleXMLElement */
    public $component;

    /**
     * @return null|string
     */
    public function getRequestObservationDate()
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
    public function getRequestObservationEndDate()
    {
        return null;
    }

    /**
     * @return null|string
     */
    public function getRequestIdCoding()
    {
        $result = null;

        if (!isset($this->code['nullFlavor'])) {
            if (!empty($this->code['code'])) {
                $result = (string)$this->code['code'];
            }
        }
	else if (isset($this->code->translation) && (!empty($this->code->translation['code']))) {
	    $result = (string)$this->code->translation['code'];
	}
	
        return $result;
    }

    /**
     * @return null|string
     */
    public function getRequestIdSystem()
    {
        $result = null;

        if (!isset($this->code['nullFlavor'])) {
            if (!empty($this->code['codeSystemName'])) {
                $result = (string)$this->code['codeSystemName'];
            }
        }
	else if (isset($this->code->translation) && (!empty($this->code->translation['codeSystemName']))) {
	    $result = (string)$this->code->translation['codeSystemName'];
	}

        return $result;
    }

    /**
     * @return null|string
     */
    public function getRequestIdentifier()
    {
        $result = NULL;

        if (!isset($this->code['nullFlavor'])) {
            if (!empty($this->code['displayName'])) {
                $result = (string)$this->code['displayName'];
            }
        }
	else if (isset($this->code->translation) && (!empty($this->code->translation['displayName']))) {
	    $result = (string)$this->code->translation['displayName'];
	}
	else if (isset($this->code->originalText) && !empty($this->code->originalText)) {
	    $result = (string)$this->code->originalText;
	}
	
        return $result;
    }

    /**
     * @return null|string
     */
    public function getRequestIdText()
    {
        return null;
    }


    /**
     * Get external observations resources
     * @return array
     */
    public function getObservations()
    {
        $result = [];

        if (!empty($this->component)) { // component is wrapper for observation structure
            $components = !is_array($this->component) ? [$this->component] : $this->component;

            foreach ($components as $component) {
                // searching observation structure
                if (
                    isset($component->observation) &&
                    isset($component->observation['classCode']) &&
                    $component->observation['classCode'] == 'OBS' &&
                    isset($component->observation['moodCode']) &&
                    $component->observation['moodCode'] == 'EVN'
                ) {
                    $observation = $component->observation;
                    // required code & value fields
                    if (isset($observation->code) && isset($observation->value) ) {
                        $observation = new RObservation($observation[0]);
                        $observation->references = $this->references;
                        $result[] = $observation;
                    }
                }

            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function IsSkipReport()
    {
        // first case
        if (isset($this->code['nullFlavor']) && $this->code['nullFlavor'] == self::CODE_NULL_FLAVOR_INVALID_REPORT) {
            return true;
        }

        // second case - single observation with nullFlavor=NI
        $observations = $this->getObservations();
        if (is_array($observations) && count($observations) == 1 && !empty($observations[0]) ) {
            $observation = $observations[0];

            if ($observation->isInvalid()) {
                return true;
            }
        }

        return false;
    }
}