<?php

namespace common\components\NotificationManager\messages\callbacks;

use common\models\Patient;
use common\models\Log\SmsPasswordRequestLog;
use common\components\NotificationManager\components\BaseCallback;
use common\components\NotificationManager\channels\SMS\SMS;

/**
 * Class ForgotPassSended
 */
class ForgotUsernameSended extends BaseCallback
{
    /**
     * @throws \yii\base\Exception
     */
    protected function _run()
    {
        if ($this->_channel::getID() == SMS::getID()) {
            $current_patient = Patient::findOne(['patients_id' => $this->_model->patients_id]);
            (new SmsPasswordRequestLog(['current_patient' => $current_patient]))->save();

            echo ("Patient with id = {$this->_model->patients_id} has been texted for remind username");
        } else {
            echo ("Patient with id = {$this->_model->patients_id} has been emailed for remind username");
        }
    }
}
