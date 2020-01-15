<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 13:50
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Class TInstant
 * @package common\components\PerfectParser
 */
class TInstant extends TElement
{
    /**
     * @inheritdoc
     */
    protected function _validate($params = null)
    {
        $time = strtotime($this->getOriginalValue());

        if ($time === false) {
            $this->log('%YInstant is not valid.%n');
            return false;
        }

        return true;
    }
}