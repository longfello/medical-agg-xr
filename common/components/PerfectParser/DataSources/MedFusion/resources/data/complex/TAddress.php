<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:51
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class TAddress
 * @package common\components\PerfectParser
 */
class TAddress extends TComplex
{
    /**
     * @var TCode (required)
     */
    public $use;

    /**
     * @var TCode (required)
     */
    public $type;

    /**
     * @var TString
     */
    public $text;

    /**
     * @var TString
     */
    public $line;

    /**
     * @var TString
     */
    public $city;

    /**
     * @var TString
     */
    public $district;

    /**
     * @var TString
     */
    public $state;

    /**
     * @var TString
     */
    public $postalCode;

    /**
     * @var TString
     */
    public $country;

    /**
     * @var TPeriod
     */
    public $period;

    /**
     * @inheritdoc
     */
    public function structure()
    {
        return [
            [['use', 'type'], TCode::class, self::REQUIRED],
            [['text', 'city', 'district', 'state', 'postalCode', 'country'], TString::class],
            ['line', [TString::class]],
            ['period', TPeriod::class]
        ];
    }
}