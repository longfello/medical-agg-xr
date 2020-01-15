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
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUri;

/**
 * Class TIdentifier
 * @package common\components\PerfectParser
 */
class TIdentifier extends TComplex
{
    /** @var null|TCode usual | official | temp | secondary (If known) */
    public $use;
    /** @var null|TCodeableConcept Description of identifier */
    public $type;
    /** @var null|TUri The namespace for the identifier */
    public $system;
    /** @var null|TString The value that is unique */
    public $value;
    /** @var null|TPeriod Time period when id is/was valid for use */
    public $period;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['use', TCode::class],
            ['type', TCodeableConcept::class],
            ['system', TUri::class],
            ['value', TString::class],
            ['period', TPeriod::class],
        ];
    }
}