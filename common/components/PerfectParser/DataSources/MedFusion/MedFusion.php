<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 30.05.18
 * Time: 16:08
 */

namespace common\components\PerfectParser\DataSources\MedFusion;


use common\components\PerfectParser\Common\Prototype\DataSource;
use common\components\PerfectParser\DataSources\MedFusion\api\api;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\RObservation;
use common\components\PerfectParser\DataSources\MedFusion\resources\RResource;
use common\components\PerfectParser\DataSources\MedFusion\resources\TInfoBlock;
use common\components\PerfectParser\Resources\RMedications;
use common\components\PerfectParser\DataSources\MedFusion\resources\RMedicationAdministration;
use common\components\PerfectParser\DataSources\MedFusion\resources\RMedicationDispense;
use common\components\PerfectParser\DataSources\MedFusion\resources\RMedicationOrder;
use common\components\PerfectParser\DataSources\MedFusion\resources\RMedicationStatement;
use common\components\PerfectParser\DataSources\MedFusion\resources\RAllergyIntolerance;
use common\components\PerfectParser\DataSources\MedFusion\resources\RPatient;
use common\components\PerfectParser\DataSources\MedFusion\resources\RCondition;
use common\components\PerfectParser\DataSources\MedFusion\resources\RProcedure;
use common\components\PerfectParser\DataSources\MedFusion\resources\RBundle;
use common\components\PerfectParser\DataSources\MedFusion\resources\RImmunization;
use common\components\PerfectParser\DataSources\MedFusion\resources\RDevice;
use common\components\PerfectParser\Resources\RAllergies;
use common\components\PerfectParser\Resources\REmergencyContacts;
use common\components\PerfectParser\Resources\RLabs;
use common\components\PerfectParser\Resources\ROtherPhysicians;
use common\components\PerfectParser\Resources\RPatientInfo;
use common\components\PerfectParser\Resources\RPractices;
use common\components\PerfectParser\Resources\RProblems;
use common\components\PerfectParser\Resources\RSurgicalHistory;
use common\components\PerfectParser\Resources\RVaccinations;
use common\models\MedfusionConnections;
use common\models\Partners;
use common\models\Patient;
use common\models\PatientInfo;
use common\models\Practices;
use common\models\Settings;
use yii\db\Query;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\web\ServerErrorHttpException;

/**
 * Class source
 * @package common\components\PerfectParser
 *
 * @property int $lastUpdated
 * @property \common\models\Patient $patient
 * @property string $jsParams
 */
class MedFusion extends DataSource
{
    /**
     * @inheritdoc
     */
const ID = 'MedFusion';

    /**
     * @inheritdoc
     */
const PARTNER_ID=Partners::PARTNER_MEDFUSION;

    /**
     * @const int seconds
     */
    const INTERVAL_ALLOW_MANUAL_UPDATE = 60 * 60;

    /** @var string[] List of rest api methods available for this data source */
    public $restMethodsAvailable = [];

    /** @var string[] List of rest api methods available for this data source in test mode */
    public $restTestMethodsAvailable = [
        self::METHOD_MED_INFO
    ];

    /** @var array $internalResourcesList List of internal resources (class names as array keys),
     *      related to external resources for current data source (string or array of class names as array values)
     */
    public $internalResourcesList = [
        RPractices::class           => RPatient::class,
        RPatientInfo::class         => RPatient::class,
        RAllergies::class           => RAllergyIntolerance::class,
        RProblems::class            => RCondition::class,
        REmergencyContacts::class   => RPatient::class,
        RSurgicalHistory::class     => RProcedure::class,
        RVaccinations::class        => RImmunization::class,
        RMedications::class => [
            RMedicationOrder::class,
            RMedicationDispense::class,
            RMedicationAdministration::class,
            RMedicationStatement::class,
            RBundle::class,
        ],
        RLabs::class                => RObservation::class,
        ROtherPhysicians::class     => RPatient::class,
    ];

    /**
     * API location
     * @var string
     */
    public $location;
    /**
     * API customer UUID
     * @var string
     */
    public $customerUUID;
    /**
     * API Key
     * @var string
     */
    public $apiKey;
    /**
     * API Client ID
     * @var string
     */
    public $clientID;
    /**
     * API Client Secret
     * @var string
     */
    public $clientSecret;

