<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 17:46
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Procedure;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;

/**
 * Class TPerformer
 * @package common\components\PerfectParser
 */
class TPerformer extends TComplex
{
    /** @var TReference The reference to the practitioner */
    public $actor;

    /** @var TCodeableConcept The role the actor was in */
    public $role;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['actor', TReference::class],
            ['role', TCodeableConcept::class],
        ];
    }

}