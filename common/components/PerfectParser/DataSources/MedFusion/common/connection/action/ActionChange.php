<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.03.18
 * Time: 15:22
 */

namespace common\components\PerfectParser\DataSources\MedFusion\common\connection\action;


use common\components\Notification;
use common\models\MedfusionConnections;

/**
 * Class ActionChange
 * @package common\components\PerfectParser
 */
class ActionChange extends prototype
{
    /**
     * @var
     */
    public $current_status;
    /**
     * @var
     */
    public $auth_error_registered;
    /**
     * @var
     */
    public $portalID;

    /**
     * @return string
     * @throws \Throwable
     */
    public function process(){
        switch ($this->current_status){
            case MedfusionConnections::STATUS_SUCCESS:
                if ($this->auth_error_registered == 1){
                    $this->setAuthError($this->portalID, 0);
                    Notification::success(Notification::KEY_MF_STATUS_CHANGED, $this->patient->patients_id, 'Premium data connection to "'.$this->getConnectionName().'" is now working correctly with validated credentials.');
                    $this->mailCredentialsOk();
                }
                break;
            case MedfusionConnections::STATUS_ERROR_USER_AUTH:
                $this->setAuthError($this->portalID, 1);
                Notification::error(Notification::KEY_MF_STATUS_CHANGED, $this->patient->patients_id, 'Premium data connection to "'.$this->getConnectionName().'" failed with invalid credentials. Please go back and fix them.');
                $this->mailCredentialsBad();
                break;
            default:
                // Notification::warning(Notification::KEY_MF_STATUS_CHANGED, $this->patient->patients_id, 'Premium data connection to "'.$this->getConnectionName().'" return status: '.MedfusionConnections::getStatusMessage($this->current_status));
        }
        return '';
    }
}