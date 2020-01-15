<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:10
 */

namespace common\components\PerfectParser\Resources;


use common\components\PerfectParser\Common\ItemCollection;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\labs\RLab;
use common\components\PerfectParser\DataSources\MedFusion\resources\RDevice;
use common\components\PerfectParser\DataSources\MedFusion\resources\RPatient;
use common\components\PerfectParser\Scenario\ScenarioAction;
use common\models\PatientInfo;

/**
 * Class RPatientInfo
 * @package common\components\PerfectParser
 */
class RPatientInfo extends RResource
{
    /** @var string class name with namespace */
    public $modelClass = PatientInfo::class;
    
    /** @var string[] names of model's attributes to identity the same model */
    public $identityAttr = ['internal_id', 'practice_id'];

    /** @var string[] names of model's attributes to compare with other same models */
    public $compareAttr = ['date_of_birth', 'height', 'weight', 'device_dependencies', 'gender'];


    /** @var ItemCollection */
    private $height;

    /** @var ItemCollection */
    private $weight;

    /** @inheritdoc
     * @param $resources
     *
     * @throws \yii\base\Exception
     */
    public function load($resources){
        $portals = $this->getPortals($resources);
        foreach($portals as $portalID){
            $this->height = new ItemCollection();
            $this->weight = new ItemCollection();
            $portalResources = $this->getPortalResources($resources, $portalID);
            $model = $this->getModel($portalID);
            $this->log("");
            $this->processPatientResourses($model, $portalResources);
            $this->processObservationResourses($model, $portalResources);
            $this->processDeviceResourses($model, $portalResources);

            $this->log("%GAdd PatientInfo record for practice '{$model->practice->practice_name}' #'$model->practice_id'%n");
            $this->add($model);
        }
    }

    /**
     * @return ScenarioAction[]
     */
    public function buildActions()
    {
        $actions = parent::buildActions();

        // ### Commented by SLID-1127 ###
        // if (!\Yii::$app->perfectParser->isTest()){
            foreach ($actions as $index => $action){
                if ($action->action == ScenarioAction::ACTION_REMOVE){
                    unset($actions[$index]);
                    $this->log("%YSkip remove PatientInfo for practice_id #{$action->model->practice_id} while SLID-1127 %n");
                    /*
                    // ### Commented by SLID-1127 - BEGIN ###
                    // Double check if connection exists but in ERROR status
                    $model = $action->model;
                    / ** @var $model PatientInfo * /
                    if ($model->practice) {
                        $portalID = (int)$model->practice->practice_umr_id;
                        $connection = MedfusionConnections::findOne([
                            'patient_id' => $this->patient->patients_id,
                            'portal_id'  => $portalID
                        ]);
                        if ($connection){
                            unset($actions[$index]);
                            $this->log("%YSkip remove PatientInfo for practice_id #{$model->practice_id} while connection exists%n");
                        }
                    }
                    // ### Commented by SLID-1127 - END ###
                    */
                }
            }
        // ### Commented by SLID-1127 ###
        // }
        return $actions;
    }

    /**
     * Process Patient Resources
     * @param $model PatientInfo
     * @param $portalResources \common\components\PerfectParser\DataSources\MedFusion\resources\RResource[]
     */
    protected function processPatientResourses(&$model, $portalResources){
        $portalFiltredResources = $this->filterResourses($portalResources, get_called_class());
        $this->log("Processing Patient resources for practice #{$model->practice_id} (".count($portalFiltredResources)." records of ".count($portalResources).")");
        foreach ($portalFiltredResources as $i => $resource){
            /** @var $resource RPatient */
            $this->log("%BIteration ". (1+$i)." of ".count($portalFiltredResources).'%n');
            $this->incPrefix();
            if ($resource->gender){
                $gender = $resource->gender->getValue();
                $candidate = ucfirst(strtolower(trim($gender)));
                if (in_array($candidate, [PatientInfo::GENDER_MALE, PatientInfo::GENDER_FEMALE])) {
                    $model->gender = $candidate;
                    $this->log("Found valid standart value, set gender to '{$candidate}'");
                } else {
                    $gender = trim($gender);
                    if ($gender) {
                        $model->gender = $gender;
                        $this->log("Found valid custom value, set gender to '{$gender}'");
                    }
                }
            }

            if ($resource->birthDate){
                $model->date_of_birth = $resource->birthDate->getValue();
                $model->date_of_birth = empty($model->date_of_birth)?"0000-00-00":$model->date_of_birth;
                $this->log("Found valid custom value, set date_of_birth to '{$model->date_of_birth}'");
            }
            $this->decPrefix();
        }
        $this->log("");
    }

