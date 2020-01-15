<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:51
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;

/**
 * Class TRange
 * @package common\components\PerfectParser
 *
 * @property null|string $units
 */
class TRange extends TComplex
{
    /** @var TSimpleQuantity Low limit */
    public $low;

    /** @var TSimpleQuantity High limit */
    public $high;

    /**
     *
     */
    const FORMAT_FULL_RANGE = '{fullRange()}';

    /**
     * @return array
     */
    public function structure()
    {
        return [
            [['low', 'high'], TSimpleQuantity::class],
        ];
    }

    /**
     * @return string
     */
    public function format_asText(){
        $text = null;
        if ($this->high && $this->low){
            $unit_high = $this->high->unit ? $this->high->unit->getValue(true) : null;
            $unit_low  = $this->low->unit  ? $this->low->unit->getValue(true)  : null;

            if ($unit_high && $unit_low && ($unit_high == $unit_low)){
                $text = $this->low->value->getValue()." - ".$this->high->value->getValue()." ".$unit_low;
            } else {
                $values = [];
                if ($this->low->value->getValue()) $values[] = $this->low->value->getValue();
                if ($unit_low) $values[] = $unit_low;
                $values[] = '-';
                if ($this->high->value->getValue()) $values[] = $this->high->value->getValue();
                if ($unit_high) $values[] = $unit_high;
                $text = implode(' ', $values);
            }
        } else {
            if ($this->high){
                $text = "up to ".$this->high->value->getValue()." ".$this->high->unit->getValue(true);
            } else {
                $text = "from ".$this->low->value->getValue()." ".$this->low->unit->getValue(true);
            }
        }
        return trim($text);
    }

    /**
     * @return string
     */
    public function format_fullRange()
    {
        $lowValue = $highValue = '?';
        if (isset($this->low)) {
            $lowValue = (string) $this->low->value->getValue();
        }

        if (isset($this->high)) {
            $highValue = (string) $this->high->value->getValue();
        }
        
        return $lowValue.' - '.$highValue;
    }

    /**
     * @return string|null
     */
    public function getUnits()
    {
        /*
        by spec, the two unit values MUST be the same. 
        If there is only 1, use that. 
        If both are present, pick either one because 
        the spec guarantees the other will be the same.
        */
        $unit = null;
        if (isset($this->low)) {
            $unit = ($this->low->unit ? $this->low->unit->getValue() : $unit);
        }

        if (isset($this->high)) {
            $unit = (!$unit && $this->high->unit ? $this->high->unit->getValue() : $unit);
        }
        return $unit;

    }

}
