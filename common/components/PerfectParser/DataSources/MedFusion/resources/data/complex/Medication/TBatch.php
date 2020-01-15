<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 16:35
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Medication;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class TBatch
 * @package common\components\PerfectParser
 */
class TBatch extends TComplex
{
    /** @var TString  */
    public $lotNumber;

    /** @var TDateTime */
    public $expirationDate;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['lotNumber', TString::class],
            ['expirationDate', TDateTime::class],
        ];
    }

}