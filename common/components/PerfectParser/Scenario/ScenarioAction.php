<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 10:33
 */

namespace common\components\PerfectParser\Scenario;


use common\components\PerfectParser\Common\Traits\DebugTrait;
use common\components\PerfectParser\DataSources\MedFusion\MedFusion;
use common\models\Allergies;
use common\models\Conditions;
use common\models\EmergencyContacts;
use common\models\MedfusionConnections;
use common\models\Medications;
use common\models\PatientInfo;
use common\models\Practices;
use common\models\SurgicalHistory;
use common\models\Vaccinations;
use yii\base\BaseObject;
use yii\helpers\BaseStringHelper;

/**
 * Class ScenarioAction
 * @package common\components\PerfectParser
 */
class ScenarioAction extends BaseObject
{
    use DebugTrait;

    /**
     *
     */
    const ACTION_ADD    = 'add';
    /**
     *
     */
    const ACTION_REMOVE = 'remove';
    /**
     *
     */
    const ACTION_UPDATE = 'update';

    /** @const Result successes */
    const RESULT_OK = 'ok';
    /** @const Result fail */
    const RESULT_FAIL = 'fail';
    /** @const Result skip */
    const RESULT_SKIP = 'skip';

    /** @var string self::ACTION_ADD | self::ACTION_REMOVE | self::ACTION_UPDATE */
    public $action;

    /** @var Allergies|Conditions|EmergencyContacts|Medications|PatientInfo|Practices|SurgicalHistory|Vaccinations Model for apply */
    public $model;

    /** @var mixed[] New data for apply to $this->model */
    public $data = [];

    /**
     * @return bool|false|int
     * @throws \Exception
     * @throws \Throwable
     */
    public function run()
    {
        $id = $this->model->primaryKey?" #".json_encode($this->model->primaryKey):'';

        $color = '%n';
        switch ($this->action){
            case self::ACTION_REMOVE:
                $color = '%R';
                break;
            case self::ACTION_ADD:
                $color = '%G';
                break;
            case self::ACTION_UPDATE:
                $color = '%B';
                break;
        }

        $practice_id = $this->model->getAttribute('practice_id');
        $this->log($color.$this->action.' '.$this->model->tableName().$id.' for practice #'.$practice_id.'%n');
        $result = self::RESULT_FAIL;

        switch ($this->action) {
            case self::ACTION_ADD:
                $result = $this->model->save()?self::RESULT_OK:self::RESULT_FAIL;

                try {
                    $this->model->afterMfSave();
                }
                catch(\Exception $e) {
                    $this->error($e->getMessage());
                }

                break;

            case self::ACTION_UPDATE:
                $this->incPrefix();
                $this->log('Loading data to record #'. json_encode($this->model->primaryKey));
                $this->log('<pre>'.json_encode($this->data, JSON_PRETTY_PRINT).'</pre>');
                $this->decPrefix();
                $this->model->refresh();
                $this->model->load($this->data, '');
                $result = $this->model->save()?self::RESULT_OK:self::RESULT_FAIL;

                try {
                    $this->model->afterMfSave();
                }
                catch(\Exception $e) {
                    $this->error($e->getMessage());
                }

                break;

            case self::ACTION_REMOVE:
                $practice = Practices::findOne(['practice_id' => $practice_id]);
                $modelClass = BaseStringHelper::basename(get_class($this->model));
                if ($practice && $practice->practice_umr_id){
                    if (\Yii::$app->perfectParser->dataSource && constant(get_class(\Yii::$app->perfectParser->dataSource).'::ID') == MedFusion::ID){
                        $connection = MedfusionConnections::getConnection(\Yii::$app->perfectParser->patient, $practice->practice_umr_id, false);
                        if ($connection && !in_array($connection->status, [MedfusionConnections::STATUS_DISCONNECTED, MedfusionConnections::STATUS_NONE])){
                            $result = $this->model->delete()?self::RESULT_OK:self::RESULT_FAIL;
                        } else {
                            $result = self::RESULT_SKIP;
                            $this->log("%YSkip remove $modelClass for practice_id #{$this->model->practice_id} while SLID-1127 %n");
                        }
                    } else {
                        $result = $this->model->delete()?self::RESULT_OK:self::RESULT_FAIL;
                    }
                } else {
                    $this->error("Something wrong: could get portalID for $modelClass record: ".json_encode($this->model->getAttributes()));
                }
                break;
            default:
                $this->error("Unknown scenario action: ".$this->action);
        }

        if ($result == self::RESULT_OK) {
            $patientInfo = \Yii::$app->perfectParser->patient->getPatientInfo($practice_id)->one();
            /** @var PatientInfo $patientInfo */
            $patientInfo->last_updated = date('Y-m-d H:i:s');
            $patientInfo->save();
            \Yii::$app->perfectParser->log('Set last_update for practice_id = '.$practice_id.' to '.$patientInfo->last_updated);
        } else if ($result == self::RESULT_FAIL) {
            $this->error($this->model->errors);
        }

        return $result;
    }
}