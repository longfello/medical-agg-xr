<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 13:32
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Class TBoolean
 * @package common\components\PerfectParser
 */
class TBoolean extends TElement
{

    /**
     * @return bool
     */
    protected function _validate(){
        return true;
    }

    /**
     * @return bool
     */
    public function getValue()
    {
        try{
            return (bool)$this->getOriginalValue();
        } catch (\Exception $e){
            $this->log("%YError converting to boolean representation: ". $e->getMessage().'%n');
        }
        return null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getValue()?"true":"false";
    }
}