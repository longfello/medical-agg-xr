<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 07.02.18
 * Time: 13:10
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class TReference
 * @package common\components\PerfectParser
 */
class TReference extends TComplex
{
    /** @var TString Literal reference, Relative, internal or absolute URL */
    public $reference;

    /** @var TIdentifier Logical reference, when literal reference is not known */
    public $identifier;

    /** @var TString Text alternative for the resource */
    public $display;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            [['reference', 'display'], TString::class],
            ['identifier', TIdentifier::class],
        ];
    }

}