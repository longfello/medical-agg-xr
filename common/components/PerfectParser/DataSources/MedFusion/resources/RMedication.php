<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:01
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Medication\TPackage;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Medication\TProduct;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class RMedication
 * @package common\components\PerfectParser
 *
 * @property null|string $strange
 * @property null|string $doseUnit
 */
class RMedication extends RResource
{
    /** @var TString */
    public $id;

    /** @var TString|null  */
    public $resourceType;

    /** @var TCodeableConcept  Codes that identify this medication */
    public $code;

    /** @var TBoolean True if a brand */
    public $isBrand;

    /** @var TReference Manufacturer of the item */
    public $manufacturer;

    /** @var TProduct Administrable medication details */
    public $product;

    /** @var TPackage Details about packaged medications */
    public $package;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['id', [TString::class]],
            ['resourceType', TString::class],
            ['code', TCodeableConcept::class],
            ['isBrand', TBoolean::class],
            ['manufacturer', TReference::class],
            ['product', TProduct::class],
            ['package', TPackage::class],
        ];
    }

    /**
     * @return null|string
     */
    public function getStrange(){
        if ($this->product){
            if ($this->product->ingredient){
                $ingridient = $this->product->ingredient->first();
                if ($ingridient->amount){
                    return $ingridient->amount->getValue();
                }
            }
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getDoseUnit(){
        if ($this->product){
            if ($this->product->form){
                return $this->product->form->getValue();
            }
        }
        return null;
    }
}