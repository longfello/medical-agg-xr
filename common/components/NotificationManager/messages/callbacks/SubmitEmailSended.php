<?php

namespace common\components\NotificationManager\messages\callbacks;

use common\models\Patient;
use common\models\Log\EmailChangeLog;
use common\components\NotificationManager\components\BaseCallback;

/**
 * Class SubmitEmailSended
 * @package common\components\mail\callback
 */
class SubmitEmailSended extends BaseCallback
{

    /**
     * @throws \yii\base\Exception
     */
    protected function _run()
    {
        $account = Patient::findOne(['patients_id' => $this->_model->patients_id]);
        (new EmailChangeLog([
            'account' => $account,
            'oldEmail' => $this->_data['oldEmail'],
            'newEmail' => $this->_data['newEmail'],
            'emailStatus' => 'Wait confirmation',
        ]))->save();

        echo("Patient with id = {$this->_model->patients_id} has been emailed to {$this->_data['newEmail']}");
    }
}
