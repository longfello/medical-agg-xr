<?php

namespace common\components\NotificationManager\messages\callbacks;

use common\components\NotificationManager\components\BaseCallback;

/**
 * Class AlertContactOnScanSended
 */
class AlertContactOnScanSended extends BaseCallback
{
    /**
     *
     */
    protected function _run()
    {
        echo ("Emergency contact with id = {$this->_data['contact_id']} has been alerted about scan card of patient with id = {$this->_model->patients_id}");
    }
}
