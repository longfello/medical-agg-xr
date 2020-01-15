<?php

namespace console\components;

use common\models\Maintenance;

/**
 * Class Controller
 * @package console\components
 */
class Controller extends \yii\console\Controller
{

    /**
     * @param $action
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function beforeAction($action)
    {
        if(Maintenance::isActiveAny()) {
            return false;
        }

        return true;
    }
}
