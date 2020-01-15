<?php

/**
 * Base Controller for patient module
 */

namespace frontend\modules\RemoteAuthentication\components;

use common\models\Maintenance;
use yii\base\Action;
use yii\web\ForbiddenHttpException;

/**
 * Class Controller
 * @package frontend\modules\patient\components
 */
class Controller extends \common\components\Controller
{

    /**
     * @param Action $action
     *
     * @return bool|\yii\web\Response
     * @throws \Throwable
     */
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

}
