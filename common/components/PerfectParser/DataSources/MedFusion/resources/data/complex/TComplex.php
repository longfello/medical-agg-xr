<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 07.02.18
 * Time: 10:23
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TElement;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\RResource;
use yii\base\Exception;
use yii\helpers\BaseStringHelper;
use yii\helpers\StringHelper;

/**
 * Class TComplex
 * @package common\components\PerfectParser
 */
class TComplex extends TElement
{
    /**
     * Field data is required
     */
    const REQUIRED = 'required';
    /**
     * Field data is optional. Default
     */
    const OPTIONAL = 'optional';

    /**
     * Return structure of complex data type
     *
     * return [
     *   ['name', TString::class, self::REQUIRED],
     *   ['opacity', TCodeableConcept::class, self::OPTIONAL] // Optional by dafault
     *   ['opacity2', TCodeableConcept::class]
     * ];
     *
     * @return array
     * @throws Exception
     */
    public function structure(){

        if (true) throw new Exception("Implementation required: ".get_called_class());
        return [];
    }

    /**
     * @inheritdoc
     * @param $data
     * @param $silent bool
     *
     * @return bool
     */
    public function load($data, $silent = false){
        $this->log("Loading ". $this->elName."[".BaseStringHelper::basename(get_called_class())."]");
        $this->incPrefix();
        $this->log("Load Data:");
        $this->incPrefix();
        $this->log($this->prettyPrint($data));
        $this->decPrefix();

        try {
            $this->setOriginalValue($data);

            foreach($data as $elementKey => $element){

                $elementKey = trim(trim($elementKey, '@'));
                if (property_exists($this, $elementKey)){

                    $type = $this->__getType($elementKey);

                    if (is_array($type)){
                        $type = array_shift($type);
                        // load array element

                        if($this->isAssoc($element)) {
                            $this->log("Processing array - only one item founded. Interpretate as array.");
                            $element = [$element];
                        }

                        $elementDatas = [];
                        $i = 0;
                        foreach ($element as $subelement){
                            $i++;
                            $this->conditionalLog($this->isAssoc($element),"  Iteration {$i} on {$elementKey} ". $this->prettyPrint($subelement));
                            $elementData = $this->__initClass($type, $elementKey, $subelement);
                            if ($elementData  instanceof TElement){
                                $elementData->debug = $this->debug;
                                $subElementData = $subelement;

                                $elementData->load($subElementData);
                                $elementDatas[] = $elementData;
                            }
                        }
                        $this->$elementKey = new TArray($elementDatas, $this->elName.'->'.$elementKey.'[]');
                    } else {
                        // load simply element
                        if (is_null($this->$elementKey)){
                            $this->_initField($elementKey, $element);
                        }
                        if ($this->$elementKey instanceof TElement){
                            $this->$elementKey->debug = $this->debug;
                            ($this->$elementKey)->load($element);

                        }

                    }
                } else {
                    $this->error("Undefined property ".get_called_class()."::".$elementKey.' => '.$this->prettyPrint($element));
                    if ($this->processingMode == self::MODE_STRICT){
                        $e = new \Exception();
                        $this->error($e->getTraceAsString());
                        die();
                    }
                }
            }

            $ok = $this->_validate();
        } catch (\Throwable $e){
            $ok = false;
            $this->error($e->getMessage());
        }
        if (!$ok) {
            try{
                $this->log("%YError converting value ".json_encode($data).' to '. get_called_class().' in '.$this->elName.'%n');
            } catch(\Exception $e){
                $this->error($e->getMessage());
            }
        }

        $this->decPrefix();
        return $ok;
    }

    /**
     * @param $attribute
     *
     * @return null
     */
    public function getText($attribute){
        if (isset($this->$attribute) && $this->$attribute && $this->$attribute instanceof TElement){
            return $this->$attribute->__toString();
        }
        return null;
    }

    /**
     * @param $name
     *
     * @return string
     * @throws Exception
     */
    public function __getType($name){
        $type = null;
        $fields = $this->structure();
        // Initialization of required fields
        foreach ($fields as $field){
            $field_name = array_shift($field);
            $field_name = is_array($field_name)?$field_name:[$field_name];
            if (in_array($name, $field_name)){
                $type = array_shift($field);
            }
        }
        if (is_null($type)){
            throw new Exception("Field `{$name}` description required in function structure() in class `". get_called_class() ."`");
        }
        return $type;
    }

    /**
     * @param $name
     * @param null|array|TArray|mixed $data
     *
     * @throws Exception
     */
    protected function _initField($name, $data = null){
        $type = $this->__getType($name);

        if (is_array($type)){
            $this->$name = new TArray(null, $this->elName.'->'.$name);
        }

        $this->$name = $this->__initClass($type, $name, $data);
    }


    /**
     * Init field
     * @param $class
     * @param $elName null|string
     * @param $data null|array|TArray|mixed
     *
     * @return mixed
     * @throws Exception
     */
    protected function __initClass($class, $elName = null, $data = null){
        $this->log('');
        $this->log('%gInit '.$this->elName.'->'.$elName.' as '. StringHelper::basename($class).'%n');
        if (class_exists($class)){
            if ($class == RResource::class) {
                if (!is_null($data)){
                    $dataAsArray = ($data instanceof TArray)?$data->getOriginalValue():$data;
                    $resourceType = isset($dataAsArray['resourceType'])?$dataAsArray['resourceType']:false;
                    if ($resourceType){
                        $this->log("%gInit RResource as subresource: R". $resourceType .'%n');
                        return RResource::create($resourceType, $this->elName.'->'.$elName.'['.$resourceType.']');
                    }
                }
                throw new Exception('Expected not null data parameter.');
            } else {
                $el = new $class(null, $this->elName.'->'.$elName);
                $el->debug = $this->debug;
                return $el;
            }
        } else {
            throw new Exception('Class not found: '. $class);
        }
    }
}