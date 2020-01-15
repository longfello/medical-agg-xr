<?php
/**
 * @var $this \yii\web\View
 * @var $content string
 */
?>

<?php $this->beginContent('@app/views/layouts/main.php') ?>
<div class="content">
    <div class="admin-content-wrapper">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><?= $content ?></div>
        <div class="clearfix"></div>
    </div>
</div>
<?php $this->endContent() ?>

