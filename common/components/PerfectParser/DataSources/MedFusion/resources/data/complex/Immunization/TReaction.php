<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 17:25
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Immunization;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;

/**
 * Class TReaction
 * @package common\components\PerfectParser
 */
class TReaction extends TComplex
{
    /** @var TDateTime When reaction started */
    public $date;

    /** @var TReference  Additional information on reaction */
    public $detail;

    /** @var TBoolean  	Indicates self-reported reaction */
    public $reported;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['date', TDateTime::class],
            ['detail', TReference::class],
            ['reported', TBoolean::class],
        ];
    }

}