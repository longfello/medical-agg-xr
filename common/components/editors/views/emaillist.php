<?php
/** @var $this \common\components\View */
/** @var $model \common\components\editors\EditorEmailList */
/** @var $action string */

use common\components\ActiveForm;
use yii\helpers\Html;

$action = ($model instanceof \common\components\editors\EditorAWSEmailList)
    ? \yii\helpers\Url::to(['/admin/update-aws-email-list', 'key' => $model->key, 'title' => $model->name])
    : \yii\helpers\Url::to(['/admin/update-settings', 'key' => $model->key]);

$newFieldTemplate = '
                <div class="form-group highlight-addon field-email-value">
                    <div class="col-xs-10">
                        <input id="field-email-value" class="form-control" name="'.$model->formName().'[emailList][]" type="email" placeholder="Email"/>
                    </div>
                    <div class="col-xs-2 padding-left-0">
                        <button class="btn btn-sm btn-remove btn-danger pull-right" type="button">
                            <span class="glyphicon glyphicon-minus"></span>
                        </button>
                    </div>
                    <div class="col-xs-10"></div>
                </div>';
$newFieldTemplate = str_replace("\n", '', $newFieldTemplate);
?>
<style>
    .margin-top-15 {
        margin-top: 15px;
    }
</style>
<div class="margin-top-15">
    <div id="rotation-form">
        <div class="controls">
            <?php $form = ActiveForm::begin([
                'action' => $action,
                'method' => 'post',
                'enableClientValidation' => false,
                'enableAjaxValidation' => false,
            ]); ?>

            <?php // this hidden field keeps option to load model's POST data when user removed all visible fields ?>
            <input class="form-control" name="<?= $model->formName() ?>[keepEmptyPost]" type="hidden" value=""/>

            <div class="row fields-container">
            <?php
                foreach($model->emailList as $key => $emailItem) {
                    $errors = $model->getErrors($key);
                    ?>
                <div class="form-group highlight-addon field-email-value <?= count($errors) ? 'has-error' : '' ?>">
                    <div class="col-xs-10">
                        <input id="field-email-value-2" class="form-control" placeholder="Email" name="<?= $model->formName() ?>[emailList][]" type="email" value="<?= Html::encode($emailItem) ?>"/>
                    </div>
                    <div class="col-xs-2">
                        <button class="btn btn-sm btn-remove btn-danger pull-right" type="button"><span class="glyphicon glyphicon-minus"></span></button>
                    </div>
                    <div class="col-xs-10">
                        <div class="help-block alert alert-danger"><?= count($errors) ? $errors[0] : '' ?></div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="row margin-top-15">
                <div class="col-xs-10">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success btn-margin']); ?>
                    <?= Html::button('Cancel', ['class' => 'btn btn-info js-close-popup']); ?>
                </div>

                <div class="col-xs-2 padding-left-0">
                    <button class="btn btn-sm btn-success btn-add pull-right" type="button"><span class="glyphicon glyphicon-plus"></span></button>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$script = <<< JS
    $(document).ready(function() {
        var removeField = function(e) {
            $(this).parent().parent().remove();
        };

        var addField = function(e) {
            var newField = $('$newFieldTemplate');
            newField.find('.btn-remove').click(removeField);
            $('.fields-container').append(newField);
        };

        $('.btn-add').click(addField);
        $('.btn-remove').click(removeField);
    });
JS;
$this->registerJs($script);
