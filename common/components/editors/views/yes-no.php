<?php
/**
 * Created by PhpStorm.
 * User: Miloslawsky
 * Date: 26.10.2018
 * Time: 10:24
 */

 /** @var $this \common\components\View */
 /** @var $model \common\components\editors\prototype */
 /** @var $action string */

$action = \yii\helpers\Url::to(['/admin/update-settings', 'key' => $model->key, 'inline' => true]);

$id = $model->getID();

$template = '{error}{input}';

if (!$model->value) $model->value = $model->default_value;
 $form = \common\components\ActiveForm::begin(['action' => $action, 'options' => ['id' => $id, 'class' => 'kvk-ajax-form']]);

echo \kartik\switchinput\SwitchInput::widget([
    'model' => $model,
    'attribute' => 'value',
    'type' => \kartik\switchinput\SwitchInput::CHECKBOX,
    'options' => [
        'id' => $id.'-control'
    ],
    'pluginEvents' => [
        "switchChange.bootstrapSwitch" => "function() {
                    $('#{$id}').submit();  
         }",
    ]
]);

\common\components\ActiveForm::end();