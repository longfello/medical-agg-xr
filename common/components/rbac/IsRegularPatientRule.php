<?php

namespace common\components\rbac;

use yii\rbac\Rule;

/**
 * Class ManageAdminUsersRule
 * @package common\components\rbac
 */
class IsRegularPatientRule extends Rule {

    /**
     * @var string
     */
    public $name = 'isRegularPatient';

    /**
     * @param int|string $userId
     * @param \yii\rbac\Item $item
     * @param array $params
     *
     * @return bool
     */
    public function execute($userId, $item, $params) {
        return \Yii::$app->patient->isRegularPatient();
    }

}
