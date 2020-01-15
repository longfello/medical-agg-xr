<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 15:21
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDecimal;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TInteger;

/**
 * Class TRepeat
 * @package common\components\PerfectParser
 */
class TRepeat extends TComplex
{
    /**
     * @inheritdoc
     * @var string
     */
    public $defaultFormat = 'repeat()';

    /** @var TDuration Length/Range of lengths, or (Start and/or end) limits */
    public $boundsQuantity;

    /** @var TRange Length/Range of lengths, or (Start and/or end) limits*/
    public $boundsRange;

    /** @var TPeriod Length/Range of lengths, or (Start and/or end) limits */
    public $boundsPeriod;

    /** @var TInteger Number of times to repeat */
    public $count;

    /** @var TDecimal How long when it happens. duration SHALL be a non-negative value */
    public $duration;

    /** @var TDecimal How long when it happens (Max) */
    public $durationMax;

    /** @var TCode s | min | h | d | wk | mo | a - unit of time (UCUM) */
    public $durationUnits;

    /** @var TInteger Event occurs frequency times per period */
    public $frequency;

    /** @var TInteger  Event occurs up to frequencyMax times per period */
    public $frequencyMax;

    /** @var TDecimal Event occurs frequency times per period  period SHALL be a non-negative value */
    public $period;

    /** @var TDecimal Upper limit of period (3-4 hours) */
    public $periodMax;

    /** @var TCode s | min | h | d | wk | mo | a - unit of time (UCUM) */
    public $periodUnits;

    /** @var TCode Regular life events the event is tied to */
    public $when;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['boundsQuantity', TDuration::class],
            ['boundsRange', TRange::class],
            ['boundsPeriod', TRange::class],
            [['count', 'frequency', 'frequencyMax'], TInteger::class],
            [['duration','durationMax', 'period', 'periodMax'], TDecimal::class],
            [['durationUnits', 'periodUnits', 'when'], TCode::class],
        ];
    }

    /**
     * @return null|string
     */
    public function format_repeat(){
        if ($this->period){
            $period = $this->period->getValue();
            $period = ($period == 1)?"": $period ;
            if ($this->frequency){
                switch ($this->frequency->getValue()){
                    case 1:
                        $freq = 'once';
                        break;
                    case 2:
                        $freq = 'twice';
                        break;
                    default:
                        $freq = $this->frequency->getValue().' times';
                }
                $chains = [];
                $chains[] = $freq;
                $chains[] = 'per';
                if ($period ) $chains[] = $period;
                $chains[] = $this->periodUnits->getValue();

                return implode(' ', $chains );
            } else {
                $chains = [];
                $chains[] = 'every';
                if ($period ) $chains[] = $period;
                $chains[] = $this->periodUnits->getValue();

                return implode(' ', $chains );
            }
        }
        return null;
    }
}