    /**
     * MedFusion API implementation
     * @var api
     */
    public $api;

    /**
     * Current Patient user UUID
     * @var string
     */
    public $patientUUID;

    /** @var TInfoBlock[] dirty data from MedFusion */
    private $externalData;

    /** @var \common\components\PerfectParser\DataSources\MedFusion\resources\RResource[] */
    private $externalResources;

    /**
     * @inheritdoc
     */
    public function init(){
        parent::init();

        if (\Yii::$app->perfectParser->isTest()) {
            \Yii::$app->setOutgoingMessages(\Yii::$app->perfectParser->testParams->notificationEnabled);
        } else {
            \Yii::$app->setOutgoingMessages(true);
        }

        $this->api = new api();
    }

    /**
     * Set current Patient
     *
     * @param Patient $patient
     * @param bool $forceCreation
     * @throws ServerErrorHttpException
     */
    public function setPatient($patient, $forceCreation = false)
    {
        $this->patientUUID = $patient->mf_uuid;
        if ($forceCreation){
            $this->api->users->selectUserUuid($patient);
        }
    }

    /**
     * Get Patient
     * @return Patient
     */
    public function getPatient()
    {
        return Patient::findOne(['mf_uuid' => $this->patientUUID]);
    }


    /**
     * Returns params for Javascript Connectors
     * @param Patient|null|true $patient
     *  - Patient instance - for retrieving params for given patient
     *  - TRUE for retrieving via admin's MF UUID
     *  - NULL for current patient
     * @return string
     * @throws \Exception
     */
    public function getJsParams($patient = null){
        try {
            if ($patient === true) {
                $this->patientUUID = Settings::get(Settings::MF_UUID_ADMIN);
            } else {
                if (is_null($patient)) {
                    $patient = \Yii::$app->patient->model;
                }
                \Yii::$app->perfectParser->setPatient($patient, true);
            }

            $params = array(
                'customerUuid' => \Yii::$app->perfectParser->dataSource->customerUUID,
                'userUuid' => $this->patientUUID,
                'accessToken' => $this->api->getUserToken(),
                'url' => 'https://' . \Yii::$app->perfectParser->dataSource->location,
                'apiKey' => \Yii::$app->perfectParser->dataSource->apiKey,
            );
            return Json::htmlEncode($params);
        } catch(\Exception $e){
/*            echo("<pre>");
            var_dump($e->getTraceAsString());
            var_dump($e->getMessage()); die();*/
            return false;
        }
    }

    /**
     * Checks whether is possible to get a new patient's data from MedFusion
     * @return boolean
     * @throws \Throwable
     */
    public function isNewDataAvailable()
    {
        if ($this->patientUUID) {
            try {
                if (MedfusionConnections::getConnectionChanges($this->patient)){
                    return true;
                } else {
                    $lastUpdated = $this->getLastUpdated();
                    if ($lastUpdated > time() - self::INTERVAL_ALLOW_MANUAL_UPDATE) {
                        return false;
                    }

                    $summaryHealthData = $this->api->healthData->getResourceSummaries();
                    foreach ($summaryHealthData as $resource) {
                        foreach ($resource['itemSummaries'] as $item) {
                            if ($item['modifiedTime']/1000 > $lastUpdated) {
                                return true;
                            }
                        }
                    }
                }
            } catch(\Exception $e) {
                \Yii::$app->perfectParser->error($e->getMessage());
            }
        }
        return false;
    }


    /**
     * Callback before import
     * @throws \Throwable
     */
    public function beforeImport()
    {
        if (!\Yii::$app->perfectParser->isTest()) {
            MedfusionConnections::getConnectionChanges(\Yii::$app->perfectParser->patient);
        }
    }

    /**
     * Callback after import
     * @throws \Exception
     */
    public function afterImport()
    {
        $this->rawSourceData = $this->api->healthData->apiLastResponse;
    }

