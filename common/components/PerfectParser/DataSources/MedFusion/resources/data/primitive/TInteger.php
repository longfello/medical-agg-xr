<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 13:47
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Class TInteger
 * @package common\components\PerfectParser
 */
class TInteger extends TElement
{
    /**
     * @inheritdoc
     */
    protected function _validate($params = null)
    {
        try {
            $value = $this->getOriginalValue();

            if (is_numeric($value)) {
                $value = (int)$value;
                $this->setOriginalValue($value);
                $matches = [];
                preg_match('/[0]|[-+]?[1-9][0-9]*/', $value, $matches);

                if (!$matches) {
                    $this->log('%YInteger does not match regexp.%n');
                }
            }
            else {
                $this->log('%YInteger is not valid: '.json_encode($value).'%n');
            }

            return true;
        }
        catch (\Exception $e) {
            $this->log('%Y'.$e->getMessage().'%n');
        }

        return false;
    }
}