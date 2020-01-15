<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.03.18
 * Time: 15:22
 */

namespace common\components\PerfectParser\DataSources\MedFusion\common\connection\action;


//use common\components\mail\MFBadCredentials;
use common\components\NotificationManager\messages\MFNowAccepted;
use common\models\MedfusionConnections;
use common\models\Patient;
use common\models\Practices;
use yii\base\BaseObject;
use common\components\NotificationManager\channels\Email\Email;
use common\components\NotificationManager\messages\MFBadCredentials;

/**
 * Class prototype
 * @package common\components\PerfectParser
 *
 * @property mixed $authError
 * @property string $connectionName
 */
abstract class prototype extends BaseObject
{
    /** @var Patient */
    public $patient;

    /** @var Practices|null */
    public $practice;

    /** @var MedfusionConnections|null */
    public $connection;

    /**
     *
     */
    public function init(){
        parent::init();
        $this->process();
    }

    /**
     * @throws \Throwable
     */
    public function mailCredentialsOk(){
        $mail = new MFNowAccepted([
            'practice_name' => $this->practice->practice_name
        ]);
        $mail->send($this->patient, true, Email::getID());
    }

    /**
     * @throws \Throwable
     */
    public function mailCredentialsBad(){
        $mail = new MFBadCredentials([
            'practice_name' => $this->practice->practice_name,
            'patient' => $this->patient
        ]);
        $mail->send($this->patient, true, Email::getID());
    }

    /**
     * @param $portalId
     * @param int $ercode
     * @throws \Throwable
     */
    public function setAuthError($portalId, $ercode=0){
        if ($portalId) {
            try{
                $model = MedfusionConnections::getConnection($this->patient, $portalId, false);
                if ($model) {
                    $model->auth_error_registered = $ercode;
                    $model->save();
                }
            } catch (\Exception $e){
                \Yii::$app->perfectParser->error($e->getMessage());
            }
        }
    }

    /**
     * @return string
     */
    public function getConnectionName(){
        return $this->connection?$this->connection->formatedPortalName():$this->practice->practice_name;
    }

    /**
     * @return mixed
     */
    abstract public function process();
}