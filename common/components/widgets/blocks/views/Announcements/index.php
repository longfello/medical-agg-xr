<?php
/**
 * @var $this \common\components\View
 * @var $list \common\models\Announcements[]
 * @var $limit int|false
 */

if (!$list) return;
?>

<div class="custom-panel panel-announcements">
  <div class="custom-panel-title bg-primary">
      <span class="panel-announcements-title">While you were away <span class="badge"><?= count($list) ?></span></span>
      <div id = "dismiss-button-wrapper">
        <a class="pull-right btn btn-danger btn-xs" id = "dismiss-button" href="<?= Yii::$app->urlManagerFrontend->createUrl(['/patient/announcements/dismiss-all']) ?>">dismiss all</a>
      </div>
  </div>
  <div class="custom-panel-content" id="while-you-were-away">
    <?php $i=0; foreach($list as $model){ ?>
        <?php $i++; if ($limit && $i>$limit) continue; ?>
        <?= $this->render('__item', ['model' => $model, 'limit' => $limit] ) ?>
    <?php } ?>

    <?php if ($limit && count($list) > $limit){ ?>
      <a href="<?= Yii::$app->urlManagerFrontend->createUrl(['/patient/announcements/all']) ?>" class="col-xs-12 btn btn-sm btn-default">Show All (see <?= count($list)-$limit ?> more)</a>
      <div class="clearfix"></div>
    <?php } ?>
  </div>
</div>

