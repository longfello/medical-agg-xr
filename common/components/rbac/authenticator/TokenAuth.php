<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.01.19
 * Time: 19:23
 */

namespace common\components\rbac\authenticator;


use common\components\rbac\authenticator\SubRoles\BaseRole;
use common\models\ApiAuthTokens;
use common\models\Log\AuthByTokenLog;
use common\models\Patient;

/**
 * Class tokenAuth
 * @package common\components\rbac\authenticator
 */
class TokenAuth extends BaseAuth
{
    /**
     * Name of GET-parameter for token
     */
    const PARAM = 'auth-token';
    /**
     * @inheritdoc
     * @throws \yii\base\Exception
     */
    public function createSignedUrl($patient, $role, $url = null){
        $token = ApiAuthTokens::create($patient->patients_id, $role->name);
        if (!$url){
            $url = $role->defaultPage;
        }
        return \Yii::$app->urlManagerFrontend->createAbsoluteUrl([$url, self::PARAM => $token]);
    }

    /**
     * @inheritdoc
     * @throws \Exception
     * @throws \Throwable
     */
    public static function auth()
    {
        if ($token = \Yii::$app->request->get(self::PARAM)){
            if ($model = ApiAuthTokens::findOne(['token' => $token])){
                $role = BaseRole::findByRoleName($model->role);
                if ($role) {
                    $log = new AuthByTokenLog();
                    $log->patient = $model->patient;
                    $log->content = "Remote access to {$role->description} has been granted as authenticated patient";
                    $log->save();

                    self::createSession($model->patients_id, $role);
                    $model->delete();
                    return true;
                }
            }
        }
        return false;
    }
}