<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 26.02.18
 * Time: 15:30
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Observation;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TRange;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TSimpleQuantity;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class TReferenceRange
 * @package common\components\PerfectParser
 */
class TReferenceRange extends TComplex
{
    /** @var TSimpleQuantity Low Range, if relevant */
    public $low;
    /** @var TSimpleQuantity High Range, if relevant */
    public $high;
    /** @var TCodeableConcept Indicates the meaning/use of this range of this range */
    public $meaning;
    /** @var TRange Applicable age range, if relevant */
    public $age;
    /** @var TString Text based reference range in an observation */
    public $text;

    /**
     *
     */
    const FORMAT_RANGE = '{range()}';

    /**
     * @return array
     */
    public function structure()
    {
        return [
            [['low', 'high'], TSimpleQuantity::class],
            [['meaning'], TCodeableConcept::class],
            [['age'], TRange::class],
            [['text'], TString::class],
        ];
    }

    /**
     * @return string|null
     */
    public function format_range()
    {
        $result = '';

        if (isset($this->text) && $value = $this->text->getValue()) {
            $result = $value;
        } elseif (isset($this->low) || isset($this->high)) {
            $lowValue = $highValue = null;
            $lowUnit = $highUnit = null;
            $meaning = null;

            if (isset($this->low)) {
                $lowValue = (isset($this->low->value) ? (string) $this->low->value->getValue() : $lowValue);
                $lowUnit = (isset($this->low->unit) ? $this->low->unit->getValue() : $lowUnit);
            }
            if (isset($this->high)) {
                $highValue = (isset($this->high->value) ? (string) $this->high->value->getValue() : $highValue);
                $highUnit = (isset($this->high->unit) ? $this->high->unit->getValue() : $highUnit);
            }
            if (isset($this->meaning) && isset($this->meaning->text)) {
                $meaning = $this->meaning->text->getValue();
            }

            $result .= ($lowValue ? $lowValue : '?').($lowUnit ? ' '.$lowUnit : '');
            $result .= ' - ';
            $result .= ($highValue ? $highValue : '?').($highUnit ? ' '.$highUnit : '');
            $result .= ($meaning ? ' ('.$meaning.')' : '');
        }

        return (empty($result) ? null : $result);
    }

}
