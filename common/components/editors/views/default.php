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

$action = \yii\helpers\Url::to(['/admin/update-settings', 'key' => $model->key]);
?>

<?php $form = \common\components\ActiveForm::begin(['action' => $action]); ?>

<?= $form->field($model, 'value')->textInput() ?>

<div class="form-group">
    <?= \yii\bootstrap\Html::submitButton('OK', ['class' => 'btn btn-success']) ?>
    <?= \yii\bootstrap\Html::button('Cancel', ['class' => 'btn btn-info js-close-popup']) ?>
</div>

<?php \common\components\ActiveForm::end(); ?>
