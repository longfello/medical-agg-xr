<?php

namespace common\components\NotificationManager\messages\callbacks;

use common\models\Patient;
use common\models\Log\SmsChangeLog;
use common\components\NotificationManager\components\BaseCallback;

/**
 * Class SubmitSmsSended
 * @package common\components\NotificationManager\messages\callbacks
 */
class SubmitSmsSended extends BaseCallback
{

    /**
     * @throws \yii\base\Exception
     */
    protected function _run()
    {
        $account = Patient::findOne(['patients_id' => $this->_model->patients_id]);
        $log = new SmsChangeLog();
        foreach($this->_data as $key => $value){
            try {
                if ($log->canSetProperty($key)){
                    $log->$key = $value;
                }
            } catch (\Throwable $e){}
        }
        $log->account = $account;
        $log->smsStatus = 'Wait confirmation';
        $log->save();

        echo("Patient with id = {$this->_model->patients_id} has been sent");
    }
}
