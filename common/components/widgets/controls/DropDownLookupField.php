<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 16.11.17
 * Time: 03:09
 */

namespace common\components\widgets\controls;


/**
 * Class DropDownLookupField
 * @package common\components\widgets\controls
 */
class DropDownLookupField extends BaseLookupField
{

    /**
     * @var array
     */
    public $pluginOptions = ['group' =>true, 'editable' =>false];
    /**
     * @var
     */
    public $items;

    /**
     * @return string
     */
    public function run()
    {
        $_items = [];
        if($this->options) {
            $_items['Information from practices'] = $this->options;
            if($this->items) { $_items['Own Choice'] = $this->items; }
        } else {
            $_items['Own Choice'] = $this->items;
        }

        $this->registerSelect($_items);

        return $this->render('dropdown-lookup-field', [
            'model' => $this->model,
            'attribute'=>$this->attribute
        ]);
    }
}