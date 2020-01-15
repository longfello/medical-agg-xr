<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 13.03.18
 * Time: 12:08
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\RMedicationOrder;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TDuration;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TSimpleQuantity;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TPositiveInt;

/**
 * Class TDispenseRequest
 * @package common\components\PerfectParser
 */
class TDispenseRequest extends TComplex
{
    /** @var TCodeableConcept|null Product to be supplied */
    public $medicationCodeableConcept;

    /** @var TReference|null Product to be supplied */
    public $medicationReference;

    /** @var TPeriod|null Time period supply is authorized for */
    public $validityPeriod;

    /** @var TPositiveInt|null Number of refills authorized */
    public $numberOfRepeatsAllowed;

    /** @var TSimpleQuantity|null Amount of medication to supply per dispense */
    public $quantity;

    /** @var TDuration|null Number of days supply per dispense */
    public $expectedSupplyDuration;

    /**
     * @inheritdoc
     */
    public function structure(){
        return [
            ['medicationCodeableConcept', TCodeableConcept::class],
            ['medicationReference', TReference::class],
            ['validityPeriod', TPeriod::class],
            ['numberOfRepeatsAllowed', TPositiveInt::class],
            ['quantity', TSimpleQuantity::class],
            ['expectedSupplyDuration', TDuration::class],
        ];
    }
}