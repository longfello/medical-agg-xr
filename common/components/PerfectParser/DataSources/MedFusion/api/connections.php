<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 08.01.18
 * Time: 10:31
 */

namespace common\components\PerfectParser\DataSources\MedFusion\api;


/**
 * Class connections Implements MedFusion Connections API
 * @package common\components\PerfectParser
 *
 * @property string|array|mixed $connections
 */
/**
 * Class connections
 * @package common\components\PerfectParser
 */
class connections extends prototype
{
    /**
     * Default Cache Duration
     */
    const CACHE_DURATION = 5;

    /**
     * Return current user connections
     *
     * @param $useCache bool
     * @return array|mixed|string
     * @throws \Exception
     */
    public function getConnections($useCache = true){
        $connections = false;
        if ($useCache) {
            $connections = $this->getCache(\Yii::$app->perfectParser->dataSource->patientUUID);
        }
        if (!$connections){
            $connections = $this->requestApi("users/".\Yii::$app->perfectParser->dataSource->patientUUID."/connections", "", self::GET, self::HEADER_BASE + self::HEADER_USER);
            $connections = is_array($connections)?$connections:[];
            if ($useCache) {
                $this->setCache(\Yii::$app->perfectParser->dataSource->patientUUID, $connections, self::CACHE_DURATION);
            }
        }
        return $connections;
    }

    /**
     * Return one connection, specified by id
     * @param $connectionId int
     * @param $useCache bool
     * @return array|mixed|string
     * @throws \Exception
     */
    public function getConnection($connectionId, $useCache = true){
        $connection = false;
        $cacheKey   = ['uuid' => \Yii::$app->perfectParser->dataSource->patientUUID, 'id' => $connectionId];
        if ($useCache) {
            $connection = $this->getCache($cacheKey);
        }
        if (!$connection){
            $connection = $this->requestApi("users/".\Yii::$app->perfectParser->dataSource->patientUUID."/connections/".$connectionId, "", self::GET, self::HEADER_BASE + self::HEADER_USER);
            $connection = is_array ($connection) ? $connection : [];
            if ($useCache) {
                $this->setCache($cacheKey, $connection, self::CACHE_DURATION);
            }
        }
        return $connection;
    }

}
