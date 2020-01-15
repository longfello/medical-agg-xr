<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 12.03.18
 * Time: 17:23
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Bundle;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TInstant;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUri;

/**
 * Class TRequest
 * @package common\components\PerfectParser
 */
class TRequest extends TComplex
{
    /** @var TCode 	GET | POST | PUT | DELETE */
    public $method;

    /** @var TUri URL for HTTP equivalent of this entry */
    public $url;

    /** @var TString For managing cache currency */
    public $ifNoneMatch;

    /** @var TInstant For managing update contention */
    public $ifModifiedSince;

    /** @var TString For managing update contention */
    public $ifMatch;

    /** @var TString 	For conditional creates */
    public $ifNoneExist;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['method', TCode::class, self::REQUIRED],
            ['url', TUri::class, self::REQUIRED],
            [['ifNoneMatch', 'ifMatch'. 'ifNoneExist'], TString::class],
            ['ifModifiedSince', TInstant::class],
        ];
    }
}