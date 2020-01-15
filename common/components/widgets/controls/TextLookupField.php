<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 16.11.17
 * Time: 03:09
 */

namespace common\components\widgets\controls;

use yii\helpers\Html;


/**
 * Class TextLookupField
 * @package common\components\widgets\controls
 */
class TextLookupField extends BaseLookupField
{
    /**
     * @var array
     */
    public $pluginOptions = ['create' => true];
    /**
     * @var array
     */
    public $inputOptions = [];

    /**
     * @return string
     */
    public function run()
    {
        $this->registerSelect();

        return $this->render('text-lookup-field', [
            'model' => $this->model,
            'attribute' => $this->attribute,
            'inputOptions' => $this->inputOptions,
        ]);
    }

    /**
     * @param bool $autoGenerate
     *
     * @return mixed|string
     */
    public function getId($autoGenerate = true)
    {
        return (isset($this->inputOptions['id'])) ? $this->inputOptions['id'] : Html::getInputId($this->model,
            $this->attribute);
    }
}