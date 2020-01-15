<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 30.05.18
 * Time: 15:57
 */

namespace common\components\PerfectParser\Common\Prototype;


use common\components\PerfectParser\Resources\RResource;
use common\models\Patient;
use common\models\Practices;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\StringHelper;

/**
 * Class DataSource Base class for all DataSources
 * @package common\components\PerfectParser
 *
 * @property void $lastUpdated
 * @property \common\models\Patient|void $patient
 */
class DataSource extends BaseObject
{
    /** @const string DataSource identifier */
    const ID=null;
    /** @const int PartnerID */
    const PARTNER_ID=null;

    /** const string Rest Method MedInfo identification string */
    const METHOD_MED_INFO = 'med-info';
    /** const string Rest Method PreEnrollment identification string */
    const METHOD_PRE_ENROLLMENT = 'pre-enrollment';
    /** const string Rest Method CheckUpdate identification string */
    const METHOD_CHECK_UPDATE = 'check-update';
    /** const string Rest Method ManualUpdate identification string */
    const METHOD_MANUAL_UPDATE = 'manual-update';
    /** const string Rest Method Frame identification string */
    const METHOD_FRAME = 'frame';

    /** @var string DataSource name */
    protected $name;

    /** @var string[] List of rest api methods available for this data source */
    public $restMethodsAvailable = [
        self::METHOD_PRE_ENROLLMENT,
        self::METHOD_MED_INFO
    ];

    /** @var string[] List of rest api methods available for this data source in test mode */
    public $restTestMethodsAvailable = [
        self::METHOD_PRE_ENROLLMENT,
        self::METHOD_MED_INFO
    ];

    /** @var array $internalResourcesList List of internal resources (class names as array keys),
     *      related to external resources for current data source (string or array of class names as array values)
     */
    public $internalResourcesList;

    /** @var RResource[] */
    public $internalResources = [];

    /** @var string */
    public $rawSourceData;

    /**
     * @var bool
     * Determines behavior for processing existing patient's records -
     * for all practices of current practice partner (TRUE) or only current practice (FALSE)
     */
    public $processAllPractices;

    /**
     * @var Practices
     */
    public $sourcePractice;


    /**
     * @inheritdoc
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        $class = get_called_class();
        if (empty(constant($class.'::ID'))) throw new Exception("{$class}::ID required");
        if (empty(constant($class.'::PARTNER_ID'))) throw new Exception("{$class}::PARTNER_ID required");
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init(){
        parent::init();
        // initialize the data source with the configuration loaded from config.php
        \Yii::configure($this, require __DIR__ . '/../../DataSources/'.StringHelper::basename(get_called_class()).'/config.php');
        $this->restMethodsAvailable[] = 'help';
        $this->restTestMethodsAvailable[] = 'help';
    }

    /**
     * Checks whether is possible to get a new patient's data from MedFusion
     * @return void
     * @throws Exception
     */
    public function isNewDataAvailable(){
        throw new Exception("Implementation needed");
    }

    /**
     * Return name of DataSource
     * @return string
     */
    public function getName(){
        if ($this->name) return $this->name;
        return constant(get_called_class().'::ID');
    }

    /**
     * Set name of DataSource
     * @param string $name
     */
    public function setName($name){
        $this->name = $name;
    }

    /**
     * Retrieve the time of last update of information from the MedFusion
     * @return void
     * @throws Exception
     */
    public function getLastUpdated()
    {
        throw new Exception("Implementation needed");
    }

    /**
     * Return class name for retrieving device resources for patient info
     * @throws Exception
     */
    public function getDeviceClass()
    {
        throw new Exception("Implementation needed");
    }

    /**
     * Callback before import
     * @throws \Exception
     */
    public function beforeImport()
    {
    }

    /**
     * Callback after import
     */
    public function afterImport()
    {
    }

    /**
     * Set current Patient
     *
     * @param Patient $patient
     * @param bool $forceCreation
     */
    public function setPatient($patient, $forceCreation = false)
    {
    }

    /**
     * Get Patient
     * @throws Exception
     */
    public function getPatient()
    {
        throw new Exception("Implementation needed");
    }

    /**
     * @param bool $debugData
     *
     * @return void
     * @throws Exception
     */
    public function retriveExternalData($debugData = false){
        throw new Exception("Implementation needed");
    }

    /**
     * Load external data from TInfoBlock[]
     * @return void
     * @throws Exception
     */
    public function loadExternalData(){
        throw new Exception("Implementation needed");
    }

    /**
     * Load internal resources from external resources
     * @return void
     * @throws Exception
     */
    public function loadInternalData(){
        throw new Exception("Implementation needed");
    }

    /**
     * Auth Data Source practice
     * @return false|Practices
     */
    public function auth(){
        return false;
    }
}
