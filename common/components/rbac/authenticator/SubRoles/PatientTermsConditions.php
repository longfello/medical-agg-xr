<?php
/**
 * Created by dmytro mytsko
 * Date: 29.01.19
 * Time: 19:41
 */

namespace common\components\rbac\authenticator\SubRoles;


/**
 * Class PatientTermsConditions
 * @package common\components\rbac\authenticator\SubRoles
 */
class PatientTermsConditions extends BaseRole
{
    /**
     * Array of enabled actions module->controller->action
     */
    public $enabledActions = [
        'patient' => [
            'menu' => [
                'show-small' => true
            ],
            'terms-conditions' => [
                'show' => true
            ]
        ]
    ];

    /**
     * @inheritdoc
     */
    public static $name = 'patient_terms_conditions';

    /**
     * @inheritdoc
     */
    public $description = 'terms and conditions page';

    /**
     * @inheritdoc
     */
    public $defaultPage = '/terms-read';
}