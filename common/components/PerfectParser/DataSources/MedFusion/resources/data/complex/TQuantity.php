<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:51
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDecimal;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUri;

/**
 * Class TQuantity
 * @package common\components\PerfectParser
 */
class TQuantity extends TComplex
{
    /**
     * @inheritdoc
     */
    public $defaultFormat = "{medication()}";

    /** @var TDecimal Numerical value (with implicit precision) */
    public $value;

    /** @var TCode < | <= | >= | > - how to understand the value */
    public $comparator;

    /** @var TString Unit representation */
    public $unit;

    /** @var TUri System that defines coded unit form */
    public $system;

    /** @var TCode Coded form of the unit */
    public $code;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['value', TDecimal::class],
            ['comparator', TCode::class],
            ['unit', TString::class],
            ['system', TUri::class],
            ['code', TCode::class],
        ];
    }

    /**
     * @param null $defaultUnit
     *
     * @return float|mixed|string
     */
    public function format_medication($defaultUnit = null){
        $value = '';
        if ($this->value) {
            $value = $this->value->getValue();
            if ($this->unit && $unit = $this->unit->getValue(true)) {
                if (!in_array($unit, [ '1' ])) {
                    return $value." ".$unit;
                }
            }
            if (!is_null($defaultUnit)) {
                return $value." ".$defaultUnit;
            }
        }
        return $value;
    }
}