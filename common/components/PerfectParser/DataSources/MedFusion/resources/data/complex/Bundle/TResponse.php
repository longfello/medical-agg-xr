<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 12.03.18
 * Time: 17:27
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Bundle;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TInstant;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUri;

/**
 * Class TResponse
 * @package common\components\PerfectParser
 */
class TResponse extends TComplex
{
    /** @var TString Status return code for entry */
    public $status;

    /** @var TUri The location, if the operation returns a location */
    public $location;

    /** @var TString The etag for the resource (if relevant) */
    public $etag;

    /** @var TInstant Server's date time modified */
    public $lastModified;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['status', TString::class, self::REQUIRED],
            ['location', TUri::class],
            ['etag', TString::class],
            ['lastModified', TInstant::class],
        ];
    }
}