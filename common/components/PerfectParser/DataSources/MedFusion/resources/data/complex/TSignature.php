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
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TInstant;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUri;

/**
 * Class TSignature
 * @package common\components\PerfectParser
 */
class TSignature extends TComplex
{
    /** @var TCoding Indication of the reason the entity signed the object(s) */
    public $type;

    /** @var TInstant When the signature was created */
    public $when;

    /** @var TUri Who signed the signature */
    public $whoUri;

    /** @var TReference Who signed the signature */
    public $whoReference;

    /** @var TCode The technical format of the signature */
    public $contentType;

    /** @var TBase64Binary The actual signature content (XML DigSig. JWT, picture, etc.) */
    public $blob;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['type', [TCoding::class], self::REQUIRED],
            ['when', TInstant::class, self::REQUIRED],
            ['whoUri', TUri::class],
            ['whoReference', TReference::class],
            ['contentType', TCode::class, self::REQUIRED],
            ['blob', TBase64Binary::class, self::REQUIRED],
        ];
    }

}