<?php


namespace frontend\modules\RemoteAuthentication\controllers;


use BaconQrCode\Renderer\Text\Html;
use common\components\NotificationManager\channels\Email\Email;
use common\components\NotificationManager\messages\RemoteSignupIDMismatch;
use common\models\Log;
use common\models\PatientInfo;
use common\models\Practices;
use common\models\RemotePendingPatients;
use common\models\Settings;
use common\models\StaticContent;
use common\models\TokenAssociations;
use common\models\TokenTypes;
use frontend\modules\patient\forms\SignInForm;
use frontend\modules\RemoteAuthentication\components\Controller;
use frontend\modules\RemoteAuthentication\form\PendingPatients;
use yii\db\Expression;
use common\models\Patient;
use yii\helpers\Url;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use common\models\SlidLookup;
use common\models\Log\RemotePendingPatientLog;

/**
 * Class AuthController
 * @package frontend\modules\RemoteAuthentication
 */
class AuthController extends Controller
{

    /**
     * 2.4 SLID-2326 Remote Authentication: Receive request from url to attach particular patient to records with particular doctor
     * https://trello.com/c/FTxj8fxY
     * @param $practice_umr_id
     * @param $billing_id
     * @return string
     * @throws HttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionIndex($practice_umr_id, $billing_id)
    {
        $practice = Practices::findOne(['practice_umr_id' => $practice_umr_id]);
        $model = new PendingPatients();
        $loginForm = new \common\models\LoginForm();
        $step = 1;
        $success = null;
        $error4create =
        $error4login =
        $error4use = '';
        if ($practice) {
            if ($practice->remote_authentication_active) {
                $remotePatient = RemotePendingPatients::findOne(['billing_id' => $billing_id, 'practice_id' => $practice->practice_id]);
                if (!$remotePatient) {
                    throw new HttpException(200, "Unknown patient. Please contact <a href='/subscriber-home/account-support'>support</a> for more assistance");
                } else if ($remotePatient->slid) {
                    throw new HttpException(200, "Connection link expired. Please contact <a href='/subscriber-home/account-support'>support</a> for more assistance");
                }

                if (\Yii::$app->request->isPost) {
                    switch (false) {
                        case (is_null(\Yii::$app->request->post('step-1'))):
                            if ($model->load(\Yii::$app->request->post())) {
                                if ($remotePatient->checkSend()) {
                                    if ($model->checkStep1($remotePatient)) {
                                        $step++;
                                        $success = true;

                                        $type = Log::TYPE_REMOTE_PENDING_PATIENT_SEND_CODE;
                                        $content = [];
                                        $content['cell_phone'] = $remotePatient->cell_phone;
                                        $content['sms_confirmation_codes_sent'] = $remotePatient->sms_confirmation_codes_sent;
                                        $content['sms_confirmation_code'] = $remotePatient->sms_confirmation_code;
                                        $content['trigger'] = \Yii::$app->request->getUserIP();

                                        $pendingLog = new RemotePendingPatientLog([
                                            'internal_id' => $billing_id,
                                            'practice_id' => $practice->practice_id,
                                            'patients_id' => 0,
                                            'type' => $type,
                                            'content' => $content
                                        ]);
                                        $pendingLog->save();
                                    } else {
                                        $success = false;
                                    }
                                } else {
                                    $success = false;
                                    $model->addError('code', 'Exceeded sending limit');
                                }

                            }

                            if ($success) {
                                $type = Log::TYPE_REMOTE_PENDING_PATIENT;
                                $content = null;
                            } else {
                                $type = Log::TYPE_REMOTE_PENDING_PATIENT_FAIL;
                                $content = (\Yii::$app->request->post('PendingPatients')) ? \Yii::$app->request->post('PendingPatients') : [];
                                $content['num_failed_attempts'] = $remotePatient->num_failed_attempts;
                            }
                            $pendingLog = new RemotePendingPatientLog([
                                'internal_id' => $billing_id,
                                'practice_id' => $practice->practice_id,
                                'patients_id' => 0,
                                'type' => $type,
                                'content' => $content
                            ]);
                            $pendingLog->save();
                            break;
                        case (is_null(\Yii::$app->request->post('step-2'))):
                            $step = 2;
                            if ($model->load(\Yii::$app->request->post())) {
                                if ($model->checkStep2($remotePatient)) {
                                    $step++;
                                    $success = true;
                                } else {
                                    $success = false;
                                }
                            } else {
                                $success = false;
                            }

                            $content = [];
                            $content['who'] = \Yii::$app->request->getUserIP();
                            $content['confirmation_code'] = $model->sms_confirmation_code;
                            if ($success) {
                                $type = Log::TYPE_REMOTE_PENDING_PATIENT_CODE_CORRECT;
                            } else {

                                $pendingPatientsPost = \Yii::$app->request->post('PendingPatients');
                                $content['sms_confirmation_code_guess_fails'] = $remotePatient->sms_confirmation_code_guess_fails;
                                $content['sms_confirmation_code_guess_fails_all'] = $remotePatient->sms_confirmation_code_guess_fails_all;
                                $content['confirmation_code'] = (isset($pendingPatientsPost['sms_confirmation_code']))? $pendingPatientsPost['sms_confirmation_code'] : '';
                                $content['who'] = \Yii::$app->request->getUserIP();
                                $content['fails_this_code'] = $remotePatient->sms_confirmation_code_guess_fails;
                                $content['fails_all'] = $remotePatient->sms_confirmation_code_guess_fails_all;
                                $type = Log::TYPE_REMOTE_PENDING_PATIENT_CODE_INCORRECT;
                            }
                            $pendingLog = new RemotePendingPatientLog([
                                'internal_id' => $billing_id,
                                'practice_id' => $practice->practice_id,
                                'patients_id' => 0,
                                'type' => $type,
                                'content' => $content
                            ]);
                            $pendingLog->save();
                            break;
                        case (is_null(\Yii::$app->request->post('step-3'))):
                        case (is_null(\Yii::$app->request->post('not-step-3'))):

                            $redirect = (\Yii::$app->request->post('not-step-3')===null)? false : true;
                            $step = 4;

                            if($redirect){
                                $content = [];
                                $content['who'] = \Yii::$app->request->getUserIP();
                                $content['first_name'] = $remotePatient->first_name;
                                $content['last_name'] = $remotePatient->last_name;
                                $type = Log::TYPE_REMOTE_PENDING_PATIENT_NAME_CHECK;
                                $pendingLog = new RemotePendingPatientLog([
                                    'internal_id' => $billing_id,
                                    'practice_id' => $practice->practice_id,
                                    'patients_id' => 0,
                                    'type' => $type,
                                    'content' => $content
                                ]);
                                $pendingLog->save();

                                $emails = Settings::findOne(['key' => Settings::REMOTE_SIGNUP_ID_MISMATCH_EMAIL])->value;
                                $url = Url::base(true).\Yii::$app->request->url;

                                foreach (explode(',', $emails) as $email) {
                                    $mail = new RemoteSignupIDMismatch(['remoteUrl' => $url]);
                                    $mail->send(trim($email), false, Email::getID());
                                }

                                $this->redirect('/');
                            }
                            break;
                        case (is_null(\Yii::$app->request->post('step-4'))):
                            $step = 4;
                            $url = '/connect/practices/' . $practice_umr_id . '/' . $billing_id . '/terms-of-use';
                            $type = Log::TYPE_REMOTE_PENDING_PATIENT_ACCOUNT_SELECT;
                            $content = [];
                            $content['who'] = \Yii::$app->request->getUserIP();
                            $content['billing_id'] = $billing_id;

                            switch (\Yii::$app->request->post('from-scenario')) {
                                case 'use-current':
                                    if (!\Yii::$app->patient->isGuest) {

                                        if ($patientInfo = \Yii::$app->patient->model->getPatientInfo($practice->practice_id, false)->one()){
                                            /** @var PatientInfo $patientInfo */
                                            $internalID = $patientInfo->internal_id;
                                        } else {
                                            $slid = SlidLookup::findUnusedSlid(true);
                                            $slid->generateSlidHash();
                                            $slid->type = TokenTypes::TYPE_STARTED;
                                            $slid->save();

                                            TokenAssociations::createAssociation($slid, \Yii::$app->patient->model);

                                            $patientInfo = \Yii::$app->patient->model->getPatientInfo($practice->practice_id)->one();

                                            $patientInfo->gender = $remotePatient->gender;
                                            $patientInfo->date_of_birth = $remotePatient->date_of_birth;
                                            if (!$patientInfo->save()) {
                                                \Yii::warning(['Error creating new patientInfo entry' => $patientInfo->errors]);
                                                throw new \Exception('Error creating new patient');
                                            }
                                            $internalID = $slid->slid;
                                        }

                                        $content['method'] = 'login';
                                        $pendingLog = new RemotePendingPatientLog([
                                            'internal_id' => $internalID,
                                            'practice_id' => $practice->practice_id,
                                            'type' => $type,
                                            'content' => $content,
                                            'patients_id' => \Yii::$app->patient->patients_id
                                        ]);
                                        $pendingLog->save();
                                        $remotePatient->slid = $internalID;
                                        $remotePatient->save();

                                        return $this->redirect($url);
                                    } else {
                                        \Yii::warning(['Error use exists patient']);
                                        $error4use = 'Your session has expired';
                                    }
                                    break;
                                case 'use-login':
                                    if ($loginForm->load(\Yii::$app->request->post()) && $loginForm->validate() && !empty($loginForm->username) && !empty($loginForm->password)) {
                                        $content['method'] = 'login';

                                        $pendingLog = new RemotePendingPatientLog([
                                            'internal_id' => \Yii::$app->patient->internal_id,
                                            'practice_id' => $practice->practice_id,
                                            'type' => $type,
                                            'content' => $content,
                                            'patients_id' => \Yii::$app->patient->patients_id
                                        ]);
                                        $pendingLog->save();
                                        $remotePatient->slid = \Yii::$app->patient->internal_id;
                                        $remotePatient->save();

                                        $patientInfo = \Yii::$app->patient->model->getPatientInfo($practice->practice_id)->one();

                                        $patientInfo->gender = $remotePatient->gender;
                                        $patientInfo->date_of_birth = $remotePatient->date_of_birth;

                                        if ($patientInfo->save()) {
                                            return $this->redirect($url);
                                        } else {
                                            \Yii::warning(['Error login as patient']);
                                            $error4login = strip_tags(\yii\bootstrap\Html::errorSummary($patientInfo, ['header' => '']));
                                        }
                                    } else {
                                        \Yii::warning(['Error login as patient']);
                                        $error4login = strip_tags(\yii\bootstrap\Html::errorSummary($loginForm, ['header' => '']));
                                    }
                                    break;
                                case 'use-create':
                                    if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
                                        $slid = SlidLookup::findUnusedSlid(true);
                                        $slid->generateSlidHash();
                                        $slid->type = TokenTypes::TYPE_STARTED;
                                        $slid->save();

                                        $patient = new Patient([
                                            'internal_id' => $slid->slid,
                                            'self_registered' => 1,
                                            'internal_id_hash' => $slid->slid_hash,
                                            'status' => Patient::STATUS_ACTIVE,
                                            'accepted_terms' => 0,
                                            'username' => $model->user_name,
                                            'first_name' => $remotePatient->first_name,
                                            'last_name' => $remotePatient->last_name,
                                            'cell_phone' => $remotePatient->cell_phone,
                                            'email' => $remotePatient->email,
                                            'zip' => $remotePatient->zip,
                                            'support_request' => 0,
                                            'notification_updates' => 1,
                                            'is_confirmed_cell_phone' => 1,
                                            'scenario' => Patient::STANDART_CRUD,
                                            'last_updated' => new Expression('NOW()'),
                                        ]);

                                        $patient->setModelSalt();
                                        $patient->password = $patient->generatePasswordHash($model->pass);
                                        $patient->registration_practice_id = $remotePatient->practice_id;

                                        if ($patient->save()) {
                                            TokenAssociations::createAssociation($slid, $patient);

                                            $patientInfo = $patient->getPatientInfo($practice->practice_id)->one();

                                            $patientInfo->gender = $remotePatient->gender;
                                            $patientInfo->date_of_birth = $remotePatient->date_of_birth;

                                            if ($patientInfo->save()) {
                                                if ($model->validatePassword()) {
                                                    $content['method'] = 'create';

                                                    $pendingLog = new RemotePendingPatientLog([
                                                        'internal_id' => \Yii::$app->patient->internal_id,
                                                        'practice_id' => $practice->practice_id,
                                                        'type' => $type,
                                                        'content' => $content,
                                                        'patients_id' => \Yii::$app->patient->patients_id
                                                    ]);
                                                    $pendingLog->save();
                                                    $remotePatient->slid = $slid->slid;
                                                    $remotePatient->save();

                                                    return $this->redirect($url);
                                                }
                                            } else {
                                                \Yii::warning(['Error creating new patientInfo entry' => $patientInfo->errors]);
                                                $error4create = 'Error creating new patient: ' . strip_tags(\yii\bootstrap\Html::errorSummary($patientInfo, ['header' => '']));
                                            }
                                        } else {
                                            \Yii::warning(['Error creating new patient' => $patient->errors]);
                                            $error4create = strip_tags(\yii\bootstrap\Html::errorSummary($patient, ['header' => '']));
                                        }
                                    } else {
                                        \Yii::warning(['Error creating new patient' => $model->errors]);
                                        $error4create = strip_tags(\yii\bootstrap\Html::errorSummary($model, ['header' => '']));
                                    }
                                    break;
                            }
                            break;
                    }
                }
            } else {
                throw new HttpException(200, "Signup from this practice not currently supported. Please contact <a href='/subscriber-home/account-support'>support</a> for more assistance");
            }
        } else {
            throw new NotFoundHttpException("Practice not found");
        }

        return $this->render('index', [
            'remotePatient' => $remotePatient,
            'model' => $model,
            'step' => $step,
            'practice' => $practice,
            'loginForm' => $loginForm,
            'practice_umr_id' => $practice_umr_id,
            'error4create' => $error4create,
            'error4login'  => $error4login,
            'error4use'    => $error4use,
        ]);
    }

    /**
     * @return string|\yii\console\Response|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionLogout(){
        if (\Yii::$app->request->isAjax){
            if (!\Yii::$app->patient->isGuest){
                \Yii::$app->patient->session->delete();
            }
            return 'Ok';
        }
        return \Yii::$app->response->redirect('/');
    }

    /**
     * acepted terms
     * @param $practice_umr_id
     * @param $billing_id
     * @return string|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionTermsOfUse($practice_umr_id, $billing_id)
    {
        $practice = Practices::findOne(['practice_umr_id' => $practice_umr_id]);
        $model = new SignInForm(['scenario' => SignInForm::SCENARIO_TERMS_READ]);

        if (\Yii::$app->request->isPost && $model->load(\Yii::$app->request->post()) && $model->validate()) {
            $patient = \Yii::$app->patient->model;
            $patient->accepted_terms = 1;
            $patient->completed_registration_date = new Expression('NOW()');

            $patientInfo = $patient->getPatientInfo($practice->practice_id)->one();
            $patientInfo->hipaa_terms_accepted = 1;

            if ($patient->save() && $patientInfo->save()) {

                $type = Log::TYPE_REMOTE_PENDING_PATIENT_TERMS_ACCEPTED;
                $content = [];
                $content['who'] = \Yii::$app->request->getUserIP();
                $content['billing_id'] = $billing_id;

                $pendingLog = new RemotePendingPatientLog([
                    'internal_id' => \Yii::$app->patient->internal_id,
                    'practice_id' => $practice->practice_id,
                    'type' => $type,
                    'content' => $content,
                    'patients_id' => \Yii::$app->patient->patients_id
                ]);
                $pendingLog->save();

                return $this->redirect('/subscriber-home/record');
            }
            else{
                $practice->addError('', 'Unknown error');
            }
        }

        $textHipaa = $practice->hipaa_terms;
        if(!$textHipaa){
            $staticContent = StaticContent::find()->where(['key' => 'hipaa_terms'])->one();
            $textHipaa = ($staticContent) ? $staticContent->content : '';
        }
        return $this->render('terms', [
            'model' => $model,
            'practice' => $practice,
            'textHipaa'=>$textHipaa
        ]);
    }
}