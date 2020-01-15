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
 * Class TTiming
 * @package common\components\PerfectParser
 */
class TTiming extends TComplex
{
    /**
     * @var string
     */
    public $defaultFormat = 'timing()';

    /** @var TDateTime When the event occurs */
    public $event;

    /** @var TRepeat When the event is to occur Either frequency or when can exist, not both  if there's a duration, there needs to be duration units if there's a period, there needs to be period units If there's a periodMax, there must be a period If there's a durationMax, there must be a duration */
    public $repeat;

    /** @var TCodeableConcept QD | QOD | Q4H | Q6H | BID | TID | QID | AM | PM + http://hl7.org/fhir/DSTU2/valueset-timing-abbreviation.html */
    public $code;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['event', TDateTime::class],
            ['repeat', TRepeat::class],
            ['code', TCodeableConcept::class],
        ];
    }

    /**
     * @return bool|null|string
     */
    public function format_timing()
    {
        if ($this->code && $value = $this->code->getValue()) return $value;
        if ($this->repeat && $value = $this->repeat->format()) return $value;
        return null;
    }
}