    /**
     * Retrieve the time of last update of information from the MedFusion
     * @return int
     */
    public function getLastUpdated()
    {
        $last = (new Query())
            ->select('max(pi.last_updated)')
            ->from(PatientInfo::tableName().' pi')
            ->leftJoin(Practices::tableName().' pr', 'pi.practice_id = pr.practice_id')
            ->leftJoin(Partners::tableName().' pa', 'pr.partner_id = pa.partner_id')
            ->where(['pi.internal_id' =>\Yii::$app->perfectParser->patient->internal_id])
            ->andWhere(['pa.partner_id' => self::PARTNER_ID])
            ->scalar();

        return ($last ? strtotime($last) : 0);
    }

    /**
     * Get practice status by connection status
     * @param Practices $practice
     * @param bool $refresh
     *
     * @return string
     * @throws \Throwable
     */
    public function getPracticeStatus(Practices $practice, $refresh = false){
        $portal = (int)$practice->practice_umr_id;
        $model = MedfusionConnections::getConnection(\Yii::$app->perfectParser->patient, $portal, $refresh);
        return $model?$model->status:MedfusionConnections::STATUS_DISCONNECTED;
    }

    /**
     * Return class name for retrieving device resources for patient info
     * @return string
     * @throws Exception
     */
    public function getDeviceClass()
    {
        return RDevice::class;
    }

    /**
     * @param bool $debugJSON
     * @return bool
     */
    public function retriveExternalData($debugJSON = false)
    {
        $this->externalData = [];
        try {
            if ($debugJSON) {
                $data = \GuzzleHttp\json_decode($debugJSON, true);
            }
            else {
                $data = $this->api->healthData->getAllResources();
            }

            $data = $data ? $data : [];

            foreach ($data as $one) {
                $this->externalData[] = new TInfoBlock($one);
            }
        } catch(\Exception $e) {
            \Yii::$app->perfectParser->error('Retrive External Data Exception: '.$e->getMessage());
            Settings::set(Settings::MF_API_USE_CACHE, 0);
            return false;
        }
        Settings::set(Settings::MF_API_USE_CACHE, 1);
        return true;
    }

    /**
     * Load external data from TInfoBlock[]
     * @return bool
     */
    public function loadExternalData(){

        $this->externalResources = [];
        try{
            foreach ($this->externalData as $block){
                foreach ($this->extractResourcesDataFromBlock($block) as $resourceData){
                    $resourceName = $resourceData->getElement('resourceType', '');
                    foreach ($block->sourcePortalIds as $portalID){
                        /** @var $resourceModel RResource */
                        try{
                            $block_id = (isset($block->id))?$block->id->getValue():null;
                            $resourceModel = RResource::create($resourceName, null, $block_id);
                            if ($resourceModel){
                                $resourceModel->load($resourceData);
                                $resourceModel->portalID   = $portalID;
                                $resourceModel->updated_at = $block->modifiedTime;
                                $this->externalResources[] = $resourceModel;
                            } else {
                                \Yii::$app->perfectParser->log("Skip unknown resource: {$resourceName}");
                            }
                        } catch (\Throwable $e){
                            \Yii::$app->perfectParser->error('Load External Resource Exception: '.$e->getMessage());
                        }
                    }
                }
            }
        } catch(\Throwable $e){
            \Yii::$app->perfectParser->error('Load External Resources Exception: '.$e->getMessage());
        }
        return true;
    }

    /**
     * Load internal resources from external resources
     * @return bool
     */
    public function loadInternalData(){

        $this->internalResources = [];
        try{
            foreach (array_keys($this->internalResourcesList) as $className){
                if (class_exists($className)){
                    \Yii::$app->perfectParser->log('%yProcessing resource: '.StringHelper::basename($className).'%n');
                    \Yii::$app->perfectParser->incPrefix();
                    $resourceModel = new $className();
                    /** @var $resourceModel \common\components\PerfectParser\Resources\RResource */
                    $resourceModel->load($this->externalResources);
                    $this->internalResources[] = $resourceModel;
                    \Yii::$app->perfectParser->decPrefix();
                } else {
                    \Yii::$app->perfectParser->error("Class Not Found: ".$className);
                }
            }
        } catch(\Exception $e){
            \Yii::$app->perfectParser->error('Load Internal Resources Exception: '.$e->getMessage());
        }
        return true;
    }

    /**
     * @param TInfoBlock $block
     *
     * @return TArray[]
     */
    protected function extractResourcesDataFromBlock(TInfoBlock $block){
        return [$block->data];
    }

}
                                        