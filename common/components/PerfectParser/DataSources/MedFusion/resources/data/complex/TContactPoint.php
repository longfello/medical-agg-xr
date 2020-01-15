<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:51
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TPositiveInt;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class TContactPoint
 * @package common\components\PerfectParser
 */
class TContactPoint extends TComplex
{
    /**
     *
     */
    const PHONE         = 'phone';
    /**
     *
     */
    const PHONE_ANY     = false;
    /**
     *
     */
    const PHONE_MOBILE  = 'mobile';
    /**
     *
     */
    const PHONE_HOME    = 'home';
    /**
     *
     */
    const PHONE_WORK    = 'work';
    /**
     *
     */
    const EMAIL         = 'email';

    /** @var TCode phone | fax | email | pager | other */
    public $system;
    /** @var TString The actual contact point details */
    public $value;
    /** @var TCode home | work | temp | old | mobile - purpose of this contact point */
    public $use;
    /** @var TPositiveInt Specify preferred order of use (1 = highest) */
    public $rank;
    /** @var TPeriod Time period when the contact point was/is in use */
    public $period;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['system', TCode::class],
            ['value', TString::class],
            ['use', TCode::class],
            ['rank', TPositiveInt::class],
            ['period', TPeriod::class],
        ];
    }

    /**
     * @return bool
     */
    public function usable(){
        if ($this->system){
            switch ($this->system->getValue()){
                case self::EMAIL:
                    return true;
                case self::PHONE:
                    return true;
            }
        }
        return false;
    }
}