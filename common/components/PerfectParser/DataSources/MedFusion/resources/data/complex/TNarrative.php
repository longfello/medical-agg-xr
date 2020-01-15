<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 26.02.18
 * Time: 17:56
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class TNarrative
 * @package common\components\PerfectParser
 */
class TNarrative extends TComplex
{
    /** @var TCode generated | extensions | additional | empty */
    public $status;

    /** @var TString Limited xhtml content */
    public $div;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['status', TCode::class],
            ['div', TString::class],
        ];
    }
}