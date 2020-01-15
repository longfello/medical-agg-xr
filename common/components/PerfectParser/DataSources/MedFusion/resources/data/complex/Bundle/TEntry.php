<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 12.03.18
 * Time: 17:10
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Bundle;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUri;
use common\components\PerfectParser\DataSources\MedFusion\resources\RResource;

/**
 * Class TEntry
 * @package common\components\PerfectParser
 */
class TEntry extends TComplex
{
    /** @var TLink[] Links related to this entry */
    public $link;

    /** @var TUri Absolute URL for resource (server address, or UUID/OID) */
    public $fullUrl;

    /** @var RResource A resource in the bundle */
    public $resource;

    /** @var TSearch Search related information */
    public $search;

    /** @var TRequest Transaction Related Information */
    public $request;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['link', [TLink::class]],
            ['fullUrl', TUri::class],
            ['resource', RResource::class],
            ['search', TSearch::class],
            ['request', TRequest::class],
        ];
    }
}