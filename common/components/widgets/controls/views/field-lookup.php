<?php
/** @param $this \yii\web\View */
/** @param $text */

?>

<div class="block-field-text">
    <div class="model-attribute-text"><?= $text ?></div>
    <div class="model-attribute-text-hide">
        <div class="see-more-btn"
             title="<?= $text ?>"
             data-toggle="popover"
             data-placement="top">See more</div>
    </div>
</div>
