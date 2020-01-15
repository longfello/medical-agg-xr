<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 17:15
 */

namespace common\components\PerfectParser\Resources;


use common\components\PerfectParser\Scenario\ScenarioAction;
use common\components\PerfectParser\Common\Traits\DebugTrait;
use common\models\Allergies;
use common\models\Conditions;
use common\models\EmergencyContacts;
use common\models\Medications;
use common\models\Patient;
use common\models\PatientInfo;
use common\models\Practices;
use common\models\SurgicalHistory;
use common\models\Vaccinations;
use yii\base\BaseObject;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\base\Model;

/**
 * Class RResource
 * @package common\components\PerfectParser
 */
class RResource extends BaseObject
{
    use DebugTrait;

    /**
     * @var bool
     */
    public $processScenario = true;

    /** @var Patient Current patient */
    public $patient;

    /** @var string class name with namespace */
    public $modelClass;

    /** @var string Field which identity patient */
    public $patientIdentityColumn = 'patients_id';

    /** @var Allergies|Conditions|EmergencyContacts|Medications|PatientInfo|Practices|SurgicalHistory|Vaccinations New models from MedFusion */
    public $newModels = [];

    /** @var Allergies|Conditions|EmergencyContacts|Medications|PatientInfo|Practices|SurgicalHistory|Vaccinations Current models from DB */
    public $currentModels = [];

    /** @var string[] names of model's attributes to identity the same model */
    public $identityAttr = [];

