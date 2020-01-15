<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.01.19
 * Time: 19:31
 */

namespace common\components\rbac\authenticator\SubRoles;


use yii\base\Action;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * Class prototype
 * @package common\components\rbac\authenticator\SubRoles
 */
class BaseRole extends BaseObject
{
    /**
     * @const true
     */
    const ALL_ACTIONS_ENABLED = true;

    /**
     * Array of enabled actions module->controller->action
     * @var array|true
     */
    public $enabledActions = self::ALL_ACTIONS_ENABLED;

    /**
     * Name for sub-role
     * @var string
     */
    public static $name;
    /**
     * Description for sub-role
     * @var string
     */
    public $description;

    /**
     * Default home page for sub-role
     * @var string
     */
    public $defaultPage = '/';

    /**
     * Allow browser-based TZ auto correction
     * @var boolean
     */
    public $allowTZAutoCorrection = true;

    /**
     * Find and return sub-role class by given name
     * @param string $name
     * @return false|BaseRole
     */
    public static function findByRoleName($name)
    {
        $className = __NAMESPACE__ . '\\' . Inflector::camelize($name);
        if (class_exists($className)){
            return new $className;
        }
        return false;
    }

    /**
     * Register assets specific for current role
     */
    public function registerAssets(){}

    /**
     * Return is action enabled by sub-role
     * @param Action|string $action
     *
     * @return bool
     */
    public function isEnabledAction($action)
    {
        if ($this->enabledActions === self::ALL_ACTIONS_ENABLED) return true;

        $enabled = false;

        switch (true) {
            case ($action instanceof Action):
                if (isset($this->enabledActions[$action->controller->module->id][$action->controller->id][$action->id])){
                    $enabled = true;
                }
                break;
            case (is_string($action) && $action):
                $action = str_replace('/','.', $action);
                $enabled = ArrayHelper::getValue($this->enabledActions, $action, false);
                break;
        }

        return $enabled;
    }

    /**
     * Set redirect url after success authenticate
     */
    public function processSessionCreate()
    {
        if(!empty(\Yii::$app->session)) {
            \Yii::$app->session->set('loginUrl', $this->defaultPage);
        }
    }

    public function getName()
    {
       return static::$name;
    }

}