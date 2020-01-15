<?php

/* @var $this \yii\web\View */

/* @var $content string */

use yii\helpers\Html;
use frontend\assets\AppAsset;

AppAsset::register($this);
unset(Yii::$app->log->targets['debug']);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <title><?= Html::encode($this->title) ?></title>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <?= Html::csrfMetaTags() ?>
        <?php $this->head() ?>
    </head>
    <body class="ca-<?= Yii::$app->controller->id ?>-<?= Yii::$app->controller->action->id ?> patient-<?= Yii::$app->patient->isGuest ? 'guest' : 'registred' ?>"
          style="min-width: 100%;">
    <?php $this->beginBody() ?>
    <div class="container">
        <?= $content ?>
    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>