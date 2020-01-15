<?php
namespace common\components\widgets\controls;

use common\components\View;
use yii\helpers\Html;

/**
 * Class PhoneInput
 * @package common\components\widgets\controls
 */
class PhoneInput extends \borales\extensions\phoneInput\PhoneInput
{
    /**
     * @const string
     */
    const SWITCH_TO_AMERICAN = 'United States number';
    /**
     * @const string
     */
    const SWITCH_TO_INTERNATIONAL = 'International number';

    /**
     * @var \yii\base\Model
     */
    public $model;
    /**
     * @var string
     */
    public $attribute;
    /**
     * @var string
     */
    public $inputId;
    /**
     * @var string
     */
    public $uniqueSuffix;
    /**
     * @var string
     */
    public $linkId;
    /**
     * @var string
     */
    public $switchText;
    /**
     * @var string
     */
    public $placeholder = 'Example: 555-555-5555';
    /***
     * @var string
     */
    public $custom_class;
    /**
     *
     */
    public function init()
    {
        parent::init();

        $attr = $this->attribute;
        $this->inputId = Html::getInputId($this->model, $this->attribute);
        if (!empty($this->uniqueSuffix)) {
            $this->inputId .= '-' . $this->uniqueSuffix;
        }
        $this->linkId = $this->inputId.'-switch';

        if (substr($this->model->$attr, 0, 1) != '+') {
            $this->view->registerJs('$("#'.$this->inputId.'").intlTelInput("destroy");');
            $this->switchText = static::SWITCH_TO_INTERNATIONAL;
        } else {
            $this->switchText = static::SWITCH_TO_AMERICAN;
        }
        $this->view->registerJs('$("#'.$this->inputId.'").attr("placeholder", "'.$this->placeholder.'");');
    }

    /**
     * @return string
     */
    public function run()
    {
        $html = Html::a($this->switchText, null, ['id' => $this->linkId, 'class' => 'ml-auto pl-label pull-right intl-phone-change-label', 'style' => ['cursor' => 'pointer']]);
        $html.= Html::tag('div','',['class'=>'clearfix']);
        $html.= Html::label($this->placeholder,$this->inputId,['id'=>'label-'.$this->inputId, 'class'=>'attached-label']);
        $html.= Html::activeTextInput($this->model, $this->attribute, [
            'id' => $this->inputId,
            'class' => ($this->custom_class ? $this->custom_class : 'pl-form-control pl-form-group__control js-no-autosave').' intl-phone-input',
            'placeholder' => $this->placeholder
        ]);

        $html.= $this->renderScript();
        return $html;
    }

    /**
     * @return string
     */
    private function renderScript(){
        $script = '
$(document).ready(function(){
    setTimeout(function(){
        $("#'.$this->linkId.'").on("click.switchFormat", function () {
            var switchLink = $("#'.$this->linkId.'");
            var inputPhone = $("#'.$this->inputId.'");
            var phone = $.trim(inputPhone.val());
    
            inputPhone.val(phone);
            if (switchLink.text() == "'.static::SWITCH_TO_AMERICAN.'") {
                inputPhone.intlTelInput("destroy");
                switchLink.text("'.static::SWITCH_TO_INTERNATIONAL.'");
                inputPhone.attr("placeholder", "");
                inputPhone.trigger(\'blur\');
                if (phone.substr(0, 2) == "+1") {
                    inputPhone.val($.trim(phone.substr(2)));
                }
            } else {
                switchLink.text("'.static::SWITCH_TO_AMERICAN.'");
                inputPhone.attr("placeholder", "+1 ");
                inputPhone.trigger(\'click\');
    
                if (phone.length === 0) {
                    inputPhone.val("");
                } else if (phone.substr(0, 1) != "+") {
                    inputPhone.val("+1" + phone);
                }
                inputPhone.intlTelInput({nationalMode: false, autoPlaceholder: "off"});
            }
        });
        $("#' . $this->inputId . '").trigger("init");
    }, 200);
});
        ';

        if (\Yii::$app->request->isAjax){
            $script = "
<script>        
{$script}
</script>
            ";
        } else {
            $this->view->registerJs($script);
            $script = '';
        }

        return $script;
    }
}
