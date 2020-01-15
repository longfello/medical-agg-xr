<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 13.03.18
 * Time: 12:13
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\MedicationDispense;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;

/**
 * Class TSubstitution
 * @package common\components\PerfectParser
 */
class TSubstitution extends TComplex
{
    /** @var TCodeableConcept Type of substitution  */
    public $type;

    /** @var TCodeableConcept[]|null Why was substitution made */
    public $reason;

    /** @var TReference[]|null Who is responsible for the substitution */
    public $responsibleParty;

    /**
     * @inheritdoc
     */
    public function structure()
    {
        return [
            ['type', TCodeableConcept::class, self::REQUIRED],
            ['reason', TCodeableConcept::class],
            ['responsibleParty', TReference::class],
        ];
    }

}
