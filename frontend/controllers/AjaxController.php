<?php

namespace frontend\controllers;

use common\components\Helper;
use common\components\widgets\Forgot;
use common\models\Log;
use common\models\Patient;
use common\models\Practices;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Response;
use common\models\Log\ForgotPasswordLog;
use common\models\Patient as PatientModel;
use common\components\Controller;
use yii\web\HttpException;
use yii\validators\EmailValidator;
use common\models\RemotePendingPatients;

/**
 * Class AjaxController
 * @package frontend\controllers
 */
class AjaxController extends Controller
{
    /*** @var int $interval - time interval for attempts remind username, seconds ***/
    private $interval = 21600;

    /*** @var int $frequency - attemps per $interval***/
    private $frequency = 2;

    /**
     * @return string|array|Response
     * @throws HttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function actionForgotPassword()
    {
        $username = !Yii::$app->patient->isGuest ? Yii::$app->patient->username : \Yii::$app->request->post('username');
        $username = trim($username);
        $popup    = \Yii::$app->request->post('popup');
        $validate    = \Yii::$app->request->post('validate');
        $show_success = (\Yii::$app->request->post('show_success'))? 1 : 0;

        // PATIENT
        if(!empty($username)) {
            $patient = PatientModel::find()->where(['username' => $username])->one();
        } else {
            $patient = null;
        }

        /** @var Patient|null $patient */
        // CHECK USER CAN RESTORE PASSWORD (ACTIVE, HAS PASSWORD, COMPLETED REGISTRATION)
//        if($patient->status != PatientModel::STATUS_ACTIVE) {
//            return 'error';
//        }
//
        if($validate != 0) {
            $errorMessage = '';
            if (!$patient) {
                if ((new EmailValidator())->validate($username)) {
                    $errorMessage = 'There is no registed account with that username. Perhaps you entered email address rather than username';
                } else {
                    $errorMessage = 'There is no registed account with that username.';
                }
            } else {
                if ($patient->self_registered === 1 && $patient->status == PatientModel::STATUS_PENDING) {
                    $errorMessage = 'There is no registed account with that username. Perhaps you need to complete your registration';
                }
            }

            // IS ERROR MESSAGE
            if ($errorMessage) {
                if ($popup > 0) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'message' => $errorMessage,
                        'isError' => true
                    ];
                } elseif ($popup == 0) {
                    \Yii::$app->session->addFlash('loginMessage', '<ul><li>' . $errorMessage . '</li></ul>');
                    return $this->redirect(['/login'], 200);
                }
            }
        }
