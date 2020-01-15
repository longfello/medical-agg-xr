<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 16.11.17
 * Time: 03:09
 */

namespace common\components\widgets\controls;

use borales\extensions\phoneInput\PhoneInputAsset;
use common\components\View;
use yii\helpers\Json;
use yii\web\JsExpression;


/**
 * Class PhoneLookupField
 * @package common\components\widgets\controls
 * @property View $view
 */
class PhoneLookupField extends BaseLookupField
{
    /**
     * @var array
     */
    public $phoneInputOptions = ['nationalMode' => false];

    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->pluginOptions['onSelect'] = new JsExpression('function(that, selectedValue) {
            that.intlTelInput("setNumber", selectedValue);
        }');
    }

    /**
     * @return string
     */
    public function run()
    {
        PhoneInputAsset::register($this->view);

        $jsOptions = $this->phoneInputOptions ? Json::encode($this->phoneInputOptions) : "";
        $this->view->registerModalScript("$('#$this->id').intlTelInput($jsOptions);");

        $this->registerSelect();

        return $this->render('phone-lookup-field', [
            'model' => $this->model,
            'attribute'=>$this->attribute
        ]);
    }
}