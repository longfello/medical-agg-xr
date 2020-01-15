<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 08.01.18
 * Time: 10:29
 */

namespace common\components\PerfectParser;


use common\components\PerfectParser\Common\Importer;
use common\components\PerfectParser\Common\Traits\DebugTrait;
use common\components\PerfectParser\Common\Traits\ReportTrait;
use common\components\PerfectParser\Common\UpdateResults;

use common\components\PerfectParser\Common\Prototype\DataSource;
use common\components\PerfectParser\DataSources\CCDA\CCDA;
use common\components\PerfectParser\DataSources\EMR\EMR;
use common\components\PerfectParser\DataSources\HL7\HL7;
use common\components\PerfectParser\DataSources\MedFusion\MedFusion;
use common\components\TestParameters;
use common\models\MedfusionConnections;
use common\models\MedfusionParseLog;
use common\models\Patient;
use common\models\PatientInfo;
use common\models\Practices;
use yii\base\Component;
use yii\base\Exception;
use yii\web\HttpException;


/**
 * MedFusion Component
 * @property string $jsParams
 * @property string $umrId
 * @property \common\models\Patient $patient
 * @property Importer $importer
 * @property string $errorsAsList
 *
 * @property MedfusionParseLog $parseLog
 */
class PerfectParser extends Component
{
    use DebugTrait, ReportTrait;

    /** @const string Test environment */
    const ENV_TEST = 'test';

    /** @const string Normal environment */
    const ENV_NORMAL = 'normal';

    /** @var string Processing environment */
    public $environment = self::ENV_NORMAL;

    /**
     * MedFusion Parser Update Results
     * @var UpdateResults
     */
    public $updateResults;

    /**
     * @var array $errors
     */
    public $errors = [];

    /**
     * @var string $parseLogContent
     */
    public $parseLogContent = '';

    /**
     * @var string[] $parseLogTitles
     */
    public $parseLogTitles = [];

    /**
     * @var string $parseLogTitleId
     */
    public $parseLogTitleId = 0;

    /** @var integer|null $lastParseLogRequestId */
    public $lastParseLogId;

    /** @var TestParameters $testParams */
    public $testParams;

    /** @var DataSource|MedFusion|HL7|CCDA|null DataSource class */
    public $dataSource;

    /** @var bool Perform additional check of the file structure before import */
    public $strictImport = false;

    /** @var bool */
    public $notifyPatientAboutChanges = true;

    /**
     * @var MedfusionParseLog $_parseLog
     */
    private $_parseLog;

    /**
     * Current Patient user
     * @var Patient|null
     */
    private $_patient;

    /**
     * @var array
     */
    protected $availableDataSources = [
        MedFusion::class,
        CCDA::class,
        HL7::class,
        EMR::class,
    ];

    /**
     * @inheritdoc
     * @throws
     */
    public function init(){
        if (!\Yii::$app->request->isConsoleRequest) {
            if (!\Yii::$app->patient->isGuest) {
                $this->setPatient(\Yii::$app->patient->model);
            }
        }
        $this->testParams = new TestParameters();
        if ($this->testParams->isPythonTest){
            $this->setEnvironment(self::ENV_TEST);
            if(strtolower(getenv('ALLOW_MF_TEST_INTERFACE')) != 'true'){
                throw new HttpException(403,"Test access denied by environment", 403);
            }
        }
    }

    /**
     * Setter for processing environment
     * @param string
     * @throws HttpException
     */
    public function setEnvironment($environment){
        $this->environment = $environment;
        if ($this->environment == self::ENV_TEST){
            if(strtolower(getenv('ALLOW_MF_TEST_INTERFACE')) != 'true'){
                throw new HttpException(403,"Test access denied by environment", 403);
            }
        }
    }

    /**
     * Setter for processing environment
     * @param string
     * @throws Exception
     */
    public function setDataSource($dataSourceID){
        $this->dataSource = $this->getDataSource($dataSourceID);
        if (!$this->dataSource){
            throw new Exception("Data source not found: ".$dataSourceID);
        }
        if ($this->_patient){
            $this->dataSource->setPatient($this->_patient);
        }
    }

    /**
     * Return true if current processing environment is test
     * @return bool
     */
    public function isTest(){
        return $this->environment == self::ENV_TEST;
    }

    /**
     * Set current Patient
     *
     * @param Patient $patient
     * @param bool $forceCreation
     *
     * @throws \yii\web\ServerErrorHttpException
     */
    public function setPatient(Patient $patient, $forceCreation = false){
        $this->updateResults = new UpdateResults();
        $this->_patient = $patient;
        if ($this->dataSource){
            $this->dataSource->setPatient($patient, $forceCreation);
        }
    }

