<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 20.02.18
 * Time: 14:23
 */

namespace common\components\PerfectParser\Common;


use yii\helpers\BaseInflector;

/**
 * Class UpdateResults
 * @package common\components\PerfectParser
 */
class UpdateResults {
    /** @const string Resource skipped */
    const ACTION_SKIP = 'skip';
    /** @const string Resource inserted */
    const ACTION_INSERT = 'insert';
    /** @const string Resource error */
    const ACTION_ERROR = 'error';
    /** @const string Resource delete */
    const ACTION_DELETE = 'delete';

    /** @var string[][] Array of Array of actions. Key = resource name */
    public $log;


    /**
     * @param $resource
     */
    public function insert($resource){
        $this->add(self::ACTION_INSERT, $resource);
    }

    /**
     * @param $resource
     */
    public function error($resource){
        $this->add(self::ACTION_ERROR, $resource);
    }

    /**
     * @param $resource
     */
    public function skip($resource){
        $this->add(self::ACTION_SKIP, $resource);
    }

    /**
     * @param $resource
     */
    public function delete($resource){
        $this->add(self::ACTION_DELETE, $resource);
    }

    /**
     * @return bool
     */
    public function isReallyUpdated()
    {
        if (!empty($this->log)) {
            foreach ($this->log as $resource => $item) {
                if (in_array(self::ACTION_DELETE, $item) || in_array(self::ACTION_INSERT, $item)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function changes()
    {
        $changes = [];
        if (!empty($this->log)) {
            foreach ($this->log as $resource => $item) {
                $name = Helper::get_medinfo_name_from_resource($resource);
                if (in_array(self::ACTION_INSERT, $item) || in_array(self::ACTION_DELETE, $item)) {
                    $changes[$name] = $name;
                }
            }
        }
        return $changes;
    }

    /**
     * @param bool $includeSkips
     * @param bool $includeErrors
     *
     * @return string
     */
    public function total($includeSkips = false, $includeErrors = false){
        $text = '';
        $update = $skip = $error = [];
        foreach ($this->log as $resource => $item) {
            $name = BaseInflector::camel2words($resource);
            if (in_array(self::ACTION_DELETE, $item)) { $update[$name] = $name; }
            if (in_array(self::ACTION_INSERT, $item)) { $update[$name] = $name; }
            if (in_array(self::ACTION_SKIP, $item))   { $skip[$name]   = $name; }
            if (in_array(self::ACTION_ERROR, $item))  { $error[$name]  = $name; }
        }

        if ($update) {
            $text .= "Updated medical info: ".implode(', ', $update).'. ';
        }
        if ($includeErrors && $error) {
            $text .= "Error occurred while processing: ".implode(', ', $error).'. ';
        }
        if ($includeSkips && $skip) {
            $text .= "Already actual information: ".implode(', ', $skip).'. ';
        }

        return $text;
    }

    /**
     * @param $action
     * @param $resource
     */
    private function add($action, $resource){

        \Yii::$app->perfectParser->log("%B>>> $action [$resource]%n");

        if (!isset($this->log[$resource])){
            $this->log[$resource] = [];
        }
        $this->log[$resource][] = $action;
    }
}