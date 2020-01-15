<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 08.01.18
 * Time: 10:31
 */

namespace common\components\PerfectParser\DataSources\MedFusion\api;


use common\components\PerfectParser\Common\Traits\DebugTrait;
use common\models\MedfusionApiRequestsLog;
use yii\base\BaseObject;
use yii\helpers\BaseInflector;
use yii\helpers\Json;

/**
 * Class prototype - custom class for MedFusion API
 * @package common\components\PerfectParser
 *
 * @property mixed $cache
 */
class prototype extends BaseObject
{
    use DebugTrait;

    /**
     * Default Cache Duration
     */
    const CACHE_DURATION = 5;
    /**
     * Append Basic headers
     */
    const HEADER_BASE         = 1;
    /**
     * Append Customer Token Headers
     */
    const HEADER_CUSTOMER     = 2;
    /**
     * Append User Token Headers
     */
    const HEADER_USER         = 4;
    /**
     * Append Content-type=json
     */
    const HEADER_CONTENT_TYPE = 8;
    /**
     * Append Content-type=octet-stream
     */
    const HEADER_CONTENT_TYPE_OCTET_STREAM = 16;
    /**
     * Append Documents header  "Accept: application/octet-stream"
     */
    const HEADER_DOCUMENT = 32;

    /**
     * Method DELETE
     */
    const DELETE = 'DELETE';
    /**
     * Method GET
     */
    const GET    = 'GET';
    /**
     * Method POST
     */
    const POST   = 'POST';
    /**
     * Method PUT
     */
    const PUT    = 'PUT';
    /**
     * Method HEAD
     */
    const HEAD   = 'HEAD';

    /**
     * Basic auth prefix
     */
    const AUTH_PREFIX         = 'Authorization: Bearer ';

    /** @var api Api base object */
    public $api;

    /**
     * @var int last api call HTTP code
     */
    public $apiLastHttpCode;
    /**
     * @var string last api call HTTP response body
     */
    public $apiLastResponse;

    /**
     * @var int PK of current log record
     */
    public $requestLogId;

    /**
     * Send Request To MedFusion API
     * @param $function string
     * @param array $params
     * @param string $method
     * @param int $headersOrigin
     * @param $toParseLog bool set log model as primary to parsing log foriegn key
     *
     * @return mixed|string
     * @throws \Exception
     */
    protected function requestApi($function, $params = [], $method = self::POST, $headersOrigin = self::HEADER_BASE, $toParseLog = false){

        $content = '';

        if (\Yii::$app->perfectParser->dataSource->customerUUID && !\Yii::$app->perfectParser->isTest()){
            $headers = $this->compileHeaders($headersOrigin);
            $url = "https://". \Yii::$app->perfectParser->dataSource->location ."/v1/customers/". \Yii::$app->perfectParser->dataSource->customerUUID ."/".$function;

            $startTime = microtime(true);
            try{
                $content = $this->CURL_Request($url, $method, $headers, $params);
            } catch (\Exception $e){
                $this->error($e->getMessage());
            }
            $endTime = microtime(true);

            $parseLog = \Yii::$app->perfectParser->parseLog;
            $request_id = MedfusionApiRequestsLog::add([
                'patient_id'    => \Yii::$app->perfectParser->patient->patients_id,
                'patient_uuid'  => \Yii::$app->perfectParser->patient->mf_uuid,
                'url'           => $url,
                'method'        => $method,
                'headers'       => json_encode($headers),
                'data'          => json_encode($params),
                'http_code'     => $this->apiLastHttpCode,
                'response'      => ($this->apiLastResponse ? $this->apiLastResponse : '[empty]'),
                'response_time' => round($endTime-$startTime, 4),
            ]);

            if ($toParseLog) {
                $parseLog->request_id = $request_id;
            }

            // use json decode for not octet stream type
            if (!($headersOrigin & self::HEADER_CONTENT_TYPE_OCTET_STREAM)) {
                try{
                    $content = Json::decode($content);
                } catch (\Exception $e){
                    throw new \Exception("Error: Exiting due to {$function} returning $content. Error message was: ".$e->getMessage());
                }
            }
        }
        return $content;
    }

