<?php
/**
 * @var \frontend\modules\patient\forms\ActivateForm $form
 * @var \frontend\modules\RemoteAuthentication\form\PendingPatients $model
 * @var \common\models\LoginForm $loginForm
 * @var string $error4use
 * @var string $error4create
 * @var string $error4login
 */

use yii\helpers\Html;

echo Html::hiddenInput('step-4', 1);
$mainFormClass   = (Yii::$app->patient->isGuest || $error4create || $error4login)?"":"hidden";
$authedFormClass = (Yii::$app->patient->isGuest || $error4create || $error4login)?"hidden":"";
$username = isset(Yii::$app->patient->model->username)?Yii::$app->patient->model->username:'';
?>
<div class="col-md-12 <?= $mainFormClass?>" id="js-assoc-account">
    <div class="col-md-6">
        <fieldset class="hp-fieldset hp-form__fieldset hp-form__remote-pending left-side">
            <p class="hp-form__legend">New here? It's simple to create a new account!</p>

            <?php if ($error4create) { ?>
                <div class="row">
                    <div class="alert alert-danger"><?= $error4create ?></div>
                </div>
            <?php } ?>

            <?= $form->field($model, 'user_name', [
                'template' => '<div class=\'pl-form-group hp-form-group hp-form__group form-group\'>{input}</div>',
                'addRequiredToPlaceholder' => false])
                ->textInput(['tabindex' => '5',
                             'class' => 'hp-form-control hp-form__control',
                             'placeholder' => 'Select Username']); ?>

            <?= $form->field($model, 'pass', [
                'template' => '<div class=\'pl-form-group hp-form-group hp-form__group form-group\'>{input}<span for="pendingpatients-pass" class="pl-form-label pl-form-group__label" data-password-toggle>SHOW</span></div>',
                'addRequiredToPlaceholder' => false])
                ->passwordInput(['tabindex' => '6',
                                 'class' => 'hp-form-control hp-form__control',
                                 'placeholder' => 'Select Password']); ?>

            <?= $form->field($model, 'pass2', [
                'template' => '<div class=\'pl-form-group hp-form-group hp-form__group form-group\'>{input}<span for="pendingpatients-pass2" class="pl-form-label pl-form-group__label" data-password-toggle>SHOW</span></div>',
                'addRequiredToPlaceholder' => false])
                ->passwordInput(['tabindex' => '6',
                                 'class' => 'hp-form-control hp-form__control',
                                 'placeholder' => 'Password Repeat']); ?>

            <?= Html::submitButton('Create account', ['class' => 'btn btn--submit hp-form__submit js-action-submit', 'name' => 'login-button', 'tabindex' => '7', 'data-acton' => 'use-create']) ?>
        </fieldset>
    </div>

    <div class="col-md-6">
        <fieldset class="hp-fieldset hp-form__fieldset hp-form__remote-pending">
            <p class="hp-form__legend">Already have an account? Just login!</p>

            <?php if ($error4login) { ?>
                <div class="row">
                    <div class="alert alert-danger"><?= $error4login ?></div>
                </div>
            <?php } ?>

            <?php
            $form = \common\components\ActiveForm::begin([
                'id' => 'signInForm',
                'action' => '/login',
                'enableClientValidation' => false,
                'enableAjaxValidation' => false,
                'fieldConfig' => [
                    'template' => "\n<div class='pl-form-group hp-form-group hp-form__group form-group'>{input}</div>",
                ],
                'options' => ['class' => 'hp-form hp-form--fixed hp-welcome__form']
            ]);
            ?>
            <?= $form->field($loginForm, 'username', [
                'addRequiredToPlaceholder' => false,
                'template' => '<div class=\'pl-form-group hp-form-group hp-form__group form-group\'>{input}</div>',
            ])->textInput([
                'tabindex' => '5',
                'class' => 'hp-form-control hp-form__control',
                'placeholder' => 'Username']); ?>

            <?= $form->field($loginForm, 'password', [
                'template' => '<div class=\'pl-form-group hp-form-group hp-form__group form-group\'>{input}<span for="loginform-password" class="pl-form-label pl-form-group__label" data-password-toggle>SHOW</span></div>',
                'addRequiredToPlaceholder' => false])
                ->passwordInput([
                    'tabindex' => '6',
                    'class' => 'hp-form-control hp-form__control',
                    'placeholder' => 'Password']); ?>

            <p class="hp-text hp-form__text">
                <a href="#" class="hp-form-link hp-form__link btn-forgot-popup"
                   data-type-forgot="password"
                   data-show_success="yes">Forgot Password? ></a></p>
            <p class="hp-text hp-form__text">
                <a href="#" class="hp-form-link hp-form__link btn-forgot-username-popup"
                   data-type-forgot="username"
                   data-show_success="yes">Forgot Username? ></a></p>
            <?= Html::submitButton('Sign In', ['class' => 'btn btn--submit hp-form__submit js-action-submit', 'name' => 'login-button', 'tabindex' => '7', 'data-acton' => 'use-login']) ?>
            <?php \common\components\ActiveForm::end(); ?>
        </fieldset>
    </div>
</div>
<div class="col-md-12 <?= $authedFormClass ?>" id="js-assoc-current">

    <?php if ($error4use) { ?>
        <div class="row">
            <div class="alert alert-danger"><?= $error4use ?></div>
        </div>
    <?php } ?>

    <p class="well-sm">Your information will be added to account: <?= $authedFormClass?"":$username; ?></p>
    <?= Html::submitButton('Continue', ['class' => 'btn btn--submit js-action-submit', 'name' => 'continue-button', 'data-acton' => 'use-current']) ?>
    <a href="#" class="js-choose-another">Choose another account instead</a>
</div>