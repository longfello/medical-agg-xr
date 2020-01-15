<?php
/**
 * @var $this \common\components\View
 * @var $list \common\models\Announcements[]
 * @var $limit int|false
 */

?>

<div class="custom-panel panel-announcements-preview">
  <div class="cm-text panel-announcements-preview-title-wrapper">
      <a href="<?= Yii::$app->urlManagerFrontend->createUrl(['/patient/announcements/all']) ?>" class="panel-announcements-preview-title">Important Notifications: <?= $count ?></a>
  </div>
  <div class="cm-text panel-announcements-preview-content-wrapper">
      <a href="<?= Yii::$app->urlManagerFrontend->createUrl(['/patient/announcements/all']) ?>" class="panel-announcements-preview-content">click to read your messages</a>
  </div>
</div>

