<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 13:24
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;


use common\components\PerfectParser\Common\Traits\DebugTrait;
use yii\base\BaseObject;
use yii\helpers\BaseStringHelper;
use yii\helpers\StringHelper;

/**
 * Class TElement
 * @package common\components\PerfectParser
 *
 * @property $elName string
 * @property $value string|mixed
 */
class TElement extends BaseObject implements TElementInterface
{
    use DebugTrait;

    /**
     *
     */
    const MODE_STRICT = 'strict';
    /**
     *
     */
    const MODE_NON_STRICT = 'non-strict';

    /**
     *
     */
    const FORMAT_JSON = '{json()}';

    /**
     * @var string
     */
    public $processingMode = self::MODE_NON_STRICT;

    /** @var string String representation of element name */
    private $_elName;

    /**
     * Default format for string representation
     * @var string
     */
    public $defaultFormat = '{text()}';

    /**
     * @var
     */
    protected $originalValue;

    /**
     * TElement constructor.
     *
     * @param null $data
     * @param null $elementName
     */
    public function __construct($data = null, $elementName = null)
    {
        parent::__construct();
        if (!is_null($data)){
            if ($data instanceof TArray){
                $data = $data->getValue();
            }
            $this->load($data);
        }

        if (!is_null($elementName)){
            $this->elName = $elementName;
        }
    }

    /**
     * @return string
     */
    public function getElName(){
        return trim($this->_elName, '->');
    }

    /**
     * @param $value string
     */
    public function setElName($value){
        $this->_elName = $value;
    }

    /**
     * @return mixed
     */
    public function getValue(){
        return $this->getOriginalValue();
    }

    /**
     * @param $data
     * @param bool $silent
     *
     * @return bool|mixed
     */
    public function load($data, $silent = false){
        if (!$silent){
            $this->log("Loading ". $this->elName."[".BaseStringHelper::basename(get_called_class())."]");
        }
        $this->incPrefix();
        if (!$silent){
            $this->log("Load Data:");
            $this->incPrefix();
            $this->log($this->prettyPrint($data));
            $this->decPrefix();
        }
        $this->setOriginalValue($data);
        $ok = $this->_validate();
        if (!$ok) {
            try{
                $this->log("%YError converting value ".json_encode($data).' to '. get_called_class().' in '.$this->elName.'%n');
            } catch(\Exception $e){
                $this->log('%Y'.$e->getMessage().'%n');
            }
        }
        if (!$silent){
            $this->log("Loaded : ".$this->getValue());
        }
        $this->decPrefix();
        return $ok;
    }

    /**
     * @param null $format
     *
     * @return bool|string
     */
    public function format($format = null)
    {
        $format = is_null($format)?$this->defaultFormat:$format;

        $result = '';
        $success = true;
        $itemsList = preg_split("/(\}.*?\{)/", trim($format, ' {}'), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        foreach ($itemsList as $item) {
            if (strpos($item, '}') === 0) { // item is delimiter
                $result .= trim($item, '{}');
            } else { // item is method with arguments
                preg_match("/^(\w+)\((['|\"].+['|\"])?\)$/", $item, $parseTag);

                $method = 'format_' . $parseTag[1];
                $argList = [];
                try{
                    $argList = preg_split("/['|\"],\s*['|\"]/", trim($parseTag[2], ' \'"'));
                }catch (\Exception $e){}

                if (method_exists($this, $method)) {
                    try {
                        $result .= call_user_func_array([$this, $method], $argList);
                        continue;
                    } catch (\Exception $e) {
                        $this->error('Error call method: '.get_called_class().'::'.$method.' with arguments: '.implode(', ', $argList));
                    }
                } else {
                    $this->error('Method not found: '.get_called_class().'::'.$method);
                }
                $success = false;
            }
        }

        return ($success ? $result : false);
    }

    /**
     * @param $value
     * @param string $default
     *
     * @return mixed|null|string
     */
    public function format_property($value, $default = '')
    {
        $return = null;
        if (property_exists($this, $value)){
            $prop = $this->$value;
            /** @var $prop TElement */
            if ($prop instanceof TElement){
                $return = $prop->getValue();
            }
        }

        return is_null($return)?$default:$return;
    }

    /**
     * @return string
     */
    public function format_text()
    {
        return $this->__toString();
    }

    /**
     * @return string
     */
    public function format_json()
    {
        return json_encode($this);
    }

    /**
     * @return string
     */
    public function __toString(){
        try {
            return $this->prettyPrint($this->getValue());
        } catch(\Exception $e){
            $this->log("%YError converting to string representation: ". $e->getMessage().'%n');
            return '';
        }
    }

    /**
     * @return bool
     */
    protected function _validate() {
        return true;
    }

    /**
     * @return mixed
     */
    protected function getOriginalValue(){
        return $this->originalValue;
    }

    /**
     * @param $value
     */
    protected function setOriginalValue($value){
        $this->originalValue = $value;
    }

    /**
     * Return is Assoc array or not assoc
     * @param $arr array|TArray
     * @return bool
     */
    function isAssoc($arr)
    {
        if (is_array($arr)){
            if (array() === $arr) return false;
            return array_keys($arr) !== range(0, count($arr) - 1);
        } elseif ($arr instanceof TArray) {
            return $this->isAssoc($arr->getOriginalValue());
        }

        return true;
    }

    /**
     * @param $value
     * @param bool $return
     *
     * @return string
     */
    function prettyPrint($value, $return = true){
        $result  = is_object($value) ? StringHelper::basename(get_class($value)) : gettype($value);
        $result  = ($result == 'TArray')?"":"($result)";
        $result .= '<pre>'.stripslashes(is_array($value)?json_encode($value,JSON_PRETTY_PRINT):(string)$value).'</pre>' ;
        if (!$return){
            echo($result);
        }
        return $result;
    }
}