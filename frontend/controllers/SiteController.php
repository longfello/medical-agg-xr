<?php

namespace frontend\controllers;

use common\components\NotificationManager\messages\InvoicePaymentFailedLast;
use common\models\AttachSlidForm;
use common\components\Helper;
use common\models\Maintenance;
use common\models\SlidLookup;
use common\models\StaticContent;
use common\models\TokenAssociations;
use common\models\TokenTypes;
use common\models\ScanEvent;
use common\models\MessagesBlocking;
use common\models\UnblockForm;
use Yii;
use common\models\LoginForm;
use common\models\LoginFormPage;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\Url;
use yii\validators\EmailValidator;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use common\models\Patient as PatientModel;
use common\models\FaqPage;
use common\models\ContactNotificationsBlocking;
use common\models\Patient;
use common\models\MedicationReminders;
use common\components\NotificationManager\channels\Email\Email;
use common\components\NotificationManager\channels\SMS\SMS;
use common\components\NotificationManager\messages\ForgotPass;
use common\components\NotificationManager\messages\EmergencyContactDecline;
use common\components\NotificationManager\messages\RejectPhoneEmail;

/**
 * Class SiteController
 * @package frontend\controllers
 */
class SiteController extends \common\components\Controller
{
    /**
     * @var array
     */
    private $urls = [
        'med-info' => "/premium-data",
        'manage-cards-ids' => "/subscriber-home/manage-tokens"
    ];

    /**
     * Displays homepage.
     *
     * @return string
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public function actionIndex()
    {
        $loginUrl = \Yii::$app->guestSession->get('loginUrl');

        if(in_array($loginUrl, $this->urls)) {
            \Yii::$app->guestSession->remove('loginUrl');
        }

        $this->view->params['showLogoUrl'] = true;
        $this->view->params['showBannerHeader'] = true;

        return $this->render('index');
    }


    /**
     * Render error page.
     *
     * @return string|Response
     */
    public function actionError(){
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
        return $this->redirect('/');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionMaintenance()
    {
        if (Yii::$app->isMaintenance) {
            if (\Yii::$app->isDbFree) {
                $this->layout = 'maintenance-db-free';
            } else {
                $this->layout = 'maintenance';
            }

            try {
                if (\Yii::$app->patient->isGuest || !\Yii::$app->patient->isAdmin()) {
                    return $this->render('maintenance');
                }
            } catch (\Throwable $e){
                return $this->render('maintenance');
            }

            if (Yii::$app->request->get('maintenance', false) === 'off') {
                Maintenance::deleteAll();
                $this->redirect('/');
                return '';
            }
            return $this->render('maintenance');
        }

        $this->redirect('/');
        return '';
    }

    /**
     * @param null $forceLogout
     * @param bool $setScanEvent
     *
     * @return string|\yii\console\Response|Response
     * @throws Exception
     * @throws \Throwable
     */
    public function actionLogin($forceLogout = null, $setScanEvent = false)
    {
        if ($setScanEvent) {
            $scanEvent = ScanEvent::findOne(['event_id' => $setScanEvent]);
            $scanEvent->state_enum = ScanEvent::STATE_LOGIN;
            $scanEvent->save();
        }

        if (!Yii::$app->patient->isGuest) {
            if ($forceLogout !== null) {
                \Yii::$app->patient->logout();
                return \Yii::$app->response->redirect(Yii::$app->urlManagerFrontend->createAbsoluteUrl('login'));
            }

            return \Yii::$app->response->redirect(Url::to('/subscriber-home/record'));
        }

        if(Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if(isset($post['LoginForm'])) {
                $model = new LoginForm(['scenario' => LoginForm::SCENARIO_LOGIN]);
            }
            else {
                $model = new LoginFormPage(['scenario' => LoginForm::SCENARIO_LOGIN]);
            }
        } else {
            $model = new LoginFormPage(['scenario' => LoginForm::SCENARIO_LOGIN]);
        }

        $model->maintenance = Maintenance::isActiveAny();

        $redirectUrl = $model->maintenance ? '/admin' : '/subscriber-home';

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if (!Yii::$app->patient->isGuest && !$model->forgot) {
                    $url = \Yii::$app->session->get('loginUrl',
                        \Yii::$app->guestSession->get('loginUrl', $redirectUrl));
                    \Yii::$app->guestSession->remove('loginUrl');
                    $url = \Yii::$app->guestSession->removeGetParam( $url, 'username' );

                    return $this->redirect($url);
                }
            }
        }

