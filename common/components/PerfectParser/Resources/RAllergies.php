<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:10
 */

namespace common\components\PerfectParser\Resources;


use common\components\PerfectParser\DataSources\CCDA\resources\RAllergy;
use common\components\PerfectParser\DataSources\MedFusion\resources\RAllergyIntolerance;
use common\components\PerfectParser\Scenario\ScenarioAction;
use common\models\Allergies;

/**
 * Class RAllergies
 * @package common\components\PerfectParser
 */
class RAllergies extends RResource
{
    /** @const string */
    const STATUS_ACTIVE = 'active';

    /** @const string */
    const STATUS_INACTIVE = 'inactive';

    /** @const string */
    const STATUS_RESOLVED = 'resolved';

    /** @var string Status refuted */
    const STATUS_REFUTED = 'refuted';

    /** @var string Status entered In Error */
    const STATUS_ENTERED_IN_ERROR = 'entered-in-error';

    /**  @var string unconfirmed status */
    const STATUS_UNCONFIRMED = 'unconfirmed';

    /** @var string class name with namespace */
    public $modelClass = Allergies::class;

    /** @var string Field which identity patient */
    public $patientIdentityColumn = 'internal_id';

    /** @var string[] names of model's attributes to identity the same model */
    public $identityAttr = ['internal_id', 'practice_id', 'allergies_text'];

    /** @var string[] names of model's attributes to compare with other same models */
    public $compareAttr = ['allergies_severity', 'allergies_reaction', 'date_of_onset'];

    /** @var Allergies */
    private $model;

    /**
     * @inheritdoc
     * @param $resources
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function load($resources){
        $portals = $this->getPortals($resources);
        $isPotentialNoAllergy = 0;

        foreach($portals as $portalID){
            $practice = \Yii::$app->perfectParser->getPractice($portalID);
            $portalResources = $this->getPortalResources($resources, $portalID);
            $portalFiltredResources = $this->filterResourses($portalResources, get_called_class());

//            var_dump($portalFiltredResources[0]);
//            exit;

            $this->log("Processing Allergies resources for practice #{$practice->practice_id} (".count($portalFiltredResources)." records of ".count($portalResources).")");
            foreach ($portalFiltredResources as $i => $resource) {
                /** @var $resource RAllergyIntolerance|RAllergy */
                $this->log("%BIteration ". (1+$i)." of ".count($portalFiltredResources).'%n');
                $this->incPrefix();
                $isPotentialNoAllergy = !$isPotentialNoAllergy ? $resource->isNoAllergy() : $isPotentialNoAllergy;
                $status = strtolower($resource->getStatus());

                //if (!in_array($status, [self::STATUS_REFUTED, self::STATUS_ENTERED_IN_ERROR, self::STATUS_INACTIVE])){
                if (!in_array($status, [self::STATUS_REFUTED, self::STATUS_ENTERED_IN_ERROR])){ // Should be processed an inactive allergies?
                    if ($resource->isSubstancePresent()){
                        if ($resource->isValid()) {

                            $allergyText = $resource->getAllergyText();
                            if (!empty($allergyText)) {
                                $this->log("Processing '$allergyText' record");
                                $this->setModel($portalID);

                                $this->log('STATUS '.$resource->getStatus());
                                $this->set('allergies_text', $allergyText);
                                $this->set('allergies_reaction', (string)$resource->getReactionText());
                                $this->set('allergies_severity', (string)$resource->getSeverityText());
                                $this->set('date_of_onset', (string)$resource->getDateOnSet());
                                $this->set('rx_norm_code', null);
                                $this->set('snomed_code', null);
                                $this->set('ncdid', null);

                                $this->add($this->model);
                                $this->log("%GAdd new allergie '{$this->model->allergies_text}' with reaction '{$this->model->allergies_reaction}' and severity '{$this->model->allergies_severity}'%n");
                            } else {
                                $this->log("Skip record by empty allergy_text");
                            }
                        } else {
                            $this->log("Skip record by invalid allergy entity");
                        }
                    } else {
                        $this->log("Skip record by no substance founded");
                    }
                } else {
                    $this->log("Skip record by status = ".$status);
                }
                $this->decPrefix();
            }
            $this->log("");
        }

        // set not allergy flag - allergies is empty && exist flag no allergy
        /*
        if (empty($this->newModels) && $isPotentialNoAllergy) {
            $patient = Patient::findOne(['internal_id' => \Yii::$app->perfectParser->patient->internal_id]);

            $patient->medinfoMeta->saveValue(PatientMedinfoMeta::SLUG_NO_ALLERGIES, 1);

            $this->log("Set flag no allergy");
        }
        */
    }

    /**
     * @return ScenarioAction[]
     * @throws \yii\db\Exception
     */
    /*
    public function buildActions()
    {
        $actions = parent::buildActions();
        foreach ($actions as $one) {
           if ($one->action == ScenarioAction::ACTION_ADD) {
                /\Yii::$app->perfectParser->patient->medinfoMeta->saveValue(PatientMedinfoMeta::SLUG_NO_ALLERGIES, 0);
                break;
            }
        }
        return $actions;
    }
    */

    /**
     * @param $portalID
     *
     * @return Allergies
     */
    protected function setModel($portalID){
        $practice = \Yii::$app->perfectParser->getPractice($portalID);
        $model = new Allergies();
        $model->internal_id = \Yii::$app->perfectParser->patient->internal_id;
        $model->practice_id = $practice->practice_id;
        $model->display = $this->patient->display_by_default;

        $this->model = $model;
        return $model;
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
            $this->error("Problems model do not have attribute named '{$field}'");
        }
    }
}
