<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 08.02.18
 * Time: 10:28
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TInteger;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;

/**
 * Class TInfoBlock
 * @package common\components\PerfectParser
 */
class TInfoBlock extends TComplex
{
    /** @var TString[]|TArray|null */
    public $id;

    /** @var TString */
    public $type;

    /** @var TDateTime */
    public $createTime;

    /** @var TDateTime */
    public $modifiedTime;

    /** @var TBoolean */
    public $isArchived;

    /** @var TInteger */
    public $profileId;

    /** @var TInteger[] */
    public $sourcePortalIds;

    /** @var TString[] */
    public $sourceDocumentIds;

    /** @var TArray */
    public $data;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['id', [TString::class]],
            ['type', TString::class],
            [['createTime', 'modifiedTime'], TDateTime::class],
            ['isArchived', TBoolean::class],
            ['profileId', TInteger::class],
            ['sourcePortalIds', [TInteger::class]],
            ['sourceDocumentIds', [TString::class]],
            ['data', TArray::class]
        ];
    }

}
