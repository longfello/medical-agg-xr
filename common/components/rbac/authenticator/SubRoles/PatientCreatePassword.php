<?php
/**
 * Created by dmytro mytsko
 * Date: 29.01.19
 * Time: 19:41
 */

namespace common\components\rbac\authenticator\SubRoles;


/**
 * Class PatientCreatePassword
 * @package common\components\rbac\authenticator\SubRoles
 */
class PatientCreatePassword extends BaseRole
{
    /**
     * Array of enabled actions module->controller->action
     */
    public $enabledActions = [
        'patient' => [
            'menu' => [
                'show-small' => true,
            ],
            'password' => [
                'create' => true,
            ],
            'default' => [
                'update-tz' => true,
            ]
        ]
    ];

    /**
     * @inheritdoc
     */
    public static $name = 'patient_create_password';

    /**
     * @inheritdoc
     */
    public $description = 'create password';

    /**
     * @inheritdoc
     */
    public $defaultPage = '/terms-read';
}