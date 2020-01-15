<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 07.06.18
 * Time: 15:45
 */

namespace common\components\PerfectParser\Common\Actions\Methods;

use common\components\PerfectParser\Common\Prototype\RestActionMethod;
use common\components\rbac\authenticator\SubRoles\BaseRole;
use common\components\rbac\authenticator\TokenAuth;
use common\models\ApiAuthTokens;
use common\models\Log\AuthByTokenLog;
use common\models\Patient;
use common\models\PatientInfo;
use common\models\Practices;
use Yii;
use yii\helpers\VarDumper;
use yii\web\Response;


/**
 * Class MedInfo
 * @package common\components\PerfectParser
 */
class Frame extends RestActionMethod
{
    /** Inheritdoc */
    public static $name = "returns a link to the requested page with restrictions for access as if it was authenticated user";

    /** @inheritdoc */
    public $format = Response::FORMAT_JSON;

    /** @inheritdoc */
    public $defaultFormat = Response::FORMAT_JSON;

    /**
     * @var Practices|false Practice
     */
    private $practice;

    /** @inheritdoc */
    public function beforeRun(){
        // Auth
        if (!$this->practice = Yii::$app->perfectParser->dataSource->auth()){
            header("WWW-Authenticate: " .
                "Basic realm=\"Enter practice credentials\"");
            header("HTTP/1.0 401 Unauthorized");
            print("Restricted Area");
            die();

        }
        return true;
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     */
     protected function run(){
         if ($this->practice) {
             if ($internal_id = strtoupper(Yii::$app->request->get('patient_id'))) {
                 if ($token = Yii::$app->request->get('session_id')) {
                     if ($page = Yii::$app->request->get('page')) {
                         $patientInfo = PatientInfo::find()->where([
                             'internal_id'   => $internal_id,
                             'umr_token'   => $token,
                             'practice_id' => $this->practice->practice_id,
                         ])->one();
                         /** @var PatientInfo $patientInfo */
                         if ($patientInfo && $patientInfo->patient) {
                             $patient = $patientInfo->patient;

                             $role = BaseRole::findByRoleName($page);
                             if ($role) {
                                 $log = new AuthByTokenLog();
                                 $log->patient = $patient;
                                 $log->content = "Practice {$this->practice->practice_name} (id = {$this->practice->practice_id}) requested remote access to {$role->description} as authenticated patient using token";
                                 $log->save();
                                 $authentificator = new TokenAuth();
                                 return [
                                     'result' => true,
                                     'url'    => $authentificator->createSignedUrl($patient, $role)
                                 ];
                             } else $this->throwError("Page not found.", 6, ['criteria' => ['page' => $page]]);
                         } else $this->throwError("Patient not found.", 5, ['criteria' => ['umr_token' => $token, 'practice_username' => $this->practice->auth_user, 'patient_id' => $internal_id]]);
                     } else $this->throwError("Missing Page Param.", 4);
                 } else $this->throwError("Missing Patient's CommLifeToken Param.", 3);
             } else $this->throwError("Missing Patient's SLID Param.", 2);
         } else $this->throwError("Bad practice's credentials given.", 1);
    }
}