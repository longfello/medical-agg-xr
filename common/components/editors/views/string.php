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
 $form = \common\components\ActiveForm::begin(['action' => $action, 'options' => ['id' => $id, 'class' => 'kvk-ajax-form', 'data-target' => '#'.$id]]);

echo $form->field($model, 'value', [
    'template' => $template,
    'addon' => [
        'append' => [
            'content' => \yii\helpers\Html::submitButton('save', ['class' => 'btn btn-info']),
            'asButton' => true
        ]
    ]
])->input('text');

\common\components\ActiveForm::end();