    /** @var string[] names of model's attributes to compare with other same models */
    public $compareAttr = [];

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
        $this->patient = \Yii::$app->perfectParser->patient;
        $this->currentModels = $this->getCurrentModels();
        $this->log("Exist models count: ".count($this->currentModels));
    }

    /**
     * Load data from external resources
     * @param \common\components\PerfectParser\DataSources\MedFusion\resources\RResource[]|\common\components\PerfectParser\DataSources\CCDA\resources\RResource[] $resources
     */
    public function load($resources){
        $this->error("Absent ".get_called_class()."::load() method.");
    }

    /**
     * @param Model $model
     */
    public function add(Model $model){
        $this->newModels[] = $model;
    }

    /**
     * Return array of available portal IDs
     * @param $resources \common\components\PerfectParser\DataSources\MedFusion\resources\RResource[]
     * @return int[]
     */
    public function getPortals($resources){
        $portals = [];
        foreach ($resources as $resource){
            $portal_id = $resource->portalID->getValue();
            $portals[$portal_id] = $portal_id;
        }
        return $portals;
    }

    /**
     * Return resourses to specific portal
     * @param $resources \common\components\PerfectParser\DataSources\MedFusion\resources\RResource[]
     * @param $portalID int
     * @return \common\components\PerfectParser\DataSources\MedFusion\resources\RResource[]
     */
    public function getPortalResources($resources, $portalID){
        $result = [];
        foreach ($resources as $resource){
            if ($resource->portalID->getValue() == $portalID) {
                $result[] = $resource;
            }
        }
        return $result;
    }

    /**
     * @param $resources \common\components\PerfectParser\DataSources\MedFusion\resources\RResource[]
     * @param $className string
     * @param $getRelatedClass boolean
     *
     * @return \common\components\PerfectParser\DataSources\MedFusion\resources\RResource[]
     */
    public function filterResourses($resources, $className, $getRelatedClass = true){
        $result = [];

        if ($getRelatedClass) {
            $classes = \Yii::$app->perfectParser->dataSource->internalResourcesList[$className];
            $classes = (is_array($classes) ? $classes : [$classes]);
        } else {
            $classes = [$className];
        }

        foreach ($resources as $resource){
            foreach ($classes as $class){
                if ($resource instanceof $class) {
                    $result[] = $resource;
                }
            }
        }
        return $result;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws \Exception
     */
    protected function getCurrentModels(){
        if (class_exists($this->modelClass)){
            $query = call_user_func(array($this->modelClass, 'find'));
            $practiceCondition = (\Yii::$app->perfectParser->dataSource->processAllPractices ? ['partner_id' => \Yii::$app->perfectParser->dataSource::PARTNER_ID] : ['practice_id' => \Yii::$app->perfectParser->dataSource->sourcePractice->practice_id]);
            /** @var $query ActiveQuery */

            return $query->where(array_merge(
                ['practice_id' => Practices::find()->where($practiceCondition)->column()],
                $this->getPatientIdentity()
            ))->all();
        } else {
            throw new \Exception("Class not found: ".$this->modelClass, 500);
        }
    }

    /**
     * Returns condition for identity patient for current resource models
     * @return array
     */
    protected function getPatientIdentity()
    {
        return [$this->patientIdentityColumn => $this->patient->getAttribute($this->patientIdentityColumn)];
    }

    /**
     * Get actions for synchronisation $currentModels related to $newModels
     *
     * @return ScenarioAction[]
     */
    public function buildActions()
    {
        $actualModels = [];
        $actions = [];

        $processedCurrentModel = $this->currentModels;

        foreach ($this->newModels as $newModel) {
            $add = true;
            foreach ($processedCurrentModel as $currentModel) {
                if ($this->compareRecords($newModel, $currentModel, $this->identityAttr, true)) {
                    // models are related to same records, check it for skip or update
                    $add = false;
                    $actualModels[] = $currentModel;

                    if (!$this->compareRecords($newModel, $currentModel, $this->compareAttr)) {
                        // models are not equivalent and current model must be updated
                        $actions[] = new ScenarioAction([
                            'action' => ScenarioAction::ACTION_UPDATE,
                            'model'  => $currentModel,
                            'data'   => $this->compileUpdateData($currentModel, $newModel)
                        ]);
                    }
                    break;
                }
            }

            if ($add) {
                $actions[] = new ScenarioAction([
                    'action' => ScenarioAction::ACTION_ADD,
                    'model'  => $newModel,
                    'data'   => [],
                ]);
                $processedCurrentModel[] = $newModel;
            }
        }

        foreach ($this->currentModels as $currentModel) {
            $delete = true;
            foreach ($actualModels as $actualModel) {
                /** @var ActiveRecord $currentModel */
                if ($currentModel->getPrimaryKey() == $actualModel->getPrimaryKey()) {
                    $delete = false;
                    break;
                }
            }

            if ($delete) {
                $actions[] = new ScenarioAction([
                    'action' => ScenarioAction::ACTION_REMOVE,
                    'model'  => $currentModel,
                    'data'   => [],
                ]);
            }
        }
        return $actions;
    }

    /**
     * Compare specified attributes in two models
     *
     * @param ActiveRecord $model1 new model
     * @param ActiveRecord $model2 current model
     * @param string[] $compareAttributes
     * @param bool $identity
     *
     * @return boolean
     */
    protected function compareRecords($model1, $model2, $compareAttributes, $identity = false)
    // Should be overridden in some child RResource classes for compare changed records
    {
        $model1->trigger(ActiveRecord::EVENT_AFTER_FIND);

        foreach ($compareAttributes as $attr) {
            if ($model1->$attr !== $model2->$attr && !(is_null($model1->$attr) && is_null($model2->$attr))) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param ActiveRecord $currentModel
     * @param ActiveRecord $newModel
     *
     * @return array
     */
    protected function compileUpdateData($currentModel, $newModel){
        $data = array_udiff_assoc($newModel->getAttributes(), $currentModel->getAttributes(), function($a, $b){
            return ($a === $b)?0:1;
        });

        foreach ($data as $key => $value){
            if (!in_array($key, $this->compareAttr)){
                unset($data[$key]);
            }
        }

        foreach($currentModel->primaryKey() as $key){
            unset($data[$key]);
        }
        return $data ;
    }
}
