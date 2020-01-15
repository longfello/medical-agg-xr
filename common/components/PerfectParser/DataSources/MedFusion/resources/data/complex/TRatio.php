<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:51
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;

/**
 * Class TRatio
 * @package common\components\PerfectParser
 */
class TRatio extends TComplex
{
    /** @var TQuantity Numerator value */
    public $numerator;

    /** @var TQuantity Denominator value */
    public $denominator;

    /**
     *
     */
    const FORMAT_VALUE = '{value()}';
    /**
     *
     */
    const FORMAT_UNITS = '{units()}';


    /**
     * @return array
     */
    public function structure()
    {
        return [
            [['numerator', 'denominator'], TQuantity::class],
        ];
    }

    /**
     * @return mixed|string
     */
    public function getValue()
    {
        $result = [];
        if ($this->numerator && $value = $this->numerator->format()){
            $result[] = $value;
        }
        if ($this->denominator && $value = $this->denominator->format()){
            $result[] = $value;
        }
        return implode('/', $result);
    }

    /**
     * @return string|float
     */
    public function format_value(){
        $num = $this->numerator && $this->numerator->value ? $this->numerator->value->getValue() : 1;
        $den = $this->denominator && $this->denominator->value ? $this->denominator->value->getValue() : 1;
        try {
            if (is_numeric($num) && is_numeric($den) && $den <> 0){
                return $num/$den;
            } else {
                return $this->getValue();
            }
        } catch (\Throwable $e){ }
        return $this->getValue();
    }

    /**
     * @return string|null
     */
    public function format_units()
    {
        $numUnit = $denUnit = null;
        if (isset($this->numerator)) {
            $numUnit = (isset($this->numerator->unit) ? $this->numerator->unit->getValue() : $numUnit);
        }
        if (isset($this->denominator)) {
            $denUnit = (isset($this->denominator->unit) ? $this->denominator->unit->getValue() : $denUnit);
        }

        if ($numUnit && $denUnit) {
            $per = ((strpos($numUnit, '/') === false && strpos($denUnit, '/') === false) ? '/' : ' per ');
            $unit = $numUnit.$per.$denUnit;
        } elseif ($numUnit) {
            $unit = $numUnit;
        } elseif ($denUnit) {
            $unit = '1/'.$denUnit;
        } else {
            $unit = null;
        }
        
        return $unit;
    }

}
