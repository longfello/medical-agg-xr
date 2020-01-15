<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 16:37
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Medication;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;

/**
 * Class TPackage
 * @package common\components\PerfectParser
 */
class TPackage extends TComplex
{
    /** @var TCodeableConcept E.g. box, vial, blister-pack */
    public $container;

    /** @var TContent What is in the package */
    public $content;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['container', TCodeableConcept::class],
            ['content', TContent::class],
        ];
    }

}