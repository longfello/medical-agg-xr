<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 27.02.18
 * Time: 16:23
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Patient;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;

/**
 * Class TCommunication
 * @package common\components\PerfectParser
 */
class TCommunication extends TComplex
{
    /** @var TCodeableConcept The language which can be used to communicate with the patient about his or her health */
    public $language;

    /** @var TBoolean Language preference indicator */
    public $preferred;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['language', TCodeableConcept::class],
            ['preferred', TBoolean::class],
        ];
    }
}