    /**
     * Get current Patient
     *
     * @return Patient|null
     * @throws Exception
     */
    public function getPatient(){
        if (!$this->_patient){
            $this->_patient = $this->dataSource->getPatient();
        }
        return $this->_patient;
    }

    /**
     *
     * @param string $portalId
     * @param boolean $forcePatientInfoCreation
     * @return Practices | null
     */
    public function getPractice($portalId, $forcePatientInfoCreation = false)
    {
        $practice = Practices::findOne(['practice_umr_id' => $portalId]);
        if ($practice) {
            if ($forcePatientInfoCreation) {
                $this->createPatientInfo($practice);
            }
            return $practice;
        }
        return null;
    }

    /**
     * Get import object
     *
     * @return Importer
     */
    public function getImporter(){
        return new Importer();
    }

    /**
     * Getter for parse log AR model
     * @return MedfusionParseLog
     */
    public function getParseLog()
    {
        if (empty($this->_parseLog)) {
            $this->_parseLog = new MedfusionParseLog();
        }
        return $this->_parseLog;
    }

    /**
     * Switch to new log
     */
    public function parseLogNext()
    {
        $this->_parseLog = new MedfusionParseLog();
    }

    /**
     *
     * @param string|integer $practice_identification_string
     * @param string $name
     * @param string $phone
     * @param integer $isDemo 0 | 1
     * @param boolean $forcePatientInfoCreation
     *
     * @return Practices | null
     * @throws \Exception
     */
    public function createPractice($practice_identification_string, $name = '', $phone = '', $isDemo = 0, $forcePatientInfoCreation = false)
    {
        $practice = new Practices;
        $practice->fill();
        $practice->practice_umr_id = (string)$practice_identification_string;
        $practice->auth_user       = 'user_' . $practice_identification_string;
        $practice->practice_name   = (string) $name;
        $practice->cell_phone      = $phone;
        $practice->partner_id      = $this->dataSource::PARTNER_ID;
        $practice->demo            = $isDemo;

        if($name == ''){
            $portal = MedfusionConnections::find()
                ->where(['portal_id' => $practice->practice_umr_id])
                ->andWhere(['not',['portal_name'=>null]])
                ->one();
            /** @var MedfusionConnections $portal */
            $practice->practice_name = (!empty($portal))?$portal->portal_name:'';
        }

        if ($practice->save()) {
            if ($forcePatientInfoCreation) {
                $this->createPatientInfo($practice);
            }
            return $practice;
        }

        $this->error($practice->errors);
        return null;
    }

    /**
     *
     * @param Practices $practice
     * @return boolean
     */
    public function createPatientInfo(Practices $practice)
    {
        $patient = \Yii::$app->perfectParser->patient;
        $practicePatientInfo = PatientInfo::find()
            ->where([
                'internal_id' => $patient->internal_id,
                'practice_id' => $practice->practice_id
            ])
            ->one();

        if ($practicePatientInfo) {
            return true;
        } else {
            $practicePatientInfo = new PatientInfo([
                'patients_id' => $patient->patients_id,
                'internal_id' => $patient->internal_id,
                'practice_id' => $practice->practice_id
            ]);
            return $practicePatientInfo->save();
        }
    }

    /**
     * Import and apply data from data source
     *
     * @param bool|string $debugData
     * @param bool $strict Perform additional check of the file structure before import
     *
     * @return bool Is import proceed successfuly
     * @throws \Throwable
     */
    public function import($debugData = false, $strict = false)
    {
        $this->log("Choose patient to process", true);
        $this->log("Processing Patient with internal_id = '{$this->patient->internal_id}'");

        $this->strictImport = $strict;
        $this->dataSource->beforeImport();
        $result = $this->importer->import($debugData);
        $this->dataSource->afterImport();

        $processedData = ($debugData ? $debugData : \Yii::$app->perfectParser->dataSource->rawSourceData);
        $this->sendReport($processedData, $this->errors);
        $this->lastParseLogId = $this->saveParseLog();

        return $result;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return (bool) $this->errors;
    }

    /**
     * Get parse errors as html-formatted list
     * @return string
     */
    public function getErrorsAsList()
    {
        $list = '';
        if (count($this->errors)) {
            $list .= '<ul>';
            foreach ($this->errors as $one) {
                $list .= '<li>'.$one['error'].'</li>';
            }
            $list .= '</ul>';
        }

        return $list;
    }

    /**
     * @param string $id DataSourceID
     *
     * @return DataSource | null
     */
    private function getDataSource($id){
        foreach($this->availableDataSources as $class){
            if (constant($class."::ID") == $id){
                return new $class;
            }
        }
        return null;
    }

}