        // There are page and popup model. But when render, we need model for page
        $formModel = new LoginFormPage(['scenario' => LoginForm::SCENARIO_LOGIN]);
        $formModel->setAttributes($model->getAttributes());

        if($model->hasErrors()) {
            $formModel->addErrors($model->getErrors());
        }

        $page =  Yii::$app->request->getQueryParam('page', null);

        if(array_key_exists($page, $this->urls) && !empty($this->urls[$page])) {
            \Yii::$app->guestSession->set('loginUrl', $this->urls[$page]);
        }

        $this->view->params['showBannerHeader'] = true;

        return $this->render('login', [
            'model' => $formModel,
            'chooseWay' => false
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     * @throws \Throwable
     */
    public function actionLogout()
    {
        Yii::$app->patient->logout();
        return $this->redirect('/');
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * @return string
     */
    public function actionFaq()
    {
        $mail = new InvoicePaymentFailedLast();
        $mail->href = Url::to('/subscriber-home/account-status?mode=popup', true);
        $mail->total = 666;
        $mail->patient = Yii::$app->patient->model;
        $mail->send('diomidtreumov+dev@gmail.com', true, Email::getID());

        $cache = Yii::$app->cache;
        $faqPage = $cache->get(FaqPage::FAQ_PAGE_CACHE_KEY);
        if ($faqPage === false) {
            $faqPage = FaqPage::getFaqPageList();
            $cache->set(FaqPage::FAQ_PAGE_CACHE_KEY, $faqPage);
        }
        return $this->render('faq',['faqPage' => $faqPage]);
    }

    /**
     * @return string
     */
    public function actionSupportFaq()
    {
        $params = '?section=content';

        foreach($_GET as $key => $value) {
            if($key === 'question') {
                $key = 'faq';
            }

            $params .= '&' . $key . '=' . $value;
        }

        $url = (getenv('WWW_SCHEME') . '://' . getenv('FAQ_HOST') . $params);

        return $this->render('proxy-faq', ['url' => $url]);
    }

    /**
     * @param $page
     *
     * @return string
     * @throws HttpException
     */
    public function actionPage($page)
    {
        $pageContent = StaticContent::getContentPage($page);

        if (!$pageContent) {
            throw new HttpException(404, 'Page not found');
        }

        $this->view->title = $pageContent['title'];

        return $this->render('page', ['pageContent' => $pageContent]);
    }


    /**
     * Page for unblock email or phone
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionUnblockPage()
    {
        $model = new UnblockForm();

        if (\Yii::$app->request->isPost && $model->load(\Yii::$app->request->post()) && $model->validate()) {
            $contact = $model->emailOrPhone;

            // is contact not email, normalize in phone number
            $isContactEmail = (new EmailValidator())->validate($contact);
            if(!$isContactEmail) {
                $contact = Helper::normalizePhoneNumber($model->emailOrPhone);
            }

            $messageBlocking = MessagesBlocking::find()
                                               ->andWhere(['contact' => $contact])
                                               ->andWhere(['block_status' => 1])
                                               ->andWhere([
                                                   'or',
                                                   ['<=', 'date_resubscribe_sent', new Expression('DATE_SUB(NOW(), INTERVAL '.getenv('RESUBSCRIBE_DISALLOW_PERIOD').')')],
                                                   ['date_resubscribe_sent' => NULL]
                                               ])
                                               ->one();

            // is blocked item exist
            if ($messageBlocking) {
                /** @var MessagesBlocking $messageBlocking */
                if($messageBlocking->sendResubscribeMessage()) {
                    $messageBlocking->setDateResubscribeSent();

                    $contact = $messageBlocking->contact;
                    switch($messageBlocking->type) {
                        case MessagesBlocking::CONTACT_TYPE_EMAIL:
                            $wasSentMessage = "Email was sent to $contact. Check your email for further instructions";
                            break;
                        case MessagesBlocking::CONTACT_TYPE_PHONE:
                            $wasSentMessage = "SMS was sent to $contact. Check your phone for further instructions";
                            break;
                        default:
                            $wasSentMessage = false;
                    }

                    Yii::$app->session->setFlash('success', $wasSentMessage);
                    $model->emailOrPhone = '';
                }
            } else {
                // check if item exist without 3 month condition
                $isToOftenForUnblock = MessagesBlocking::find()
                                                       ->andWhere(['contact' => $contact])
                                                       ->andWhere(['block_status' => 1])
                                                       ->exists();

                // is to often for unblock (3 month limit)
                if ($isToOftenForUnblock) {
                    $supportHref = '/support/faq/?faq=how-can-i-get-more-support';
                    Yii::$app->session->setFlash('error', 'Email or phone number can be unblocked this way only once in 3 months. <a target="_blank" href="'.$supportHref.'">Contact support</a> if you need further help');

                    // blocked email or phone doesnt exist
                } else {
                    Yii::$app->session->setFlash('error', 'Blocked email or phone was not found');
                }
            }
        }

        return $this->render('unblock-page', ['model' => $model]);
    }

