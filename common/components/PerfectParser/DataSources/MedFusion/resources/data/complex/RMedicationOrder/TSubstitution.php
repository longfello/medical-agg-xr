<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 13.03.18
 * Time: 12:13
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\RMedicationOrder;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;

/**
 * Class TSubstitution
 * @package common\components\PerfectParser
 */
class TSubstitution extends TComplex
{
    /** @var TCodeableConcept generic | formulary + ActSubstanceAdminSubstitutionCode  */
    public $type;

    /** @var TCodeableConcept|null Why should (not) substitution be made SubstanceAdminSubstitutionReason */
    public $reason;

    /**
     * @inheritdoc
     */
    public function structure()
    {
        return [
            ['type', TCodeableConcept::class, self::REQUIRED],
            ['reason', TCodeableConcept::class]
        ];
    }
}