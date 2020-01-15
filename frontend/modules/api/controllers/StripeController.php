<?php

namespace frontend\modules\api\controllers;

use yii\web\HttpException;
use yii\web\Response;
use frontend\modules\api\components\Controller;

/**
 * Default controller for the `admin` module
 */
class StripeController extends Controller
{
    /** @const Ok signature */
    const OK = 'Ok';

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;
    /**
     * @inheritdoc
     */
    public $layout = '//ajax';

    /**
     * Renders the index view for the module
     * @return string
     * @throws HttpException
     */
    public function actionWebhook()
    {
        \Yii::$app->response->format = Response::FORMAT_RAW;
        $verify = filter_var(env('STRIPE_SIGNATURE_CHECK', null, true), FILTER_VALIDATE_BOOLEAN);
        \Yii::$app->stripe->processEvent($verify);
        if (\Yii::$app->stripe->isError()) {
            $errors = implode("\r\n\.", \Yii::$app->stripe->getErrors());
            echo $errors;
            throw new HttpException(400, $errors);
        }
        return self::OK;
    }
}
