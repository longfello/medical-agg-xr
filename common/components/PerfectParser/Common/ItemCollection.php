<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 09.03.18
 * Time: 10:41
 */

namespace common\components\PerfectParser\Common;


/**
 * Class ItemCollection
 * @package common\components\PerfectParser
 */
class ItemCollection
{
    /** @var mixed[] */
    public $items=[];

    /**
     * Add item to collection
     * @param $item
     * @param null $timeOrIndex
     */
    public function add($item, $timeOrIndex = null){
        if (!is_null($timeOrIndex)){
            $this->items[$timeOrIndex] = $item;
        } else {
            $this->items[] = $item;
        }
    }

    /**
     * Return first item from collection by time
     * @return mixed|null
     */
    public function getFirstByTime(){
        if ($this->items) {
            $index = min(array_keys($this->items));
            return $this->items[$index];
        }
        return null;
    }

    /**
     * Return last item from collection by time
     * @return mixed|null
     */
    public function getLastByTime(){
        if ($this->items) {
            $index = max(array_keys($this->items));
            return $this->items[$index];
        }
        return null;
    }
}