    /**
     * @param $model PatientInfo
     * @param $portalResources \common\components\PerfectParser\DataSources\MedFusion\resources\RResource[]
     * @param $isSubResource bool
     */
    protected function processObservationResourses(&$model, $portalResources, $isSubResource = false){
        $portalFiltredResources = $this->filterResourses($portalResources, RLabs::class);
        $resourseType = $isSubResource?"SubResource":"Resource";
        $this->log("Processing Observation {$resourseType} for practice #{$model->practice_id} (".count($portalFiltredResources)." records of ".count($portalResources).")");
        foreach ($portalFiltredResources as $i => $resource){
            /** @var $resource RLab */
            $foundedAny = false;
            $this->log("%BIteration ". (1+$i) ." of ".count($portalFiltredResources).'%n');
            $this->incPrefix();

            if ($resource->contained){
                $this->processObservationResourses($model, $resource->contained, true);
                $foundedAny = true;
            }

            // process height
            if ($resource->hasCode(TCode::CODE_HEIGHT, TCode::MARKER_HEIGHT)) {
                $value = $resource->getResourceValue();
                $time  = $resource->getResourceTime();
                $this->log("Found height value '{$value}' dated as '".date('d M Y H:i:s', $time)."'");
                $this->height->add($value, $time);
                $foundedAny = true;
            }

            // process weight
            if ($resource->hasCode(TCode::CODE_WEIGHT, TCode::MARKER_WEIGHT)) {
                $value = $resource->getResourceValue();
                $time  = $resource->getResourceTime();
                $this->log("Found weight value '{$value}' dated as '".date('d M Y H:i:s', $time)."'");
                $this->weight->add($value, $time);
                $foundedAny = true;
            }

            if (!$foundedAny){
                $this->log('Not found useful information.');
            }

            $this->decPrefix();
        }
        if (!$isSubResource) {
            $model->height = $this->height->getLastByTime();
            $model->height = str_replace(['[in_us]', '[in]'],'inches', $model->height);
            $this->log("Set height to {$model->height}");

            $model->weight = $this->weight->getLastByTime();
            $model->weight = str_replace(['[lb_av]', '[lb]'], 'lb', $model->weight);
            $this->log("Set weight to {$model->weight}");
            $this->log("");
        }
    }

    /**
     * @param $model PatientInfo
     * @param $portalResources \common\components\PerfectParser\DataSources\MedFusion\resources\RResource[]
     *
     * @throws \yii\base\Exception
     */
    protected function processDeviceResourses($model, $portalResources){
        $deviceClass = \Yii::$app->perfectParser->dataSource->getDeviceClass();
        $portalFiltredResources = $this->filterResourses($portalResources, $deviceClass, false);
        $this->log("Processing Device resources for practice #{$model->practice_id} (".count($portalFiltredResources)." records of ".count($portalResources).")");
        $devices = [];
        foreach ($portalFiltredResources as $i => $resource){
            /** @var $resource RDevice */
            $this->log("%BIteration ". (1+$i)." of ".count($portalFiltredResources).'%n');
            $this->incPrefix();
            if ($resource->type){
                if ($resource->type->text){
                    $this->log("Found device: ".$resource->type->text->getValue());
                    $devices[] = $resource->type->text->getValue();
                }
                if ($resource->type->coding){
                    foreach($resource->type->coding as $coding){
                        if ($coding->display && !empty($coding->display->getValue())) {
                            $this->log("Found device: ".$coding->display->getValue());
                            $devices[] = $coding->display->getValue();
                        }
                    }
                }
            }
            $this->decPrefix();
        }
        if ($devices){
            $devices = implode(', ', $devices);
            $this->log("Set device_dependencies to '{$devices}'");
            $model->device_dependencies = $devices;
        }

        $this->log("");
    }

    /**
     * @param $portalID
     *
     * @return PatientInfo
     */
    protected function getModel($portalID){
        $practice = \Yii::$app->perfectParser->getPractice($portalID);
        $model = new PatientInfo();
        $model->patients_id = \Yii::$app->perfectParser->patient->patients_id;
        $model->internal_id = \Yii::$app->perfectParser->patient->internal_id;
        $model->practice_id = $practice->practice_id;
        $model->display_comments = $this->patient->display_by_default;
        $model->display_device = $this->patient->display_by_default;
        $model->display_dob = $this->patient->display_by_default;
        $model->display_emr_emergency_summary = $this->patient->display_by_default;
        $model->display_height = $this->patient->display_by_default;
        $model->display_weight = $this->patient->display_by_default;
        $model->display_gender = $this->patient->display_by_default;

        return $model;
    }
}