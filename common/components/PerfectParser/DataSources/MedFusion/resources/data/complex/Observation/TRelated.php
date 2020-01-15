<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 26.02.18
 * Time: 15:35
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Observation;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;

/**
 * Class TRelated
 * @package common\components\PerfectParser
 */
class TRelated extends TComplex
{
    /** @var TCode has-member | derived-from | sequel-to | replaces | qualified-by | interfered-by */
    public $type;

    /** @var TReference Resource that is related to this one */
    public $target;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['type', TCode::class],
            ['target', TReference::class],
        ];
    }
}