    /**
     * @param null $hash
     * @param null $phoneHash
     *
     * @return array|string
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function actionUnsubscribe($hash = null, $phoneHash = null)
    {
        if (\Yii::$app->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $response = ['success' => false];
            $contactItem = MessagesBlocking::findOne(['hash' => \Yii::$app->request->post('hash')]);
            if ($contactItem) {
                if ($contactItem->block_status) {
                    $contactItem->block_status = 0;
                    $response['result'] = 'unblocked';

                    // successfuly used hash for resubscribe - generate new hash
                    $contactItem->generateNewHash();
                } else {
                    $postPhoneHash = \Yii::$app->request->post('phone_hash');
                    if ($postPhoneHash){
                        $this->resetCellPhone($postPhoneHash);
                    }

                    $contactItem->block_status = 1;
                    $response['result'] = 'blocked';
                }

                if ($contactItem->save()) {
                    $response['success'] = true;
                } else {
                    $response['result'] = $contactItem->errors;
                }
            }
            return $response;
        }

        $contactItem = MessagesBlocking::findOne(['hash' => $hash]);
        if ($contactItem) {
            //reset cell phone if patient has already blocked
            if ($contactItem->block_status && $phoneHash){
                $this->resetCellPhone($phoneHash);
            }
            return $this->render('unsubscribe', ['contactItem' => $contactItem, 'phoneHash' => $phoneHash]);
        }

        throw new NotFoundHttpException('This page is not found');
    }

    /**
     * This method reset patient cell_phone by $cellPhoneHash and sends email that cell phone has been reset.
     *
     * @param $cellPhoneHash
     * @throws \Throwable
     */
    private function resetCellPhone($cellPhoneHash)
    {
        /** @var Patient[] $patients */
        $patients = Patient::find()->where('md5(cell_phone) = :hash', [':hash' => $cellPhoneHash])->all();
        foreach ($patients as $patient){
            $patientCellPhone = $patient->cell_phone;
            $patient->cell_phone = '';
            if ($patient->email){
                $mail = new RejectPhoneEmail([
                    'cellPhone' => $patientCellPhone
                ]);
                $mail->send($patient, true, Email::getID());
            }
            $patient->save();
        }
    }

