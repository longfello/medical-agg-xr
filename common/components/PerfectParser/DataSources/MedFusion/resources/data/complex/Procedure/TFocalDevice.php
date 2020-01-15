<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 17:54
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Procedure;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;

/**
 * Class TFocalDevice
 * @package common\components\PerfectParser
 */
class TFocalDevice extends TComplex
{
    /** @var TCodeableConcept Kind of change to device */
    public $action;

    /** @var TReference Device that was changed */
    public $manipulated;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['action', TCodeableConcept::class],
            ['manipulated', TReference::class],
        ];
    }

}