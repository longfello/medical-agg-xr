<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 27.02.18
 * Time: 15:40
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Practitioner;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod;

/**
 * Class TQualification
 * @package common\components\PerfectParser
 */
class TQualification extends TComplex
{
    /** @var TIdentifier An identifier for this qualification for the practitioner */
    public $identifier;

    /** @var TCodeableConcept Coded representation of the qualification */
    public $code;

    /** @var TPeriod Period during which the qualification is valid */
    public $period;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['identifier', TIdentifier::class],
            ['code', TCodeableConcept::class],
            ['period', TPeriod::class],
        ];
    }
}