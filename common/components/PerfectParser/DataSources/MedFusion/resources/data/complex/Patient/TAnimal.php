<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 27.02.18
 * Time: 16:15
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Patient;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;

/**
 * Class TAnimal
 * @package common\components\PerfectParser
 */
class TAnimal extends TComplex
{
    /** @var TCodeableConcept E.g. Dog, Cow */
    public $species;

    /** @var TCodeableConcept E.g. Poodle, Angus */
    public $breed;

    /** @var TCodeableConcept E.g. Neutered, Intact */
    public $genderStatus;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            [['species', 'breed', 'genderStatus'], TCodeableConcept::class]
        ];
    }
}