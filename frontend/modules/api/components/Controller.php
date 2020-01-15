<?php

namespace frontend\modules\api\components;

use common\models\Maintenance;

/**
 * Class Controller
 * @package frontend\modules\api\components
 */
class Controller extends \common\components\Controller
{
    /**
     * @param $action
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
        if (!$this->availableInMaintenance && Maintenance::isActiveAny()) {
            \Yii::$app->isMaintenance = true;
            \Yii::$app->isDbFree = false;
            return false;
        }

        return parent::beforeAction($action);
    }
}
