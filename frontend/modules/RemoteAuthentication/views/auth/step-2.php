<?php
/**
 * @var \frontend\modules\patient\forms\ActivateForm $form
 * @var \frontend\modules\RemoteAuthentication\form\PendingPatients $model
 */

?>
<div class="col-xs-12 padding-0">
<?= $form->field( $model, 'sms_confirmation_code' )->textInput( [
    'placeholder' => '6 digit confirmation code',
    'class'       => "pl-form-control",
    'style'       => 'min-width: 300px;',
    'maxlength'   => 6
] ) ?>
</div>
