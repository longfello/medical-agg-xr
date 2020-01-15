<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 08.01.18
 * Time: 10:30
 */

namespace common\components\PerfectParser\DataSources\MedFusion\api;


use yii\base\BaseObject;
use common\models\Settings;

/**
 * Class api Implements MedFusion API
 * @package common\components\PerfectParser
 *
 * @property string $userToken
 * @property string $customerToken
 * @property string $userProfileId
 */
class api extends BaseObject
{
    /**
     * Common cache prefix
     */
    const CACHE_KEY_PREFIX      = 'MF_API_';
    /**
     * Cache prefix for customer token
     */
    const CACHE_CUSTOMER_PREFIX = self::CACHE_KEY_PREFIX . 'CUSTOMER';
    /**
     * Cache prefix for user token
     */
    const CACHE_USER_PREFIX     = self::CACHE_KEY_PREFIX . 'USER_';
    /**
     * Common caching duration 2 hours without 60 seconds
     */
    const CACHE_DURATION        = 2 * 60 * 60 - 60;

    /**
     * @var connections Connections API implementation
     */
    public $connections;

    /**
     * @var documents Documents API implementation
     */
    public $documents;

    /**
     * @var directory Directory API implementation
     */
    public $directory;
    /**
     * @var healthData Health Data API implementation
     */
    public $healthData;
    /**
     * @var tokens Tokens API implementation
     */
    public $tokens;
    /**
     * @var users Users API implementation
     */
    public $users;

    /**
     * @var string Customer Token cache
     */
    protected $_customer_token;
    /**
     * @var string[] User Profile Id cache
     */
    protected $_user_profile_id = [];
    /**
     * @var string[] User Token cache
     */
    protected $_user_token = [];


    /**
     * @inheritdoc
     */
    public function init(){
        $this->documents = new documents(['api' => $this]);
        $this->connections = new connections(['api' => $this]);
        $this->directory = new directory(['api' => $this]);
        $this->healthData = new healthData(['api' => $this]);
        $this->tokens = new tokens(['api' => $this]);
        $this->users = new users(['api' => $this]);
    }

    /**
     * Getter for Customer Token
     * @return string
     * @throws \Exception
     */
    public function getCustomerToken(){
        /** @noinspection PhpUndefinedClassInspection */
        if (!$token = \Yii::$app->cache->get(self::CACHE_CUSTOMER_PREFIX)){
            $token = $this->tokens->createCustomerToken();
            \Yii::$app->cache->set(self::CACHE_CUSTOMER_PREFIX, $token, self::CACHE_DURATION);
        }
        return $token;
    }

    /**
     * Getter for User Token
     * @return string
     * @throws \Exception
     */
    public function getUserToken()
    {
        $cache_key = self::CACHE_USER_PREFIX.\Yii::$app->perfectParser->dataSource->patientUUID;
        if (!Settings::get(Settings::MF_API_USE_CACHE) || (!$token = \Yii::$app->cache->get($cache_key))) {
            $token = $this->tokens->createUserToken(\Yii::$app->perfectParser->dataSource->patientUUID);

            if ($token) {
                \Yii::$app->cache->set($cache_key, $token, self::CACHE_DURATION);
                Settings::set(Settings::MF_API_USE_CACHE, 1);
            } else {
                Settings::set(Settings::MF_API_USE_CACHE, 0);
            }
        }
        return $token;
    }

    /**
     * Getter for User Profile ID
     * @return string
     * @throws \Exception
     */
    public function getUserProfileId(){
        if (!isset($this->_user_profile_id[\Yii::$app->perfectParser->dataSource->patientUUID])){
            $this->_user_profile_id[\Yii::$app->perfectParser->dataSource->patientUUID] = $this->users->getProfileId();
        }
        return $this->_user_profile_id[\Yii::$app->perfectParser->dataSource->patientUUID];
    }

    /**
     * Truncate cached tokens
     */
    public function clearCache(){
        $this->_user_profile_id = [];
    }

}