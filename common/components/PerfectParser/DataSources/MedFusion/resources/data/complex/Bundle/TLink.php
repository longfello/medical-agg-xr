<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 12.03.18
 * Time: 17:06
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Bundle;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUri;

/**
 * Class TLink
 * @package common\components\PerfectParser
 */
class TLink extends TComplex
{
    /** @var TString http://www.iana.org/assignments/link-relations/link-relations.xhtml */
    public $relation;

    /** @var TUri Reference details for the link */
    public $url;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['relation', TString::class],
            ['url', TUri::class],
        ];
    }
}