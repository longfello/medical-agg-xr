<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 12.09.18
 * Time: 21:54
 */

namespace common\components\LogRotation;


use common\models\Settings;

/**
 * Class LogRotation
 * @package common\components
 */
class LogRotation
{
    /**
     * LogRotation constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Rotation entities objects
     * @var LogRotationEntity
     */
    public $rotationEntities;

    /**
     * Initializes Rotation entities
     */
    public function init()
    {
        $this->rotationEntities = $this->initRotationEntities();
    }

    /**
     * Init rotation entities
     *
     * @return array
     */
    private function initRotationEntities()
    {
        $logRotationSetting = Settings::get(Settings::LOG_ROTATION_SETTING);
        $rotationEntities = [];

        if (!empty($logRotationSetting) && is_array($logRotationSetting)) {
            foreach ($logRotationSetting as $setting) {
                $rotationEntity = new LogRotationEntity($setting['table']);
                $rotationEntity->dateField = $setting['date_field'];
                $rotationEntity->rowsLimit = $setting['rows_limit'];
                $rotationEntity->daysLimit = $setting['days_limit'];;
                $rotationEntity->leaveRows = $setting['leave_rows'];
                $rotationEntities[] = $rotationEntity;
            }
        }

        return $rotationEntities;
    }
}