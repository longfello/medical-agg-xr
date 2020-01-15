<?php
namespace common\components\NotificationManager\messages\callbacks;

use common\components\NotificationManager\components\BaseCallback;

/**
 * Class AlertOnScanSended
 * @package common\components\NotificationManager\messages\callbacks
 */
class AlertOnScanSended extends BaseCallback
{
    /**
     * {@inheritdoc}
     */
    protected function _run()
    {
        echo ("Patient with id = {$this->_model->patients_id} has been alerted about scan");
    }
}
