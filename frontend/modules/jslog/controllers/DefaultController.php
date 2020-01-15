<?php
namespace frontend\modules\jslog\controllers;

use Yii;
use frontend\modules\jslog\models\Error;
use yii\base\InvalidArgumentException;

/**
 * Class DefaultController
 * @package frontend\modules\jslog\controllers
 */
class DefaultController extends \yii\web\Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'add' => ['post'],
                ],
            ],
        ];
    }

//    public function beforeAction($action)
//    {
//        if ($action->id == 'add') {
//            $this->enableCsrfValidation = false;
//        }
//
//        return parent::beforeAction($action);
//    }


    /**
     *
     */
    public function actionAdd()
    {
        if (!Yii::$app->request->isAjax) {
           throw new \yii\base\InvalidCallException('Ajax requests only');
        }
        if (!Yii::$app->request->isExternalUrl() && Yii::$app->request->getReferrer()) {
            $model = new Error();
            if (!$model->load(Yii::$app->request->post(), '') || !$model->save()) {
                throw new InvalidArgumentException('Data validation error');
            }
        }
    }
}
