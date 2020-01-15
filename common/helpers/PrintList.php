<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 06.07.18
 * Time: 11:18
 */

namespace common\helpers;

use yii\helpers\Html;

/**
 * Class PrintList
 * @package common\helpers
 */
class PrintList
{
    /**
     * Build list as ul list
     * @param $items
     * @param $options
     * @return string
     */
    public function simple($items, $options = [])
    {
        return Html::ul($items, $options);
    }

    /**
     * Build list as string
     * @param $items
     * @return string
     *
     * example: entry 1, entry 2, entry 3 and entry 4
     */
    public function native($items)
    {
        $result = '';
        if (!empty($items) && is_array($items)) {
            $arrayLength = count($items);

            foreach ($items as $key => $item) {
                if($key == 0) { // first item with empty prepend
                    $result .= $item;
                } elseif ($key == $arrayLength-1) { //last item with prepend and
                    $result .= " and $item";
                } else { //other items with coma
                    $result .= ", $item";
                }
            }
        }

        return $result;
    }
}