    /**
     * @param null $hash
     *
     * @return mixed|string
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidRouteException
     */
    public function actionResubscribe($hash = null)
    {
        if(!$hash) {
            throw new NotFoundHttpException('This page is not found');
        }

        $contactItem = MessagesBlocking::findOne(['hash' => $hash]);

        if(!$contactItem || !$contactItem->block_status) {
            $supportHref = '/support/faq/?faq=how-can-i-get-more-support';
            return $this->render('resubscribe-invalid-hash', [
                'supportHref' => $supportHref
            ]);
        }

        return $this->runAction('unsubscribe', ['hash' => $hash]);
    }

    /**
     * @param null $hash
     *
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionContactDecline($hash = null)
    {
        if(!$hash) {
            throw new NotFoundHttpException('This page is not found');
        }

        $type = Yii::$app->request->get('type');
        if(!in_array($type, [ContactNotificationsBlocking::TYPE_EMAIL, ContactNotificationsBlocking::TYPE_PHONE])) {
            throw new NotFoundHttpException('This page is not found');
        }

        $internalId = Yii::$app->request->get('internal_id');

        // CONTACT BLOCKING
        $contactItem = MessagesBlocking::findOne(['hash' => $hash]);
        if(!$contactItem) {
            throw new NotFoundHttpException('This page is not found');
        }

        // CONTACT NOTIFICATION BLOCKING ITEM
        $contactNotificationBlocking = ContactNotificationsBlocking::findOne([
            'internal_id' => $internalId,
            'destination' => $contactItem->contact,
            'type'        => $type
        ]);
        if(!$contactNotificationBlocking) {
            throw new NotFoundHttpException('This page is not found');
        }

        // PATIENT
        $patient = PatientModel::findOne(['internal_id' => $internalId]);
        if(!$patient) {
            throw new NotFoundHttpException('This page is not found');
        }

        if(\Yii::$app->request->isPost) {
            // COMMENT
            $comment = \yii\helpers\HtmlPurifier::process(\Yii::$app->request->post('comment'));

            // BLOCK EMERGENCY CONTACT
            $contactNotificationBlocking->blocked = ContactNotificationsBlocking::BLOCKED_YES;
            $contactNotificationBlocking->explanation = $comment;
            $contactNotificationBlocking->save();

            // EMAIL TO PATIENT
            $mail = new EmergencyContactDecline([
                'comment' => $comment,
                'contact' => $contactItem->contact,
                'type'    => $contactItem->type,
            ]);
            $mail->send($patient, false, Email::getID());

            Yii::$app->session->setFlash('success', "Declined successfully");
            return $this->redirect('/');
        }
        else {
            if($contactNotificationBlocking->blocked) {
                return $this->render('contact-allow', [
                    'contactItem' => $contactItem,
                    'patient'     => $patient,
                    'type'        => $type
                ]);
            }
            else {
                return $this->render('contact-decline', [
                    'contactItem' => $contactItem,
                    'patient'     => $patient
                ]);
            }
        }
    }

    /**
     * @param null $hash
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionContactAllow($hash = null)
    {
        if(!$hash) {
            throw new NotFoundHttpException('This page is not found');
        }

        $type = Yii::$app->request->get('type');
        if(!in_array($type, [ContactNotificationsBlocking::TYPE_EMAIL, ContactNotificationsBlocking::TYPE_PHONE])) {
            throw new NotFoundHttpException('This page is not found');
        }

        $internalId = Yii::$app->request->get('internal_id');
        if(!$internalId) {
            throw new NotFoundHttpException('This page is not found');
        }

        // CONTACT BLOCKING
        $contactItem = MessagesBlocking::findOne(['hash' => $hash]);
        if(!$contactItem) {
            throw new NotFoundHttpException('This page is not found');
        }

        // CONTACT NOTIFICATION BLOCKING ITEM
        $contactNotificationBlocking = ContactNotificationsBlocking::findOne([
            'internal_id' => $internalId,
            'destination' => $contactItem->contact,
            'type'        => $type
        ]);
        if(!$contactNotificationBlocking) {
            throw new NotFoundHttpException('This page is not found');
        }

        // CHECK IF CONTACT BLOCKED
        if(!$contactNotificationBlocking->blocked) {
            throw new NotFoundHttpException('This page is not found');
        }

        // UNBLOCK
        $contactNotificationBlocking->blocked = 0;
        $contactNotificationBlocking->save();

        Yii::$app->session->setFlash('success', "Allowed successfully");
        return $this->redirect('/');
    }

    /**
     * Method attaching scanned card to account
     * @return string view
     * @throws Exception
     * @throws HttpException
     * @throws \Throwable
     */
    public function actionAttachSlid()
    {

        $tokenSlidHash = \Yii::$app->request->get('token_slid_hash', null);
        $token = SlidLookup::findOne(['slid_hash' => $tokenSlidHash]);
        /** @var SlidLookup $token */
        if (!SlidLookup::canBeAttachedToPatient($token) || !$tokenSlidHash) {
            throw new HttpException(404, 'Token not found.');
        }

        $request = $token->order;
        $form = $patient = null;

        if (\Yii::$app->patient->isGuest) {
            $form = new AttachSlidForm();
            $form->scenario = 'attach_slid_scenario';
            $form->token_slid_hash = \Yii::$app->request->get('token_slid_hash', null);

            \Yii::$app->guestSession->set('slid', $token->slid);

            if ($form->load(\Yii::$app->request->post())) {
                if ($form->validate()) {
                    $patient = PatientModel::findOne(['username' => $form->username]);
                }
            }
        } else {
            $patient = PatientModel::findOne(['username' => \Yii::$app->patient->username]);
        }

        if ($patient) {
            if ($request) {
                if ($request->internal_id != $patient->internal_id) {
                    \Yii::$app->patient->logout();
                    throw new HttpException(404, 'That is not the account that ordered card. Please try again or contact support');
                }
                $request->received_date = new Expression('NOW()');
                $request->save();
            }

            TokenAssociations::createAssociation($token, $patient);

            \Yii::$app->session->setFlash('card_activated_success', 'Card activated');
            return $this->redirect(Url::to('/subscriber-home/manage-tokens'));
        }

        $type = TokenTypes::find()->where(['token_type' => $token->type])->select(['text'])->asArray()->one()['text'];

        return $this->render('attach-slid', [
            'ordered' => (bool) $request,
            'model'   => $form,
            'type'    => $type
        ]);
    }

