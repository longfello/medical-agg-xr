<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 13:24
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;

/**
 * Class TArray
 * @package common\components\PerfectParser
 */
class TArray extends TElement implements \Iterator, \ArrayAccess, \Countable
{
    /**
     * @inheritdoc
     * @param $data
     * @param bool $silent
     *
     * @return bool|mixed
     */
    public function load($data, $silent = false){
        return parent::load($data, true);
    }

    /**
     * @inheritdoc
     * @return bool
     */
    protected function _validate() {
        return is_array($this->originalValue);
    }

    /**
     * @param int|string $key
     * @param mixed|null $default
     *
     * @return TArray|mixed|null
     */
    public function getElement($key, $default = null){

        if (isset($this->originalValue[$key])){
            if (is_array($this->originalValue[$key])) {
               return new TArray($this->originalValue[$key], $this->elName.'->'.$key);
            } else return $this->originalValue[$key];
        } else return $default;
    }

    /**
     * return a nested array value
     *
     * @param array $path  the path to the value
     * @param mixed $defaultValue (optional) default value if path not found
     *
     * @return mixed|TArray array element by path
     */
    public function getElementByPath($path = array(), $defaultValue = null)
    {
        try{
            $ref = &$this->originalValue;
            foreach ($path as $key) {
                if (!is_array($ref)) {
                    $ref = array();
                }
                $ref = &$ref[$key];
            }
            if (is_array($ref)){
                return new TArray($ref,$this->elName.'->'.implode('->', $path));
            } else return $ref;
        } catch(\Exception $e){
            return $defaultValue;
        }
    }

    /**
     * Implode arrays emelents field, if exists
     * @param string $field Field Name
     * @param string $glue Glue
     *
     * @return string
     */
    public function implodeField($field, $glue = ' '){
        $list = [];
        /** @var $this TComplex[] */
        foreach ($this as $element){
            if ($element && isset($element->$field)){
                $value = $element->$field;
                if ($value instanceof TArray){
                    foreach ($value as $subvalue){
                        $list[] = $subvalue->getValue();
                    }
                } else {
                    $list[] = $value->getValue();
                }
            }
        }
        return implode($glue, $list);
    }

    /**
     * Return array elements which fields eq value
     * @param $field
     * @param $value
     *
     * @return TArray|TComplex[]
     */
    public function filterByField($field, $value){
        $list = [];
        foreach ($this as $element){
            if ($element && isset($element->$field)){
                $elementField = $element->$field;
                /** @var TElement $elementField */
                if ($elementField->getValue() == $value) {
                    $list[] = $element;
                }
            }
        }
        return new TArray($list, $this->elName);
    }

    /**
     * Return elements with minimal value (integer) of given field
     *
     * @param string $field
     *
     * @param bool $strict
     *
     * @return TArray|TComplex[]
     */
    public function filterByLowerField($field, $strict = true){
        $elements = new TArray([]);

        $min = $this->getMinFieldValue($field);

        if (!is_null($min)){
            $elements = $this->filterByField($field, $min);
        }

        return ($strict || count($elements) > 0 ? $elements : $this);
    }

    /**
     * Return minimal integer value of array elements field values
     * @param string $field
     *
     * @return integer|null
     */
    public function getMinFieldValue($field){
        $min = null;
        foreach ($this as $element){
            /** @var $element TComplex */
            if ($element && isset($element->$field)){
                $item = $element->$field;
                /** @var $item TElement */
                if ($value = $item->getValue()){
                    if (is_int($value)){
                        if (is_null($min) || ($min > $value)){
                            $min = $value;
                        }
                    }
                }
            }
        }
        return $min;
    }

    /**
     * Return first element
     * @return mixed
     */
    public function first(){
        return reset($this->originalValue);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        reset($this->originalValue);
    }

    /**
     * @inheritdoc
     * @return TArray|mixed
     */
    public function current()
    {
        $value = current($this->originalValue);
        return is_array($value)?new TArray($value, $this->elName.'->'.$this->key()):$value;
    }

    /**
     * @inheritdoc
     * @return int|mixed|null|string
     */
    public function key()
    {
        return key($this->originalValue);
    }

    /**
     * @inheritdoc
     * @return mixed
     */
    public function next()
    {
        return next($this->originalValue);
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function valid()
    {
        $key = key($this->originalValue);
        return ($key !== NULL && $key !== FALSE);
    }

    /**
     * @inheritdoc
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->originalValue[$offset]);
    }

    /**
     * @inheritdoc
     * @param mixed $offset
     *
     * @return TArray|mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->getElement($offset);
    }

    /**
     * @inheritdoc
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->originalValue[] = $value;
        } else {
            $this->originalValue[$offset] = $value;
        }
    }

    /**
     * @inheritdoc
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->originalValue[$offset]);
    }

    /**
     * @return int
     */
    public function count(){
        return count($this->originalValue);
    }
}