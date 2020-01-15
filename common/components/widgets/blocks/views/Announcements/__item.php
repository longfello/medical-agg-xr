<?php
/**
 * @var $this \common\components\View
 * @var $model \common\models\Announcements
 * @var $limit int|false
 */
$id = 'ann'.md5($model->announce_id);
?>

<div class="item item-<?= $model->type->type ?>" id="<?= $id ?>">
  <div class="alert alert-<?= $model->type->context_type ?> alert-dismissible" role="alert">
    <a type="button" class="close js-ajaxify" aria-label="Close" data-without-message='1' data-target=".panel-announcements" href="<?= Yii::$app->urlManagerFrontend->createUrl(['/patient/announcements/dismiss', 'id' => $model->announce_id, 'limit' => $limit]) ?>"><span aria-hidden="true">&times;</span></a>
    <i class="<?= $model->getIconClasses(); ?>"></i>
      <?= $model->getContent(); ?>
  </div>
</div>