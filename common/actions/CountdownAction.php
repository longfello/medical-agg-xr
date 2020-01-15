<?php
namespace common\actions;

use common\components\AjaxHelper;
use common\models\Maintenance;
use common\models\ScanEvent;
use enroll\components\Enroller;
use frontend\modules\patient\components\Patient;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 31.08.17
 * Time: 18:51
 */
class CountdownAction extends \yii\base\Action
{
    use AjaxHelper;

    /** @const string */
    const LOGIN_PATIENT = 'patient';
    
    /** @const string */
    const LOGIN_ENROLLER = 'enroller';

    /** @var string */
    public $userType;

    /**
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function run()
    {
        Yii::$app->response->headers->add("Cache-Control", "no-store, no-cache, must-revalidate");

        if (Yii::$app->request->isAjax) {
            if ($this->userType == self::LOGIN_ENROLLER) {
                $this->handleEnrollerSession();
            } else {
                $this->handlePatientSession();
            }

            $this->setResult(true);
            $this->controller->layout = 'ajax';
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return $this->response;
        }

        return false;
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    private function handlePatientSession()
    {
        $scanEventId = Yii::$app->request->get('eid');

        if ($scanEventId) {
            $duration = 0;
            $scanEvent = ScanEvent::findOne(['event_id' => $scanEventId]);
            if ($scanEvent) {
                $scanTime = ArrayHelper::getValue($scanEvent, 'scantime', false);
                if ($scanTime) {
                    $duration = Patient::AUTH_TIMEOUT - time() + strtotime($scanTime);
                    if ($duration <= 0) {
                        $scanEvent->delete();
                    }
                }
            }
            $this->setResponse('until', $duration);
        } else if (Yii::$app->patient && Yii::$app->patient->isGuest) {
            $this->setResponse('until', 0);
            $this->setResponse('percent', 0);
            $this->setResponse('text', '');
        } else {
            if (Maintenance::isActiveAny()) {
                if (Yii::$app->patient && !Yii::$app->patient->isAdmin()) {
                    Yii::$app->patient->logout();
                    $this->setResponse('logout', true);
                }
                $this->setResponse('maintenance', true);
            } else {
                $duration = Yii::$app->patient ? Yii::$app->patient->getSessionTime() : 0;
                $this->processSessionTimer(Patient::AUTH_TIMEOUT, $duration);
            }
        }
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    private function handleEnrollerSession()
    {
        if (Yii::$app->enroller && Yii::$app->enroller->isGuest) {
            $this->setResponse('until', 0);
            $this->setResponse('percent', 0);
            $this->setResponse('text', '');
        } else {
            if (Maintenance::isActiveAny()) {
                $this->setResponse('logout', true);
                $this->setResponse('maintenance', true);
            } else {
                $duration = Yii::$app->enroller ? Yii::$app->enroller->getSessionTime() : 0;
                $this->processSessionTimer(Enroller::AUTH_TIMEOUT, $duration);
            }
        }
    }

    /**
     * @param $timeout
     * @param $duration
     * @throws \yii\db\Exception
     */
    private function processSessionTimer($timeout, $duration)
    {
        $this->setResponse('until', $duration);
        $this->setResponse('percent', round(100 * $duration / $timeout));

        if ($duration > 60) {
            $duration = 60 * ceil($duration / 60);
        }
        $time = time() - $duration;
        $nextMaintenance = Maintenance::getTimeToMaintenance();
        if ($nextMaintenance < (119*60) && $nextMaintenance > $timeout) {
            $nextMaintenance = 60*round($nextMaintenance / 60);
            $this->setResponse('maintenanceText','in '.$nextMaintenance/60 .' minutes');
        }
        if ($nextMaintenance > 0 && $nextMaintenance < $timeout) {
            $nextMaintenance = 60 * round($nextMaintenance / 60);
            $maintenancePercent = 100 - round(100 * $nextMaintenance / $timeout);
            $this->setResponse('maintenanceText',
                Yii::$app->formatter->asRelativeTime(time(), time() - $nextMaintenance));
            $this->setResponse('maintenancePercent', $maintenancePercent);
        }
        $this->setResponse('text', Yii::$app->formatter->asRelativeTime(time(), $time));
    }

}
