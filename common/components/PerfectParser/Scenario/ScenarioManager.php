<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 17:19
 */

namespace common\components\PerfectParser\Scenario;


use common\components\PerfectParser\Common\Traits\DebugTrait;
use common\components\PerfectParser\Resources\RResource;
use common\models\PatientInfo;
use yii\base\BaseObject;
use yii\db\Expression;

/**
 * Class ScenarioManager
 * @package common\components\PerfectParser
 */
class ScenarioManager extends BaseObject
{
    use DebugTrait;

    /** @var ScenarioAction[] List of scenario actions */
    public $scenario = [];
    
    /** @var string[] Names of changed MedInfo items */
    public $changes = [];

    /**
     * Create scenario by new data from internal resources and current DB state
     *
     * @param $internalResources RResource[]
     *
     * @return bool Changes detected
     */
    public function create($internalResources)
    {
        $result = false;
        $this->scenario = [];

        foreach ($internalResources as $resource) {
            /** @var $resource RResource */

            if ($resource->processScenario){
                $scenarioActions = $resource->buildActions();
                if (!empty($scenarioActions)) {
                    $this->scenario = array_merge($this->scenario, $scenarioActions);
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * Add specific scenarios to update last_update fields
     */
    public function processLastUpdatedFields(){
        try{
            $updateForPracticesList = [];
            foreach ($this->scenario as $action){
                $practice_id = $action->model->getAttribute('practice_id');
                if ($practice_id){
                    $updateForPracticesList[$practice_id] = $practice_id;
                }
            }

            foreach ($updateForPracticesList as $practice_id){
                $model = PatientInfo::findOne([
                    'patients_id' => \Yii::$app->perfectParser->patient->patients_id,
                    'practice_id' => $practice_id
                ]);

                if ($model) {
                    $model->last_updated = new Expression("NOW()");
                    $model->save();
                    $this->log("Update last_updated for practice #{$practice_id}");
                } else {
                    $this->log("%RUpdate last_updated for practice #{$practice_id} failed, because PatientInfo record removed.%n");
                }
            }
        } catch(\Throwable $e){
            $this->error($e->getMessage());
        }
    }

    /**
     * Run update scenario
     * @return bool Result
     */
    public function run()
    {
        $result = true;
        try {
            foreach ($this->scenario as $index => $action) {
                $actionResult = $action->run();
                $result = $result && ($actionResult !== ScenarioAction::RESULT_FAIL);
                if ($actionResult === ScenarioAction::RESULT_SKIP){
                    unset($this->scenario[$index]);
                }
            }
        }
        catch (\Throwable $e) {
            \Yii::$app->perfectParser->error($e->getMessage());
            $result = false;
        }
        return $result;
    }
}
