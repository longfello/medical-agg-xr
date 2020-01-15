<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:02
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Class TOid
 * @package common\components\PerfectParser
 */
class TOid extends TElement
{
    /**
     * @inheritdoc
     */
    protected function _validate($params = null)
    {
        try {
            $value = $this->getOriginalValue();
            $matches = [];
            preg_match('/urn:oid:[0-2](\.[1-9]\d*)+/', $value, $matches);

            if ($matches) {
                return true;
            }
            else {
                $this->log('%YOid is not valid.%n');
            }
        }
        catch (\Exception $e) {
            $this->log('%Y'.$e->getMessage().'%n');
        }

        return false;
    }
}