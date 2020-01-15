<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:03
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;


/**
 * Class TUnsignedInt
 * @package common\components\PerfectParser
 */
class TUnsignedInt extends TInteger
{
    /**
     * @inheritdoc
     */
    protected function _validate($params = null)
    {
        try {
            $value = $this->getOriginalValue();

            if (is_int($value) && (int) $value >= 0) {
                $matches = [];
                preg_match('/[0]|([1-9][0-9]*)/', $value, $matches);

                if (!$matches) {
                    $this->log('%YInteger does not match regexp.%n');
                }
            }
            else {
                $this->log('%YInteger is not unsigned.%n');
            }

            return true;
        }
        catch (\Exception $e) {
            $this->log('%Y'.$e->getMessage().'%n');
        }

        return false;
    }
}