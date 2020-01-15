<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 03.11.18
 * Time: 13:25
 */
use common\components\editors\EditorLastNameRegex;
use common\components\View;
use common\components\ActiveForm;
use yii\bootstrap\Html;

/**
 * @var $this View
 * @var $model EditorLastNameRegex
 * @var $action string
 */

$action = \yii\helpers\Url::to(['/admin/update-settings', 'key' => $model->key]);

if (!$model->value) {
    $model->value = $model->default_value;
}

$form =  ActiveForm::begin([
    'action' => $action,
    'method' => 'post',
    'enableClientValidation' => false,
    'enableAjaxValidation' => false,
]);

?>

<?= $form->field($model, 'value', [
    "errorOptions" => [
        "class" => "help-block alert alert-danger",
        "encode" => false,
        "errorSource" => function (\yii\base\Model $model, $attribute) {
            if (count($model->getErrors($attribute)) == 0) {
                return null;
            }

            if (count($model->getErrors($attribute)) == 1) {
                return $model->getFirstError($attribute);
            }

            return sprintf("<ul>%s</ul>", implode("", array_map(function ($error) {
                return sprintf("<li>%s</li>", $error);
            }, $model->getErrors($attribute))));
        }
    ]
])->textarea(['rows' => '8'])->hint(
    "Value format: pattern|pattern<br/>".
    "Pattern validation lastnameregex: " . $model::LAST_NAME_REGEX
); ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success btn-margin']); ?>
        <?= Html::button('Cancel', ['class' => 'btn btn-info js-close-popup']); ?>
    </div>

<?php

ActiveForm::end();

?>