<?php

namespace common\components\rbac;

use yii\rbac\Rule;

/**
 * Class ManageAdminUsersRule
 * @package common\components\rbac
 */
class ManageAdminUsersRule extends Rule {

    /**
     * @var string
     */
    public $name = 'isAdmin';

    /**
     * @param int|string $userId
     * @param \yii\rbac\Item $item
     * @param array $params
     *
     * @return bool
     */
    public function execute($userId, $item, $params) {

        return is_null(\Yii::$app->authManager->getAssignment('admin', $userId)) ? false : true;
    }

}
