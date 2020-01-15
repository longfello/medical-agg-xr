<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:51
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;

/**
 * Class TPeriod
 * @package common\components\PerfectParser
 * @property $timestamp int
 */
class TPeriod extends TComplex
{
    /**
     * @var TDateTime
     */
    public $start;

    /**
     * @var TDateTime
     */
    public $end;

    /**
     * @var string
     */
    public $format = 'php:M d, Y';
    
    /**
     * @inheritdoc
     */
    public function structure()
    {
        return [
            [['start', 'end'], TDateTime::class],
        ];
    }

    /**
     * @return null|string
     */
    public function getTimestamp(){
        if ($this->end) return $this->end->getTimestamp();
        if ($this->start) return $this->start->getTimestamp();
        return null;
    }

    /**
     *
     * @param string | null $format
     * @param string $delimiter
     *
     * @return string | null
     */
    public function asDateTimePeriod($format = null, $delimiter = 'to')
    {
        if (is_null($format)) {
            $format = $this->format;
        }

        $start = (isset($this->start) ? $this->start->asDatetime($format) : null);
        $end = (isset($this->end) ? $this->end->asDatetime($format) : null);

        if ($start && $end) {
            return ($start == $end) ? $start : $start.' '.$delimiter.' '.$end;
        } elseif ($start || $end) {
            return ($start ? $start : $end);
        } else {
            return null;
        }
    }

}
