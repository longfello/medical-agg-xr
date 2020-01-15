<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 27.02.18
 * Time: 16:43
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TInteger;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUri;

/**
 * Class TExtension
 * @package common\components\PerfectParser
 */
class TExtension extends TComplex
{
    /** @var TUri */
    public $url;

    /** @var TQuantity */
    public $valueQuantity;

    /** @var TDateTime */
    public $valueDateTime;

    /** @var TReference */
    public $valueReference;

    /** @var TCodeableConcept */
    public $valueCodeableConcept;

    /** @var TInteger */
    public $valueInteger;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['url', TUri::class],
            ['valueQuantity', TQuantity::class],
            ['valueDateTime', TDateTime::class],
            ['valueReference', TReference::class],
            ['valueCodeableConcept', TCodeableConcept::class],
            ['valueInteger', TInteger::class],
        ];
    }
}