<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:51
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class TAnnotation
 * @package common\components\PerfectParser
 */
class TAnnotation extends TComplex
{
    /**
     * @var TReference
     */
    public $author;

    /**
     * @var TDateTime
     */
    public $time;

    /**
     * @var TString
     */
    public $text;

    /**
     * @inheritdoc
     */
    public function structure()
    {
        return [
            [['author'], TReference::class],
            [['time'], TDateTime::class],
            [['text'], TString::class]
        ];
    }

}