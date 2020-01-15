<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 12.03.18
 * Time: 17:21
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Bundle;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDecimal;

/**
 * Class TSearch
 * @package common\components\PerfectParser
 */
class TSearch extends TComplex
{
    /** @var TCode match | include | outcome - why this is in the result set */
    public $mode;

    /** @var TDecimal  */
    public $score;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['mode', TCode::class],
            ['score', TDecimal::class],
        ];
    }
}