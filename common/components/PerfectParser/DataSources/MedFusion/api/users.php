<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 08.01.18
 * Time: 10:31
 */

namespace common\components\PerfectParser\DataSources\MedFusion\api;


use common\models\DeletedMedfusionProviders;
use common\models\Patient;
use yii\web\ServerErrorHttpException;

/**
 * Class users Implements MedFusion Users API
 * @package common\components\PerfectParser
 *
 * @property mixed $profileId
 */
class users extends prototype
{
    /**
     * Return patient UUID and set Patient for using in medFusion - register patient on MedFusion if not registred
     * @param $patient Patient|null
     *
     * @return string
     * @throws ServerErrorHttpException
     * @throws \Exception
     */
    public function selectUserUuid($patient = null)
    {
        if ($patient) {
            /** @var $patient Patient */
            if (!$patient->mf_uuid){
                $this->registerUser($patient);
            }
            \Yii::$app->perfectParser->setPatient($patient);
        }

        return \Yii::$app->perfectParser->dataSource->patientUUID;
    }

    /**
     * Register Patient In MedFusion
     * @param Patient $patient
     *
     * @return bool
     *
     * @throws ServerErrorHttpException
     * @throws \Exception
     */
    public function registerUser(Patient $patient)
    {
        try {
            $response = $this->requestApi("users", "", self::POST, self::HEADER_BASE + self::HEADER_CUSTOMER);

            if ($response['uuid']) {
                $patient->mf_uuid = $response['uuid'];
                $patient->save();
                return true;
            }
        }
        catch (\Exception $e) {
            throw new ServerErrorHttpException($e->getMessage());
        }

        throw new \Exception("user_id not set");
    }

    /**
     * Completle unsubscribe Patient from MedFusion
     * @param bool $silent Hide progress messages
     * @return bool Result
     */
    public function unsubscribe($silent = false){
        try {
            $connections = \Yii::$app->perfectParser->dataSource->api->connections->getConnections();
            foreach ($connections as $connection){
                if (isset($connection['providers']) && is_array($connection['providers'])){
                    foreach($connection['providers'] as $provider){
                        if (!$silent) { echo ("     removing provider {$provider['providerId']}\r\n"); }
                        $patient = \Yii::$app->perfectParser->getPatient();
                        $logModel = new DeletedMedfusionProviders();
                        $logModel->source_id = $provider['providerId'];
                        $logModel->internal_id = $patient->internal_id;
                        $logModel->provider_name = isset($provider['directoryLocation']['name'])?$provider['directoryLocation']['name']:'[unknown]';
                        $logModel->doctor_name = $provider['nameAlias'];
                        $logModel->save();

                        if (!$this->unsubscribeProvider($connection['id'], $provider['providerId'])){
                            return false;
                        }
                    }
                }
            }
            return true;
        }
        catch (\Exception $e) {
            $this->error($e->getMessage(), true);
        }
        return false;
    }

    /**
     * Unsubscribe selected info providers
     * @param $connectionId
     * @param $providerId
     *
     * @return bool
     */
    public function unsubscribeProvider($connectionId, $providerId){
        try {
            $this->requestApi("users/".\Yii::$app->perfectParser->dataSource->patientUUID."/connections/{$connectionId}/providers/{$providerId}", "", self::DELETE, self::HEADER_BASE + self::HEADER_USER);
            return true;
        } catch (\Exception $e) {
            $this->error($e->getMessage(), true);
        }
        return false;
    }

    /**
     * Get default user profile ID
     * @return mixed
     * @throws \Exception
     */
    public function getProfileId(){
        $response = $this->requestApi("users/".\Yii::$app->perfectParser->dataSource->patientUUID."/profiles", "", self::GET, self::HEADER_BASE + self::HEADER_USER);
        $response = isset($response[0])?$response[0]:[];
        $data = $this->parseResponse($response, ['id' => 'user_profile_id', 'name' => 'user_name', 'zip' => 'user_zip']);

        if (! $data['user_profile_id'])
            throw new \Exception("profile_id was not set as expected");

        return $data['user_profile_id'];

    }
}