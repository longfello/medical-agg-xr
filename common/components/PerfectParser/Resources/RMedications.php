<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:10
 */

namespace common\components\PerfectParser\Resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\RBundle;
use common\components\PerfectParser\DataSources\MedFusion\resources\RMedicationAdministration;
use common\components\PerfectParser\DataSources\MedFusion\resources\RMedicationDispense;
use common\components\PerfectParser\DataSources\MedFusion\resources\RMedicationOrder;
use common\components\PerfectParser\DataSources\MedFusion\resources\RMedicationStatement;
use common\components\PerfectParser\DataSources\CCDA\resources\RMedication;
use common\models\Medications;
use yii\helpers\StringHelper;

/**
 * Class RMedications
 * @package common\components\PerfectParser
 */
class RMedications extends RResource
{
    /**
     * Status entered In Error
     */
    const STATUS_ENTERED_IN_ERROR = 'entered-in-error';

    /**
     * @const string
     * Medication text, that should be ignored
     */
    const IGNORED_TEXT = 'No Known Medications';

    /** @var string class name with namespace */
    public $modelClass = Medications::class;

    /** @var string Field which identity patient */
    public $patientIdentityColumn = 'internal_id';

    /** @var string[] names of model's attributes to identity the same model */
    public $identityAttr = ['internal_id', 'practice_id', 'medication_text', 'identity'];

    /** @var string[] names of model's attributes to compare with other same models */
    public $compareAttr = ['medication_strength', 'medication_route', 'medication_duration', 'dose_unit', 'dose_timing', 'num_refills', 'date_prescribed', 'end_date', 'status', 'direction_to_patient'];

    /** @var Medications */
    private $model;

    /** @inheritdoc
     * @param $resources
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function load($resources){
        $portals = $this->getPortals($resources);
        foreach($portals as $portalID){
            $practice = \Yii::$app->perfectParser->getPractice($portalID);
            $portalResources = $this->getPortalResources($resources, $portalID);
            $portalFiltredResources = $this->filterResourses($portalResources, get_called_class());
            $this->log("Processing Medication[] resources for practice #{$practice->practice_id} (".count($portalFiltredResources)." records of ".count($portalResources).")");
            foreach ($portalFiltredResources as $i => $resource) {
                /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement|RBundle|RMedication */
                $this->log("%BIteration ". (1+$i)." of ".count($portalFiltredResources) . ", resource base class = ". StringHelper::basename(get_class($resource)).'%n');
                $this->incPrefix();
                $status = $resource->getStatus($resource);
                if ($status !== self::STATUS_ENTERED_IN_ERROR){

                    $datas = $this->explodeResourseByMedicationName($resource);
                    $datasCount = count($datas);

                    $dataType   = ($datasCount > 1)?"multiply ({$datasCount})":"simple";
                    $this->log("Processing $dataType record");

                    foreach($datas as $data){
                        $this->incPrefix();

                        $text = $data->getMedicationText();
                        $identity = (empty($data->identity) ? $resource->identity : $data->identity);

                        if ($text && $identity && strtolower($text) != strtolower(self::IGNORED_TEXT)) {
                            $this->log("Processing '$text' record");
                            $this->incPrefix();

                            $this->setModel($portalID, $text);
                            $this->model->identity = $identity;
                            $this->set('medication_strength', (string)$data->getMedicationStrange());
                            $this->set('medication_route', (string)$data->getMedicationRoute());
                            $this->set('medication_duration', (string)$data->getMedicationDuration());
                            $this->set('dose_unit', (string)$data->getMedicationDoseUnit());
                            $this->set('dose_timing', (string)$data->getMedicationDoseTiming());
                            $this->set('num_refills', (string)$data->getMedicationNumRefills());
                            $this->set('date_prescribed', (string)$data->getMedicationDatePrescribed());
                            $this->set('end_date', (string)$data->getMedicationEndDate());
                            $this->set('status', (string)$data->getMedicationStatus());
                            $this->set('direction_to_patient', (string)$data->getMedicationDirectionToPatient());
                            $this->set('rx_norm_code', '');
                            $this->set('ncdid', '');
                            $this->set('display', $this->patient->display_by_default);

                            $this->log("%GAdd Medication record '{$this->model->medication_text}'%n");
                            $this->add($this->model);

                            $this->decPrefix();
                        } else $this->log("%RSkip record because medication name is empty%n");
                        $this->decPrefix();
                    }
                } else $this->log("%RSkip record because status == entered-in-error%n");

                $this->decPrefix();
            }
            $this->log("");
        }
    }

    /**
     * @param $field
     * @param $value
     */
    public function set($field, $value){
        if ($this->model->hasAttribute($field)){
            $valueText = empty($value)?"%PEMPTY%n":"'%G{$value}%n'";
            $this->log("Set `{$field}` to {$valueText}");
            $this->model->setAttribute($field, $value);
        } else {
            $this->error("Medication model do not have attribute named '{$field}'");
        }
    }

    /**
     * Set processing AR model
     * @param $portalID int
     * @param $name string
     *
     * @return Medications
     */
    protected function setModel($portalID, $name)
    {
        $practice = \Yii::$app->perfectParser->getPractice($portalID);
        $model = new Medications();
        $model->internal_id = \Yii::$app->perfectParser->patient->internal_id;
        $model->practice_id = $practice->practice_id;
        $model->medication_text = $name;

        $this->model = $model;
        return $this->model;
    }

    /**
     * Explode Bundle data by medication_text data
     * @param mixed $resource
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    protected function explodeResourseByMedicationName($resource){
        if ($resource instanceof RBundle){
            return $resource->explodeResourseByMedicationName();
        } else {
            return [$resource];
        }
    }

}
