<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 08.01.18
 * Time: 10:31
 */

namespace common\components\PerfectParser\DataSources\MedFusion\api;


/**
 * Class tokens Implements MedFusion Tokens API
 * @package common\components\PerfectParser
 */
/**
 * Class tokens
 * @package common\components\PerfectParser
 */
class tokens extends prototype
{
    /**
     * Create customer token
     * @return array|mixed|string
     * @throws \Exception
     */
    public function createCustomerToken(){
        $params = array( 'clientId' => \Yii::$app->perfectParser->dataSource->clientID, 'clientSecret' => \Yii::$app->perfectParser->dataSource->clientSecret );
        $response = $this->requestApi('tokens', $params, self::POST, self::HEADER_BASE + self::HEADER_CONTENT_TYPE);
        return $this->parseResponse($response, 'token');
    }

    /**
     * Create User Token for given UUID
     * @param $user_uuid
     *
     * @return array|mixed|string
     * @throws \Exception
     */
    public function createUserToken($user_uuid){
        $response = $this->requestApi("users/" . $user_uuid ."/tokens", "", self::POST, self::HEADER_BASE + self::HEADER_CUSTOMER);
        return $this->parseResponse($response, 'accessToken');
    }
}