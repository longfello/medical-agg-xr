<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 08.01.18
 * Time: 10:32
 */

namespace common\components\PerfectParser\DataSources\MedFusion\api;


/**
 * Class healthData Implements MedFusion Health Data API
 * @package common\components\PerfectParser
 *
 * @property array $healthData
 */
/**
 * Class healthData
 * @package common\components\PerfectParser
 *
 * @property string|mixed $allResources
 * @property array $healthData
 */
class healthData extends prototype
{
    /**
     * Use all resources or not
     */
    const USE_ALL_RESOURCES = true;

    /**
     * Small set of resources
     * @var array
     */
    static private $resourcesA = array(
        'DEMOGRAPHICS', 'CONDITIONSv1',
    );
    /**
     * Full set of resources
     * @var array
     */
    static private $official_resourcesA = array(
        'DEMOGRAPHICS',
        'CONDITIONSv1',
        'PROCEDURES_FHIR_DSTU2', 'MEDICATIONSv2',
        'VITAL_SIGNSv1', 'IMMUNIZATIONSv1',
        'RESULTSv1', 'ALERTSv1',
        // 'APPOINTMENTSv1'
    );

    /**
     * Return all queried resources
     * @return array
     * @throws \Exception
     */
    public function getHealthData(){
        $this->api->clearCache();
        $resultsA = [];
        $resourcesA = self::USE_ALL_RESOURCES ? self::$official_resourcesA : self::$resourcesA;

        foreach ($resourcesA as $resource){
            $resultsA[$resource] = $this->getResource($resource);
        }
        return $resultsA;
    }

    /**
     * Recive queried all resource information from MedFusion
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function getAllResources()
    {
        $user_profile_id = $this->api->getUserProfileId();
        $response = $this->requestApi("users/".\Yii::$app->perfectParser->dataSource->patientUUID."/profiles/{$user_profile_id}/resources", null, self::GET, self::HEADER_BASE + self::HEADER_USER, true);
        return $response;
    }

    /**
     * Recive queried resource information from MedFusion
     * @param $resource
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function getResource($resource)
    {
        $user_profile_id = $this->api->getUserProfileId();
        $response = $this->requestApi("users/".\Yii::$app->perfectParser->dataSource->patientUUID."/profiles/{$user_profile_id}/resources", ['resourceType' => $resource], self::GET, self::HEADER_BASE + self::HEADER_USER);
        return $response;
    }

    /**
     * Receive queried summary resource information from MedFusion
     * @param string|null $resource for getting info, null for all possible resources
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function getResourceSummaries($resource = null)
    {
        $user_profile_id = $this->api->getUserProfileId();
        $response = $this->requestApi(
            "users/".\Yii::$app->perfectParser->dataSource->patientUUID."/profiles/{$user_profile_id}/resources/summaries",
            ($resource ? ['resourceType' => $resource] : null),
            self::GET,
            self::HEADER_BASE + self::HEADER_USER
        );
        return $response;
    }

}
