<?php

namespace scanroute\controllers;

use common\components\ScanComponent;
use common\models\Announcements;
use common\models\AnnouncementTypes;
use common\models\TokenAssociations;

// add these in or remove them when we don't need them.
//use yii\base\Exception;
//use yii\web\NotFoundHttpException;

/**
 * Scan controller for the `patient` module
 *
 * Job is to receive the token id hash of a scan in the field (of card or bracelet),
 * lookup the hash in the token_associations table, and then route to the correct
 * action page based on the result.  Main case is profile scan where we translate
 * the hash into the enrollment slid and route to profile server with that id.
 */
class ScanController extends \common\components\Controller
{

    /**
     * @param \yii\base\Action $action
     *
     * @return bool
     * @throws \Throwable
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\console\Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->availableInMaintenance = true;
        return parent::beforeAction($action);
    }

    /**
     * Routes the scan request to the correct destination based on token status in associations table
     *
     * @return string
     * @throws \Exception
     */
    public function actionIndex()
    {
        $model = TokenAssociations::findOne(['token_id' => \Yii::$app->scanComponent->event->slid_hash]);
        $cardSlid = $model?$model->token_slid:'';
        $cardType = ($model && $model->type)?$model->type->text:'card';

        $dateWidget = \common\components\widgets\LocalTime::widget([
            'datetime' => \Yii::$app->scanComponent->event->scantime,
            'format'   => 'm/d/y \a\t h:i A'
        ]);

        $text = "Your {$cardType} <span class='label label-info'>{$cardSlid}</span> was scanned on ".$dateWidget;

        Announcements::add(AnnouncementTypes::TYPE_CARD_SCAN_NOTIFICATION, $text, \Yii::$app->scanComponent->patient, [
            'postedBy' => false
        ]);
        return $this->redirect(\Yii::$app->scanComponent->generateProfileUrl());
    }

}
