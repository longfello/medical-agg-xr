<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:01
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Class TCode
 * @package common\components\PerfectParser
 */
/**
 * Class TCode
 * @package common\components\PerfectParser
 */
class TCode extends TElement
{
    /**
     * @const string Height code
     */
    const CODE_HEIGHT       = '8302-2';
    /**
     * @const string Height marker
     */
    const MARKER_HEIGHT     = 'height';
    /**
     * @const string Weight code
     */
    const CODE_WEIGHT       = '3141-9';
    /**
     * @const string Weight marker
     */
    const MARKER_WEIGHT     = 'weight';
    /**
     * @return bool
     */
    protected function _validate()
    {
        if (preg_match('/[^\s]+([\s]?[^\s]+)*/', $this->getOriginalValue())) {
            return true;
        }
        return false;
    }
}