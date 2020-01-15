<?php

namespace frontend\modules\api\controllers;

use common\models\SmsQueue;
use frontend\modules\api\components\Controller;

/**
 * Nexmo Callback Controller
 */
class NexmoController extends Controller
{
    /**
     * @var bool
     */
    public $enableCsrfValidation = false;
    /**
     * @var string
     */
    public $layout = '//ajax';

    /**
     * Finds sms in queue by id and process Nexmo API webhook call
     * @param $id
     *
     * @throws \Throwable
     */
    public function actionWebhook($id)
    {
        if ($model = SmsQueue::findOne(['id' => $id])) {
            $model->processWebhook(\Yii::$app->request->get());
        }
    }
}
