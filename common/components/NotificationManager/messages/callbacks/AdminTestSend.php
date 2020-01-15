<?php

namespace common\components\NotificationManager\messages\callbacks;

use common\components\NotificationManager\components\BaseCallback;
use common\components\NotificationManager\channels\SMS\SMS;
use frontend\modules\admin\models\SmsTestAssign;

/**
 * Class AdminTestSend
 * @package common\components\NotificationManager\messages\callbacks
 */
class AdminTestSend extends BaseCallback
{
    /**
     *
     */
    protected function _run()
    {
        if ($this->_channel->getID() == SMS::getID()) {
            echo ("Admin SMS test answer. message_id: ".$this->message_id);
            $this->smsTestRefresh();
        } else {
            echo ("Admin test message has been sent successfully");
        }
    }

    /**
     *
     */
    public function smsTestRefresh(){
        $assign = SmsTestAssign::find()->where(['message_id' => $this->message_id])->one();
        if ($assign) {
            /** @var SmsTestAssign $assign */
            $assign->resp_time = gmdate('Y-m-d H:i:s');
            $assign->save();
        }
    }
}
