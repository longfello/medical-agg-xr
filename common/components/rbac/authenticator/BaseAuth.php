<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.01.19
 * Time: 19:23
 */

namespace common\components\rbac\authenticator;


use common\components\Cookie;
use common\components\rbac\authenticator\SubRoles\BaseRole;
use common\models\LifeSessions;
use common\models\Patient;
use yii\base\BaseObject;
use yii\db\Expression;

/**
 * Class prototype
 * @package common\components\rbac\authenticator
 */
abstract class BaseAuth extends BaseObject
{
    /**
     *  Create signed url for auth patient with given sub-role to $url or default sub-role page
     * @param Patient $patient
     * @param BaseRole $role
     * @param string|null $url default sub-role page if null
     *
     * @return string
     */
    abstract public function createSignedUrl($patient, $role, $url = null);

    /**
     * Try to authentificate user
     * @return bool
     */
    abstract public static function auth();

    /**
     * @param int $patient_id
     * @param BaseRole $role
     * @throws \Exception
     */
    protected static function createSession($patient_id, $role){
        $session = new LifeSessions();
        $session->patient_id = $patient_id;
        $session->last_updated = new Expression('NOW()');
        $session->session_id = LifeSessions::generateSessionID();
        $session->type = LifeSessions::TYPE_API;
        $session->role = $role->name;
        $session->save();
        $cookie = new Cookie([
            'name' => \frontend\modules\patient\components\Patient::COOKIE_PARAM,
            'value' => $session->session_id,
            'expire' => time() + 86400,
        ]);
        if (getenv('COOKIE_DOMAIN') != 'localhost') {
            $cookie->domain = getenv('COOKIE_DOMAIN');
        }
        \Yii::$app->response->cookies->add($cookie);
    }
}