<?php

namespace console\controllers;

use Yii;

/**
 * Class RbacController
 * @package console\controllers
 */
class RbacController extends \console\components\Controller {

    /**
     * @throws \yii\base\Exception
     */
    public function actionInit() {
        $auth = Yii::$app->authManager;

        $auth->removeAll();

        $manageAdminHome = $auth->createPermission('adminHome');
        $manageAdminHome->description = 'Home page';
        $auth->add($manageAdminHome);

        $rule = new \common\components\rbac\ManageAdminUsersRule;
        $auth->add($rule);

        $manageAdminUsers = $auth->createPermission('adminUsers');
        $manageAdminUsers->description = 'Admin Users';
        $manageAdminUsers->ruleName = $rule->name;
        $auth->add($manageAdminUsers);

        $manageQueryUser = $auth->createPermission('queryUser');
        $manageQueryUser->description = 'Query User';
        $auth->add($manageQueryUser);

        $manageMaintenanceMode = $auth->createPermission('maintenanceMode');
        $manageMaintenanceMode->description = 'Maintenance Mode';
        $auth->add($manageMaintenanceMode);

        $managePractices = $auth->createPermission('managePractices');
        $managePractices->description = 'Manage Practices';
        $auth->add($managePractices);

        $manageParameters = $auth->createPermission('parameters');
        $manageParameters->description = 'Parameters';
        $auth->add($manageParameters);

        $testEmail = $auth->createPermission('testEmail');
        $testEmail->description = 'Test E-mail';
        $auth->add($testEmail);

        $subscriptionCoupons = $auth->createPermission('subscriptionCoupons');
        $subscriptionCoupons->description = 'Subscription Coupons';
        $auth->add($subscriptionCoupons);

        $manageSLIDs = $auth->createPermission('manageSLIDs');
        $manageSLIDs->description = "Manage SLID's";
        $auth->add($manageSLIDs);

        $menu = $auth->createPermission('menu');
        $menu->description = 'Admin menu';
        $auth->add($menu);
        $auth->addChild($menu, $manageAdminHome);
        $auth->addChild($menu, $manageAdminUsers);
        $auth->addChild($menu, $manageQueryUser);
        $auth->addChild($menu, $manageMaintenanceMode);
        $auth->addChild($menu, $managePractices);
        $auth->addChild($menu, $manageParameters);
        $auth->addChild($menu, $testEmail);
        $auth->addChild($menu, $subscriptionCoupons);
        $auth->addChild($menu, $manageSLIDs);

        $admin = $auth->createRole('admin');
        $admin->description = 'Full rights for everything';
        $auth->add($admin);
        $auth->addChild($admin, $manageAdminHome);
        $auth->addChild($admin, $manageAdminUsers);
        $auth->addChild($admin, $manageQueryUser);
        $auth->addChild($admin, $manageMaintenanceMode);
        $auth->addChild($admin, $managePractices);
        $auth->addChild($admin, $manageParameters);
        $auth->addChild($admin, $testEmail);
        $auth->addChild($admin, $subscriptionCoupons);
        $auth->addChild($admin, $manageSLIDs);

        $auth->assign($admin, 754);

        $moderator = $auth->createRole('Moderator');
        $moderator->description = 'Can add new entries and edit them';
        $auth->add($moderator);
        $auth->addChild($moderator, $manageAdminHome);

        $receptionist = $auth->createRole('Receptionist');
        $receptionist->description = 'Read only';
        $auth->add($receptionist);
        $auth->addChild($receptionist, $manageAdminHome);
    }

}
