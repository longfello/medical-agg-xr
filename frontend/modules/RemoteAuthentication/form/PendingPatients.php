<?php

namespace frontend\modules\RemoteAuthentication\form;

use common\models\RemotePendingPatients;
use common\models\Patient;
use yii\base\Model;

/**
 * Class ActivateForm
 * @package common\models
 */
class PendingPatients extends Model
{
    /**
     * @var string
     */
    public $cell_phone;
    /**
     * @var string
     */
    public $date_of_birth;

    /**
     * @var string
     */
    public $sms_confirmation_code;

    /**
     * @var string
     */
    public $user_name;
    /**
     * @var string
     */
    public $pass;
    /**
     * @var string
     */
    public $pass2;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['cell_phone', 'date_of_birth', 'sms_confirmation_code', 'user_name', 'pass','pass2'], 'string'],
            ['user_name', 'filter', 'filter' => 'trim'],
            [['user_name', 'pass'], 'required', 'message' =>  'Incorrect username or password.'],
            [['pass','pass2'], 'string', 'min' => 6],
            [['pass'], 'validateMatch'],
            [['user_name'], 'validateName'],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateMatch($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if ($this->pass2 != $this->pass) {
                $this->addError('pass2', 'Password not match.');
            }
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateName($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $patient = Patient::findOne(['username' => $this->user_name]);
            if($patient){
                $this->addError('user_name', 'This username is already in use');
            }
        }
    }

    /**
     * @param null $attribute
     * @param null $params
     * @return bool
     * @throws \Throwable
     */
    public function validatePassword($attribute=null, $params=null)
    {
        if (!$this->hasErrors()) {
            if (\Yii::$app->patient->login($this->user_name, $this->pass)) {
                return true;
            }
            $this->addError('pass', 'Incorrect username or password.');
        }
        return false;
    }

    /**
     * step 1 remote pending patients authentication
     * @param RemotePendingPatients $remotePatient
     * @return bool
     */
    public function checkStep1( $remotePatient ){
        $phone = preg_replace("/[^0-9]/", '', $this->cell_phone);
        $remotePatient->cell_phone = preg_replace("/[^0-9]/", '', $remotePatient->cell_phone);

        if($phone != $remotePatient->cell_phone){
            $this->addError('cell_phone', 'Your input does not match the input in your Doctor\'s records.');
        }
        $year = substr($this->date_of_birth, -2);
        $pandingYear = date('y', strtotime($remotePatient->date_of_birth));

        if($year != $pandingYear){
            $this->addError('date_of_birth', 'Your input does not match the input in your Doctor\'s records.');
        }

        $out = false;
        if($this->hasErrors()){
            $remotePatient->addFailedAttempts();
        }
        else{
            $out = $remotePatient->sendCode();
            if($out){
                $remotePatient->sms_confirmation_code_guess_fails = 0;
                $remotePatient->save();
            }
        }
        return $out;
    }

    /**
     * step 2
     * @param RemotePendingPatients $remotePatient
     * @return bool
     */
    public function checkStep2( $remotePatient ){
        $time = strtotime(RemotePendingPatients::expiredCode);
        $time = date('Y-m-d H:i:s', $time);

        if($time > $remotePatient->sms_confirmation_code_sent_time){
            $this->addError('sms_confirmation_code', 'Code expired');
        }
        else if($remotePatient->sms_confirmation_code != $this->sms_confirmation_code){
            $this->addError('sms_confirmation_code', 'That is not the correct code.');
        }

        if($this->hasErrors()){
            $remotePatient->sms_confirmation_code_guess_fails++;
            $remotePatient->sms_confirmation_code_guess_fails_all++;
            $remotePatient->save();
            return false;
        }
        return true;
    }


}
