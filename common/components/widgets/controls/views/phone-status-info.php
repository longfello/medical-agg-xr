<?php
/**
 * @param common\components\View $this
 * @param common\models\Patient $patient
 * @param bool $withInput
 */


if ((bool) $patient->meta->getValue(\common\models\PatientMeta::CELLPHONE_INVALID, false)) {
    $phoneStatus = "Invalid";
    $text = "Text messages can't be delivered to phone number you provided - please, review it carefully
        or <a href='/subscriber-home/account-support'>contact support</a>";
    $btnText = 'Try again now';
} else {
    $phoneStatus = "Unverified";
    $text = "You have not clicked to confirm the confirmation text we sent to <b id='phoneForRSModal'></b>.
        Please check for that text and click it now. If you can’t find it, you can re-send it";
    $btnText = 'Resend';
}
?>

<?php if ($withInput): ?>
    <?php if ($patient->is_confirmed_cell_phone): ?>
        <div class="phone-verify-block">
            <div class="phone-verify-text green"><b>Verified</b></div>
        </div>
    <?php else: ?>
        <div class="phone-verify-block">
            <div class="phone-verify-text"><b><a href="#" class="red verify-link"><?= $phoneStatus ?></a></b></div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <?php if ($patient->is_confirmed_cell_phone): ?>
        <span class="hidden-xs"><b>Texts will be sent to: </b><?= $patient->cell_phone ?> <div class="phone-verify-text green"><b>Verified</b></div></span>
    <?php else: ?>
        <span class="hidden-xs"><b>Texts will be sent to: </b><?= $patient->cell_phone ?> <div class="phone-verify-text"><b><a href="#" class="red verify-link"><?= $phoneStatus ?></a></b></div></span>
    <?php endif; ?>
<?php endif; ?>

<div class="modal fade in" id="resendSmsModal" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="resendSmsTitle">Resend Sms</h4>
            </div>
            <div class="modal-body">
                <p class="message"><?= $text ?></p>
            </div>
            <div class="modal-footer">
                <a class="resendSmsBtnConfirm btn btn-success"><?= $btnText ?></a>
                <button id="buttonCancel" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="resendSmsModalNotAvailable" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="resendSmsTitle">Resend Sms</h4>
            </div>
            <div class="modal-body">
                <p class="message">You have not clicked to confirm the confirmation text we sent to <b id="phoneForNAModal"></b>. Please check for that text and click it now.
                        If you can’t find it, you will be able to re-send the text here in <b id="dayForReset"></b> days.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info" data-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>
