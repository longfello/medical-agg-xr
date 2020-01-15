<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 16.11.17
 * Time: 03:09
 */

namespace common\components\widgets\controls;


use common\components\View;
use frontend\assets\LookupAsset;
use yii\base\Widget;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Class BaseLookupField
 * @package common\components\widgets\controls
 */
class BaseLookupField extends Widget
{
    /** @var ActiveRecord $model */
    public $model;

    /** @var  mixed $attribute */
    public $attribute;

    /** @var string $variantAttribute */
    public $variantAttribute;

    /** @var  ActiveRecord[] $variants */
    public $variants = [];

    /**
     * @var array
     */
    public $pluginOptions = [];

    /** @var string $placeholder */
    public $placeholder = 'Type your own info or click to choose';

    /** @var array $options for view */
    protected $options = [];

    /**
     */
    public function init()
    {
        if (is_null($this->variantAttribute)) $this->variantAttribute = $this->attribute;

        $count = count($this->variants);
        for($i=0; $i<$count-1; $i++){
            for($n=$i+1; $n<$count; $n++){
                if(isset($this->variants[$n]) && isset($this->variants[$i])) {
                    if (\Yii::$app->comparer->isEquivalent($this->variants[$i]->getAttribute($this->variantAttribute), $this->variants[$n]->getAttribute($this->variantAttribute))) {
                        unset($this->variants[$n]);
                    }
                }
            }
        }

        foreach ($this->variants as $variant) {
            $value = $variant->getAttribute($this->variantAttribute);

            if (!empty($value)) {
                $item = [];
                $item['key'] = $value;
                $item['value'] = $value;

                $this->options[] = $item;
            }
        }

        parent::init();
    }

    /**
     * @param bool $autoGenerate
     *
     * @return string
     */
    public function getId($autoGenerate = true)
    {
        return Html::getInputId($this->model, $this->attribute);
    }

    /**
     * @param bool $customData
     */
    public function registerSelect($customData = false)
    {
        $id = $this->id;

        $opts = $this->pluginOptions;
        $opts['items'] = is_array($customData) && !empty($customData) ? $customData : $this->options;
        $opts['placeholder'] = $this->placeholder;

        $options = Json::encode($opts);

        /** @var View $view */
        $view = $this->getView();
        LookupAsset::register($view);
        $view->registerModalScript("jQuery('#$id').lookup($options);");
    }
}