//
//        if(empty($patient->completed_registration_date)) {
//            return 'error';
//        }
        $isGuest = is_null($patient) ?? false;
        if (is_null($patient)) {
            $patient = new PatientModel();
            $patient->username = $username;
            $patient->email = ((new EmailValidator())->validate($username))?$username:'';
        }
        $way = \Yii::$app->request->post('way');

        if ($way == Forgot::INIT_WAY) {
            if (!$isGuest && empty($patient->cell_phone)) {
                $res = $this->forgotPasswordBehavior($patient, Forgot::EMAIL_WAY);
                $widget = Forgot::widget(['model' => $patient, 'way' => $res['way'], 'popup' => $popup, 'email' => $patient->email,'show_success' => $show_success]);
            } else {
                $widget = Forgot::widget(['model' => $patient, 'way' => Forgot::INIT_WAY, 'popup' => $popup, 'isPhoneButtonExists' => true,'show_success' => $show_success]);
            }
            return $widget;
        }

        if (is_null($patient->patients_id) && $way == Forgot::PHONE_WAY) {
            $res['hiddenPhone'] = '';
            $res['way'] = Forgot::PHONE_WAY;
        } elseif (is_null($patient->patients_id) && $way == Forgot::EMAIL_WAY) {
            $res['hiddenPhone'] = null;
            $res['email'] = $patient->email;
            $res['way'] = Forgot::EMAIL_WAY;
        } else {
            $res = $this->forgotPasswordBehavior($patient, $way);
        }

        if (isset($res['error']) && !empty($res['error'])) {
            (new ForgotPasswordLog(['current_patient' => $patient, 'content' => $res['error']]))->save();
            throw new HttpException(500, $res['error']);
        }

        if (!empty($res['blocked'])) {
            return (\Yii::$app->patient->isGuest ? 'reload page' : 'show notice');
        }

        if ($popup == 2) {
            \Yii::$app->session->addFlash('hiddenPhone', $res);
            if($show_success){
                Yii::$app->response->format = Response::FORMAT_JSON;
                $mess = '';
                switch($way){
                    case Forgot::PHONE_WAY:
                        $mess = 'Instructions for resetting your password have been texted to your cell phone, if a valid cell phone number is attached to the account you specified. If you do not receive an SMS, then try again, or request an email, or <a target="_blank" href="/account-support">contact support</a>.';
                        break;
                    case Forgot::EMAIL_WAY:
                        $mess = 'Instructions for resetting your password have been sent to your email address, if a valid email address number is attached to the account you specified. If you do not receive an email, then try again, or request a sms, or <a target="_blank" href="/account-support">contact support</a>.';
                        break;
                }
                return [
                    'message' => $mess,
                    'isError' => false
                ];
            }else{
                // Set code = 200 because IE processing not property the AJAX redirects
                // See https://github.com/yiisoft/yii2/issues/9670
                return $this->redirect(['/forgot-password'], 200);
            }

        }

        $widget = Forgot::widget([
            'model' => $patient,
            'way' => $res['way'],
            'popup' => $popup,
            'hiddenPhone' => $res['hiddenPhone'],
            'email' => isset($res['email']) ? $res['email']:$patient->email,
            'show_success' => $show_success
        ]);
        return $widget;
    }

    /***
     * @param int $show_error
     * @return array|string|Response
     * @throws \Throwable
     */
    public function actionForgotUsername($show_error = 1){

        $way = \Yii::$app->request->post('way');
        $popup    = \Yii::$app->request->post('popup');
        $username = \Yii::$app->request->post('username');
        $username = trim($username);
        $show_error = ($popup == 1)? 0 : 1;
        $show_success = (\Yii::$app->request->post('show_success'))? 1 : 0;

        /***CHECK INPUT TYPE***/
        if( (new EmailValidator())->validate($username) ){
            $patient = PatientModel::find()->where(['email' => $username])->all();
            $transport_type='email';
        } else {
            $patient = Helper::findPatientsByPhone($username);
            $transport_type='phone number';
        }
        $count = count($patient);
        $errorMessage = '';

        if ($count == 1) {
            if($patient[0]->status == PatientModel::STATUS_PENDING) {
                $errorMessage = 'There is no registed account with that username. Perhaps you need to complete your registration';
            } elseif (Log::find()
                ->where(['internal_id'=>$patient[0]->internal_id,'log_type'=>Log::TYPE_FORGOT_USERNAME_ATTEMPT,])
                ->andWhere(['>','log_updated',date('Y-m-d H:i:s', time() - $this->interval)])
                ->count() > $this->frequency ) {
                $errorMessage = 'Attempt count limit. Try again later';
            }elseif($count == 0) {
                $errorMessage = 'There is no registed account with that ' . $transport_type;
            }
        } elseif ($count > 1) {
            $errorMessage = "There is more than one account with this {$transport_type}. <a href='".Url::to(['/account-support'])."'>Contact support</a> please.";
        } else {
            $errorMessage = "There are no matching accounts with this {$transport_type}. <a href='".Url::to(['/account-support'])."'>Contact support</a> please.";
        }

        // IS ERROR MESSAGE
        if($errorMessage && $show_error!=0) {
            if ($popup > 0) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'message' => $errorMessage,
                    'isError' => true
                ];
            }
            else {
                \Yii::$app->session->addFlash('loginMessage', '<ul><li>' . $errorMessage . '</li></ul>');
                return $this->redirect(['/login'], 200);
            }
        } elseif ($count == 0 && $show_error!=0) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'message' => $errorMessage,
                'isError' => true
            ];
        } elseif($count == 0 ) {
            $patient[0] = new PatientModel();
        }

        $res = $this->forgotUsernameBehavior($patient[0], $way);

        if (!empty($res['blocked'])) {
            return (\Yii::$app->patient->isGuest ? 'reload page' : 'show notice');
        }
        if ($popup == 2) {
            \Yii::$app->session->addFlash('hiddenPhone', $res);
            if($show_success){
                Yii::$app->response->format = Response::FORMAT_JSON;
                $mess = '';
                switch($way){
                    case Forgot::USERNAME_PHONE_WAY:
                        $mess = 'Username reminder have been texted to your cell phone, if a valid cell phone number is attached to the account you specified. If you do not receive an SMS, then try again, or request an email, or <a target="_blank" href="/account-support">contact support</a>.';
                        break;
                    case Forgot::USERNAME_EMAIL_WAY:
                        $mess = 'Username reminder have been sent to your email address, if a valid email address number is attached to the account you specified. If you do not receive an email, then try again, or request a sms, or <a target="_blank" href="/account-support">contact support</a>.';
                        break;
                }
                return [
                    'message' => $mess,
                    'isError' => false
                ];
            }else{
                return $this->redirect( [ '/forgot-username' ], 200 );
            }
        }

        $patient[0]->username = $username;
        $widget = Forgot::widget([
            'model' => $patient[0],
            'way' => $res['way'],
            'popup' => $popup,
            'hiddenPhone' => $res['hiddenPhone'],
            'email' => isset($res['email']) ? $res['email'] : $patient[0]->email,
            'show_success' => $show_success
        ]);
        return $widget;
    }

    /**
     * send code confirm to cell phone remote pending patient
     * @return string
     * @throws HttpException
     */
    public function actionSendCode(){
        $billing_id = \Yii::$app->request->post('billing_id');
        $practice_umr_id = \Yii::$app->request->post('practice_umr_id');
        $remotePatient = RemotePendingPatients::findOne(['billing_id' => $billing_id]);
        $practice = Practices::findOne(['practice_umr_id' => $practice_umr_id]);
        $res = false;
        $mess = ['Code not sent'];

        if ($remotePatient->checkSend()) {
            $res = $remotePatient->sendCode();
            if($res){
                $mess = ['New code sent'];

                $type = Log::TYPE_REMOTE_PENDING_PATIENT_SEND_CODE;
                $content = [];
                $content['cell_phone'] = $remotePatient->cell_phone;
                $content['sms_confirmation_codes_sent'] = $remotePatient->sms_confirmation_codes_sent;
                $content['sms_confirmation_code'] = $remotePatient->sms_confirmation_code;
                $content['trigger'] = \Yii::$app->request->getUserIP();

                $pendingLog = new Log\RemotePendingPatientLog([
                    'internal_id' => $billing_id,
                    'practice_id' => $practice->practice_id,
                    'patients_id' => 0,
                    'type' => $type,
                    'content' => $content,
                ]);
                $pendingLog->save();
            }
            else{
                $remotePatient->getErrorSummary(true);
            }
        }

        return Json::encode(['OK'=>$res, 'mess' => $mess]);
    }

}
