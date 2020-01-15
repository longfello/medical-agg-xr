<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 13:49
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Class TBase64Binary
 * @package common\components\PerfectParser
 * Represents FHIR Base64Binary data type
 */
class TBase64Binary extends TElement
{
    /**
     * @return bool
     */
    public function _validate()
    {
        $out = (bool)$this->getOriginalValue();
        if (base64_decode($out, true) === false)
        {
            return false;
        }
        return true;
    }
}