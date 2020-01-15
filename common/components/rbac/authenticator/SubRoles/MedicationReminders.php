<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.01.19
 * Time: 19:41
 */

namespace common\components\rbac\authenticator\SubRoles;


use frontend\assets\ApiMedicationReminders;
use yii\helpers\Inflector;

/**
 * Class MedicationReminders
 * @package common\components\rbac\authenticator\SubRoles
 */
class MedicationReminders extends BaseRole
{
    /**
     * Array of enabled actions module->controller->action
     */
    public $enabledActions = [
        'patient' => [
            'default' => [
                'record' => true,
                'record@medication-reminders' => true,
                'update-tz' => true
            ],
            'med-info' => [
                'load' => true,
                'load@medication-reminders' => true,
                'block' => true,
                'block@medication-reminders' => true
            ],
        ],
    ];

    /**
     * @inheritdoc
     */
    public static $name = 'medication-reminders';

    /**
     * @inheritdoc
     */
    public $description = 'medication reminders page';

    /**
     * @inheritdoc
     */
    public $defaultPage = '/subscriber-home/record/medication-reminders';

    /**
     * @inheritdoc
     */
    public $allowTZAutoCorrection = false;

    /**
     * @inheritdoc
     */
    public function registerAssets(){
        $view = \Yii::$app->view;
        ApiMedicationReminders::register($view);
    }
}