<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 01.06.17
 * Time: 11:32
 */

namespace common\components;

use yii\helpers\Html;
use yii\web\JsExpression;

//use yii\web\View;

/**
 * Class TimedClientForm
 * @package common\components
 */
class TimedClientForm extends ActiveForm
{
    /**
     * @var string
     */
    public $errorSummaryCssClass = 'alert alert-danger';

    /**
     * @param TimedForm $model
     *
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function registerTries(TimedForm $model)
    {
        if ($model->isExpired()) {
            $expire = $model->getExpire();
            $message = new JsExpression($model->triesErrorMessage);
            $attribute = new JsExpression(Html::getInputId($model, $model->maxTriesCountAttribute));
            // $model->getid.'-'$model->maxTriesCountAttribute);
            $view = $this->getView();
            $form = 'jQuery("#' . $this->options['id'] . '" )';
            $formPrefix = $this->options['data-name-prefix'];
            $view->registerJs("
(function($){
  if (!Date.now) {
    Date.now = function() { return new Date().getTime(); }
  }  

  var ts     = Math.floor(Date.now() / 1000);
  var expire = ts + parseInt({$expire});
  var form   = {$form};
  var msg    = '{$message}';
  var ti     = setInterval(sf, 1000); 
  var button = $(form).find('.js-submit-{$formPrefix}-form');
  
  $(form).on('submit', sf);
  
  function sf(e){
  var cts = Math.floor(Date.now() / 1000);
    if (expire > cts) {
      if (e) {
      e.preventDefault();
    }
      
      current_message = msg.replace('{time}', expire - cts);
      $(form).yiiActiveForm('updateMessages', {
      '{$attribute}': [current_message]   
      }, true);  
      $('div.help-block').hide();
      //$('#{$attribute}').next('div.help-block').html(current_message);
      
      
      if ('{$attribute}' == 'loginform-email') {
          $(form).yiiActiveForm('updateAttribute', 'loginform-password',[current_message]); 
      }
      if ('{$attribute}' == 'loginformpage-email') {
          $(form).yiiActiveForm('updateAttribute', 'loginformpage-password',[current_message]); 
      }

      button.attr('data-original-title', 'Registration will be available in a few seconds');
      
      return false;
    } else {
    $(form).yiiActiveForm('updateMessages', {
      '{$attribute}': false,
      }, true);      
      clearInterval(ti);
      $(form).find('.js-submit-{$formPrefix}-form').attr('data-original-title', 'Ready for registration');
      //$(form).find('.js-submit-{$formPrefix}-form').attr('disabled', false);
    }
  }
  
})(jQuery);      
", View::POS_READY, 'timeout-' . $model->id);
        }
    }
}