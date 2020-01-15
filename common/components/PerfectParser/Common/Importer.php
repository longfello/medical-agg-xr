<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 16:48
 */

namespace common\components\PerfectParser\Common;

use common\components\PerfectParser\Scenario\ScenarioAction;
use common\components\PerfectParser\Scenario\ScenarioManager;
use common\components\PerfectParser\Common\Traits\DebugTrait;
use common\models\PatientInfo;
use common\models\Practices;
use yii\base\BaseObject;
use yii\db\Exception;


/**
 * Class Importer
 * @package common\components\PerfectParser
 */
class Importer extends BaseObject
{
    use DebugTrait;

    /** @var ScenarioManager */
    private $ScenarioManager;

    /**
     * @inheritdoc
     */
    public function init(){
        parent::init();
        $this->ScenarioManager = new ScenarioManager();
    }

    /**
     * @param bool $debugData
     *
     * @return bool
     * @throws \Exception
     */
    public function import($debugData = false)
    {
        $success = false;
        if ($this->retriveExternalData($debugData)) {
            if ($this->loadExternalData()){
                if ($this->loadInternalData()){
                    if ($this->createScenario()){
                        if ($this->applyScenario()){
                            if (\Yii::$app->perfectParser->notifyPatientAboutChanges){
                                $this->processNotification();
                            }
                            $success = true;
                        }
                    }
                }
            }
        }

        return $success;
    }

    /**
     * @param bool $debugData
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    protected function retriveExternalData($debugData = false)
    {
        $this->log('%gRetrive External data '.PHP_EOL."%n", true);
        $result = \Yii::$app->perfectParser->dataSource->retriveExternalData($debugData);
        $this->log(PHP_EOL."%gRetrive External data done...%n".PHP_EOL);
        return $result;
    }

    /**
     * Load external data from TInfoBlock[]
     * @return bool
     * @throws \yii\base\Exception
     */
    protected function loadExternalData(){
        $this->log(PHP_EOL.'%gLoad External Resources '.PHP_EOL."%n".PHP_EOL, true);
        $result =  \Yii::$app->perfectParser->dataSource->loadExternalData();
        $this->log(PHP_EOL."%gLoading external Resources done...%n".PHP_EOL);
        return $result;
    }

    /**
     * Load internal resources from external resources
     * @return bool
     * @throws \yii\base\Exception
     */
    protected function loadInternalData(){
        $this->log(PHP_EOL.'%gLoad Internal Resources '.PHP_EOL."%n", true);
        $result =  \Yii::$app->perfectParser->dataSource->loadInternalData();
        $this->log(PHP_EOL."%gLoading Internal Resources done...%n".PHP_EOL);
        return $result;
    }

    /**
     * Create scenario for next apply to DB
     * @return bool
     */
    protected function createScenario(){
        $this->log(PHP_EOL.'%gCreating Scenario '.PHP_EOL."%n", true);

        $result = $this->ScenarioManager->create(\Yii::$app->perfectParser->dataSource->internalResources);
        if ($result){
            $this->log("Changes to apply: ".count($this->ScenarioManager->scenario));
            $this->incPrefix();
            foreach($this->ScenarioManager->scenario as $one){
                /** @var $one ScenarioAction */
                $id = $one->model->primaryKey?" #".json_encode($one->model->primaryKey):'';
                $this->log($one->action.' '.$one->model->tableName().$id);
            }

            $this->decPrefix();

            $this->log(PHP_EOL."%gCreating Scenario done...%n".PHP_EOL);
            return true;
        } else {
            $this->log(PHP_EOL."%gSkipping EMPTY scenario...%n".PHP_EOL);
            return false;
        }
    }

    /**
     * Step 5 - Apply scenarion to database
     * @return bool Result
     */
    protected function applyScenario(){
        $this->log(PHP_EOL.'%gApply Scenario '.PHP_EOL."%n", true);

        $result = $this->ScenarioManager->run();
        $this->ScenarioManager->processLastUpdatedFields();

        $this->log(PHP_EOL."%gApply Scenario done...%n".PHP_EOL);
        return $result;
    }

    /**
     * Step 6 - Process notifications
     * @throws \Exception
     */
    protected function processNotification(){
        $changes = [];

        $newPractices = [];
        foreach ($this->ScenarioManager->scenario as $action) {
            // SLID-1177 - notify only on remove/add
            if (in_array($action->action, [ScenarioAction::ACTION_ADD, ScenarioAction::ACTION_REMOVE])){
                if ($action->model instanceof PatientInfo){
                    if (!$action->model->last_updated){
                        $newPractices[] = $action->model->getAttribute('practice_id');
                    }
                }
            }
        }

        foreach ($this->ScenarioManager->scenario as $action){
            // SLID-1177 - notify only on remove/add
            if (in_array($action->action, [ScenarioAction::ACTION_ADD, ScenarioAction::ACTION_REMOVE])){
                $practice_id = $action->model->getAttribute('practice_id');
                if (!in_array($practice_id, $newPractices)){
                    $changedItem = Helper::get_medinfo_name_from_model(get_class($action->model));
                    if (!isset($changes[$practice_id])) {
                        $changes[$practice_id] = [];
                    }
                    $changes[$practice_id][$changedItem] = $changedItem;
                }
            }
        }

        if ($changes){
            $this->log('Sending notifications', true);
            $this->incPrefix();
            foreach ($changes as $practice_id => $info){
                $practice = Practices::findOne(['practice_id' => $practice_id]);
                $this->log($practice->practice_name.' (#'.$practice_id.')');
                \Yii::$app->perfectParser->patient->notifyAboutChanges($info, $practice);
            }
            $this->decPrefix();
        } else {
            $this->log('Sending notifications: nothing to send');
        }
    }
}