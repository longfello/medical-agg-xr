<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 13:48
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Class TDecimal
 * @package common\components\PerfectParser
 */
/**
 * Class TDecimal
 * @package common\components\PerfectParser
 */
class TDecimal extends TElement
{
    /**
     * @inheritdoc
     */
    protected function _validate($params = null)
    {
        try {
            $value = $this->getOriginalValue();

            if (!is_float($value) && $value == 0) {
                $this->log('%YDecimal is not valid.%n');
            }

            return true;
        }
        catch (\Exception $e) {
            $this->log('%Y'.$e->getMessage().'%n');
        }

        return false;
    }

    /**
     * @return float|mixed
     */
    public function getValue()
    {
        $value = parent::getValue();
        try{
            $value = floatval($value);
        } catch (\Throwable $e){

        }
        return $value;
    }
}