    /**
     * Set parameters and run CURL request
     * @param $url
     * @param string $method
     * @param string $headers
     * @param string $params
     * @param string $port
     *
     * @return mixed|string
     * @throws \yii\base\Exception
     */
    protected function CURL_Request($url, $method = self::GET, $headers = "", $params = "", $port = '') {
        $curl = curl_init();

        if (is_array($params)) {
            if ($method === 'GET')
                $url .= "?" .  $this->array2url($params);
            else
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params) );
        }
        $url = trim($url, '&');
        $this->log("%g{$method} {$url}\r\nRequest params: ".json_encode($params)."%n");

        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ));

        if ($port) curl_setopt($curl, CURLOPT_PORT, $port);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $this->apiLastHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->apiLastResponse = $response;

        if ($err) {
            $errorMessage = "Received curl error: {$this->apiLastHttpCode} $err\r\n$response";
            $this->error($errorMessage);
            $response = "{}";

            // send emergency mail
            MedfusionApiRequestsLog::sendEmergencyMail($errorMessage);
        } else {
            if ($this->apiLastHttpCode != 200) {
                $errorMessage = "Received curl error: {$this->apiLastHttpCode} $err\r\n$response";
                $this->error($errorMessage);
                $response = "{}";

                // send emergency mail
                MedfusionApiRequestsLog::sendEmergencyMail($errorMessage);
            }
        }

        return $response;
    }

    /**
     * Convert arrays to URL params
     * @param $arr
     *
     * @return bool|string
     */
    protected function array2url($arr){
        if (!is_array($arr)){
            return false;
        }
        $query = '';
        foreach($arr as $key => $value){
            $query .= $key . "=" . $value . "&";
        }
        return $query;
    }

    /**
     * Extract from Api Responses needed values
     * @param $content
     * @param $keys
     *
     * @return array|string|mixed
     * @throws \Exception
     */
    protected function parseResponse($content, $keys){
        // reset expected_valsA on each call
        $expected_valsA = [];

        if (is_array($keys)){
            foreach ($keys as $json_key => $api_key) {
                if (!isset($content[$json_key])) {
                    throw new \Exception("$api_key not received as $json_key in json response");
                }
                $expected_valsA[$api_key] = $content[$json_key];
            }
            return $expected_valsA;
        }

        if (is_string($keys)){
            if (isset($content[$keys])) {
                return $content[$keys];
            }
        }

        throw new \Exception("Bad expected keys given");
    }


    /**
     * Compile headers for Api Requests
     * @param $headers
     *
     * @return array
     * @throws \Exception
     */
    private function compileHeaders($headers){
        $result = [];
        if ($headers & self::HEADER_BASE){

            $mimeType = "application/json";
            if ($headers & self::HEADER_DOCUMENT){
                $mimeType = "application/octet-stream";
            }

            $result = [
                "mimeType: {$mimeType}",
                "x-api-key: ". \Yii::$app->perfectParser->dataSource->apiKey
            ];
        }
        if ($headers & self::HEADER_CUSTOMER){
            $result[] = self::AUTH_PREFIX . $this->api->getCustomerToken();
        }
        if ($headers & self::HEADER_USER){
            $result[] = self::AUTH_PREFIX . $this->api->getUserToken();
        }
        if ($headers & self::HEADER_CONTENT_TYPE_OCTET_STREAM){
            $result[] = "content-type: application/octet-stream";
        } else {
            $result[] = "content-type: application/json";
        }
        return $result;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    protected function getCache($key){
        return \Yii::$app->cache->get($this->getCacheKey($key));
    }

    /**
     * @param $key
     * @param $value
     * @param null $duration
     *
     * @return bool
     */
    protected function setCache($key, $value, $duration = null){
        return \Yii::$app->cache->set($this->getCacheKey($key), $value, $duration);
    }

    /**
     * @param $key
     *
     * @return string
     */
    protected function getCacheKey($key){
        return  'MF'.BaseInflector::camelize(get_called_class()).'-'.BaseInflector::camelize(json_encode($key));
    }
}