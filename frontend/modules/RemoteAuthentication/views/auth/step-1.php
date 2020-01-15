<?php
/**
 * @var \frontend\modules\patient\forms\ActivateForm $form
 * @var \frontend\modules\RemoteAuthentication\form\PendingPatients $model
 */

?>
<div class="col-xs-12 padding-0">
    <?= $form->field( $model, 'cell_phone' )->textInput( [
        'placeholder' => 'Cell Phone',
        'class'       => "pl-form-control",
        'style'       => 'min-width: 300px;'
    ] ) ?>

    <?= $form->field( $model, 'date_of_birth' )->textInput( [
        'placeholder' => 'Birth Year',
        'class'       => "pl-form-control",
        'style'       => 'max-width: 100px;'
    ] ) ?>

</div>