    /**
     * @return string|Response
     * @throws HttpException
     * @throws \Throwable
     */
    public function actionForgotPassword()
    {

        $model = new LoginForm();
        if ($model->load(\Yii::$app->request->post())) {

            $way = \Yii::$app->request->post('way');
            if ($model->forgot) {
                $patient = $model->forgotModel;
                $changePasswordUrl = Url::to('/forgot?slid=' . md5($patient->internal_id) . '&key=' . md5($patient->salt), true);
                $message = new ForgotPass(['changePasswordShortUrl' => $changePasswordUrl]);

                if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost && empty($way)) {
                    if (!empty($patient->cell_phone)) {

                        return $this->asJson(['isPhone' => true]);
                    } else {
                        return $this->asJson(['isPhone' => false]);
                    }
                } elseif (empty($way) && !empty($patient->cell_phone)) {

                    return $this->render('login', [
                        'model' => $model,
                        'chooseWay' => true,
                    ]);
                } elseif (!empty($patient->cell_phone) && $way === 'phone') {
                    $success = $message->send($patient, true, SMS::getID());

                    if (!$success) {
                        throw new HttpException(500,
                            'Sorry, SMS was not sent for an unknown error. Please, use e-mail for restore your password');
                    }

                    $patient->link_exp_date = new Expression('NOW() + INTERVAL 1 DAY');
                    $patient->save(false);

                    $session = Yii::$app->session;
                    $session->setFlash('resetting_password_by_phone', Helper::normalizePhoneNumber($patient->cell_phone, true));

                    return $this->asJson(['success' => true, 'redirectUrl' => '/forgot-password']);

                } elseif (empty($patient->cell_phone) || $way === 'email') {
                    try {
                        $success = $message->send($patient, true, Email::getID());
                        if ($success) {
                            $patient->link_exp_date = new Expression('NOW() + INTERVAL 1 DAY');
                            $patient->save(false);

                            return $this->render('forgot');
                        } else {
                            throw new \Exception('Unknown swiftmail error occurred.', 500);
                        }
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage() . ' in ' . $e->getFile() . ' (' . $e->getLine() . ")\r\n" . $e->getTraceAsString(),
                            500);
                    }
                }
            }

