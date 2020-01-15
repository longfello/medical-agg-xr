<?php

namespace common\components\rbac;

use yii\rbac\Rule;

/**
 * Class ManageAdminUsersRule
 * @package common\components\rbac
 */
class IsApiAuthRule extends Rule {

    /**
     * @var string
     */
    public $name = 'IsApiAuth';

    /**
     * @param int|string $userId
     * @param \yii\rbac\Item $item
     * @param array $params
     *
     * @return bool
     */
    public function execute($userId, $item, $params) {
        $role = isset($params['role'])?$params['role']:false;
        if ($role) {
            return \Yii::$app->patient->IsApiAuth($role);
        } else {
            return false;
        }
    }

}
