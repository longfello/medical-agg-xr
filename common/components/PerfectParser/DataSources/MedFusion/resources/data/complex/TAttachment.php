<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:51
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBase64Binary;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUnsignedInt;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUri;

/**
 * Class TAttachment
 * @package common\components\PerfectParser
 */
class TAttachment extends TComplex
{
    /** @var TCode Mime type of the content, with charset etc. */
    public $contentType;

    /** @var TCode Human language of the content (BCP-47) */
    public $language;

    /** @var TBase64Binary Data inline, base64ed */
    public $data;

    /** @var TUri Uri where the data can be found */
    public $url;

    /** @var TUnsignedInt Number of bytes of content (if url provided) */
    public $size;

    /** @var TString Hash of the data (sha-1, base64ed) */
    public $hash;

    /** @var TString Label to display in place of the data */
    public $title;

    /** @var TDateTime Date attachment was first created */
    public $creation;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            [['contentType', 'language'], TCode::class],
            ['data', TBase64Binary::class],
            ['url', TUri::class],
            ['size', TUnsignedInt::class],
            ['hash', TString::class],
            ['title', TString::class],
            ['creation', TDateTime::class],
        ];
    }

}