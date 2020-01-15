<?php
namespace common\components\PerfectParser\DataSources\CCDA\resources\type;

use yii\base\BaseObject;

/**
 * Class TElement
 * @package common\components\PerfectParser\DataSources\CCDA\resources\type
 */
class TElement extends BaseObject
{
    /** @var mixed */
    protected $_value;


    /**
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }
    
    /**
     * 
     * @param mixed $value
     * @return void
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }
    
    /**
     * 
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getValue();
    }

}
