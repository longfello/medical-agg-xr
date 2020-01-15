<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 12.03.18
 * Time: 15:09
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\AllergyIntolerance;


use common\components\PerfectParser\Common\Helper;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCoding;

/**
 * Class TSubstance
 * @package common\components\PerfectParser
 *
 * @property string|false $valueFromRxCode
 */
class TSubstance extends TCodeableConcept
{
    /**
     * @inheritdoc
     * implement task SLID-1050
     */
    public function getValue()
    {
        $value = parent::getValue();
        return $value;
    }

    /**
     * @return false|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function getValueFromRxCode(){
        if ($this->coding){
            foreach ($this->coding as $coding){
                /** @var $coding TCoding */
                if ($coding->system && $coding->system->getValue() === TCoding::SYSTEM_RXNORM){
                    if ($coding->code && is_numeric($coding->code->getValue())){
                        $this->log("Getting alergy name by RxCode: ".$coding->code->getValue());
                        return Helper::getNameByRxCode($coding->code->getValue());
                    }
                }
            }
        }
        return '';
    }
}