            if (\Yii::$app->session->hasFlash('resetting_password_by_phone')) {
                return $this->render('forgot');
            }

            if (\Yii::$app->request->isAjax && (\Yii::$app->request->post('way') === 'phone' || $model->forgot)) {
                return $this->asJson(['error' => $model->getFirstError('email')]);
            }
        }

        if (!\Yii::$app->session->hasFlash('hiddenPhone')) {
            $this->redirect('/');
        }

        //if (\Yii::$app->session->hasFlash('resetting_password_by_mail')) {
        //return $this->render('forgot', [
        //'model' => $model,
        //'email' => \Yii::$app->session->getFlash('resetting_password_by_mail'),
        //]);
        //return 0;
        //}

        return $this->render('login', [
            'model' => $model,
            'chooseWay' => false,
        ]);
    }

    /**
     * @return string
     */
    public function actionForgotUsername(){
        return $this->render('forgot-username',['hiddenPhone' => \Yii::$app->session->getFlash('hiddenPhone')[0]]);
    }

    /**
     * @return string
     */
    public function actionCamera()
    {
        $this->layout = '//ajax';
        return $this->render('camera');
    }

    /**
     * @return string
     */
    public function actionCameraIe()
    {
        $this->layout = '//ajax';
        return $this->render('camera-ie');
    }

    /**
     *
     */
    public function actionHideWarning()
    {
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->session->set('hide-warning', true);
        }
    }

    /**
     * @return array|bool
     */
    public function actionValidatePassword()
    {
        if (\Yii::$app->request->isAjax && !\Yii::$app->patient->isGuest) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $model = new LoginForm(['scenario' => LoginForm::SCENARIO_PASSWORD]);
            $model->username = \Yii::$app->patient->username;
            if ($model->load(\Yii::$app->request->post(), 'Patient') && $model->validate()) {
                return [
                    'status' => true
                ];
            }
            else {
                \Yii::$app->response->setStatusCodeByException(new ForbiddenHttpException());
                return [
                    'status' => false,
                    'errorMessage' => $model->getFirstError('password')
                ];
            }
        }

        return false;
    }

    /**
     * @param $patientHash
     * @param $reminderId
     *
     * @return array|string
     * @throws NotFoundHttpException
     */
    public function actionConfirmMedication($patientHash, $reminderId)
    {
        $reminder = MedicationReminders::findOne(['reminder_id' => $reminderId]);
        $patient = Patient::findOne(['internal_id_hash' => $patientHash]);
        /** @var MedicationReminders $reminder */
        if (!$reminder || !$reminder->is_sent || !$patient) {
            throw new NotFoundHttpException('Page not found');
        }

        $tz = null;
        if($patient->tz_auto){
            $tz = $patient->tz;
        }

        if (\Yii::$app->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $reminder->is_confirmed = true;
            $reminder->save();
            $reminder->clearNotifications();

            return ['success' => true];
        }

        return $this->render('confirm-medication', [
            'patientHash'    => $patientHash,
            'reminderId'     => $reminderId,
            'medicationList' => $reminder->getRelatedMedications('medication_text'),
            'confirmed' =>  $reminder->is_confirmed,
            'tz' => $tz
        ]);
    }
    /**
     * Displays cookie policy page.
     *
     * @return string
     */
    public function actionCookie()
    {
        return $this->render('page/cookie');
    }

    /**
     * support page for a non-logged in user
     * @return string
     */
    public function actionAccountSupport()
    {
        return $this->render('page/account-support');
    }
}
