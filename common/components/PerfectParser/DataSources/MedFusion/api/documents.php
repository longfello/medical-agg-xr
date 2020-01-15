<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 08.09.18
 * Time: 23:01
 */

namespace common\components\PerfectParser\DataSources\MedFusion\api;


/**
 * Class documents
 * @package common\components\PerfectParser
 */
class documents extends prototype
{
    /**
     * Get documents
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function getDocuments()
    {
        $user_profile_id = $this->api->getUserProfileId();
        $response = $this->requestApi("users/".\Yii::$app->perfectParser->dataSource->patientUUID."/profiles/$user_profile_id/documents", "", self::GET, self::HEADER_BASE + self::HEADER_USER);

        return $response;
    }

    /**
     * Get document
     *
     * @param $documentId
     * @return mixed|string
     * @throws \Exception
     */
    public function getDocument($documentId)
    {
        $user_profile_id = $this->api->getUserProfileId();
        $response = $this->requestApi("users/".\Yii::$app->perfectParser->dataSource->patientUUID."/profiles/$user_profile_id/documents/$documentId/file", "", self::GET, self::HEADER_BASE + self::HEADER_USER + self::HEADER_CONTENT_TYPE_OCTET_STREAM + self::HEADER_DOCUMENT);

        return $response;
    }
}
