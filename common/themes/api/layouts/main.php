<?php
/* @var $this \common\components\View */
/* @var $content string */

use common\helpers\Layout;
use yii\helpers\Html;
use frontend\assets\AppAsset;
use common\models\Patient;
use common\models\Maintenance;
use frontend\modules\patient\components\widgets\QuicklinkWidget;
use yii\widgets\Spaceless;

AppAsset::register($this);

$this->title = Layout::addPrefixToTitle($this->title);

$dataTZ = '';
if (!Yii::$app->patient->isGuest){
  if (Yii::$app->patient->model->tz_auto){
      $dataTZ =  "data-tz='".Yii::$app->patient->model->tz."'";
  }
}

$photo = '';
if (!Yii::$app->patient->isGuest) {
    $photo = Yii::$app->patient->model->getPhoto();
    if ($photo == Patient::PHOTO_ERROR || $photo == Patient::PHOTO_EMPTY) {
        $photo = $this->getAppAssetUrl(Patient::PHOTO_EMPTY);
    }
}

$this->registerJsVar('loader_big', $this->getAppAssetUrl('img/preloader_big.gif'));
$this->registerJsVar('loader_default', $this->getAppAssetUrl('img/preloader.gif'));
$this->registerJsVar('test_svg', $this->getAppAssetUrl('js/flash/jscam_canvas_only.swf'));

if ($role = Yii::$app->patient->getSubRole()) {
    $role->registerAssets();
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <title><?= Html::encode($this->title) ?></title>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="theme-color" content="#ffffff">

        <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
        <link rel="manifest" href="/manifest.json">
        <link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#5bbad5">
        <style>body {display: none;}</style>
        <?= Html::csrfMetaTags() ?>
        <?php $this->head() ?>
    </head>
    <body class="ca-<?= Yii::$app->controller->id ?>-<?= Yii::$app->controller->action->id ?> patient-<?= (Yii::$app->id == 'enroll' || Yii::$app->patient->isGuest) ? 'guest' : 'registred' ?> <?= (Yii::$app->id == 'enroll' && !Yii::$app->enroller->isGuest) ? 'enroller-registered' : '' ?><?= \Yii::$app->devicedetect->isMobile()?"mobile":"" ?> <?= \Yii::$app->devicedetect->isTablet()?"tablet":"" ?>" <?= $dataTZ ?>>

    <?php if ($warningText = \Yii::$app->session->getFlash('browser-warning')): ?>
        <div class="old-browser-warning">
            <div class="content">
                <div class="col-md-12">
                    <a class="pull-right close-icon js-ajaxify" href="/hide-warning" onclick="$('.old-browser-warning').hide();" data-without-response="1" data-without-message="1" ><i class="fa fa-times"></i></a>
                    <p><strong>Warning - Unsupported Browser Version!</strong></p>
                    <p><?php echo $warningText ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <noscript>
        <style type="text/css">
            /** hide all content? */
        </style>
        <div class="old-browser-warning">
            <div class="content">
                <div class="col-md-12">
                    <p><strong>Javascript disabled!</strong></p>
                    <p>You disabled JavaScript in your browser, thus we can't use cookies which are required for login</p>
                </div>
            </div>
        </div>
    </noscript>

        <?php $this->beginBody() ?>
        <?= $content ?>

        <div id="messager-system-wrapper"></div>
        <input type="hidden" id="refresh" value="no">
        <?php $this->endBody() ?>
    </body>
    <?=$this->render('web-analytics');?>
</html>
<?php $this->endPage() ?>