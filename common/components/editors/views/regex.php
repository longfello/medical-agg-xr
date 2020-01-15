<?php

use common\components\editors\EditorRegex;
use common\components\View;
use common\components\ActiveForm;
use yii\bootstrap\Html;

/**
 * @var $this View
 * @var $model EditorRegex
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
        "Value format: pattern|pattern|pattern<br/>".
        "Pattern validation regex: " . $model::EMAIL_PATTERN_REGEX
); ?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success btn-margin']); ?>
    <?= Html::button('Cancel', ['class' => 'btn btn-info js-close-popup']); ?>
</div>

<?php

ActiveForm::end();

?>