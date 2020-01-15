<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:51
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUri;

/**
 * Class TCoding
 * @package common\components\PerfectParser
 */
class TCoding extends TComplex
{
    /**
     *
     */
    const SYSTEM_RXNORM = 'http://www.nlm.nih.gov/research/umls/rxnorm';

    /**
     * @var TUri
     */
    public $system;

    /**
     * @var TString
     */
    public $version;

    /**
     * @var TCode
     */
    public $code;

    /**
     * @var TString
     */
    public $display;

    /**
     * @var TBoolean
     */
    public $userSelected;

    /**
     * @inheritdoc
     */
    public function structure()
    {
        return [
            [['system'], TUri::class],
            [['version', 'display'], TString::class],
            [['code'], TCode::class],
            [['userSelected'], TBoolean::class],
        ];
    }
}