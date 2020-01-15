<?php
namespace common\components\PerfectParser\Resources;


use common\components\PerfectParser\DataSources\CCDA\resources\RProblem;
use common\components\PerfectParser\DataSources\MedFusion\resources\RCondition;
use common\models\Problems;

/**
 * Class RProblems
 * @package common\components\PerfectParser
 */
class RProblems extends RResource
{
    /** @var string Status refuted */
    const STATUS_REFUTED = 'refuted';

    /** @var string Status entered In Error */
    const STATUS_ENTERED_IN_ERROR = 'entered-in-error';

    /** @var string class name with namespace */
    public $modelClass = Problems::class;

    /** @var string Field which identity patient */
    public $patientIdentityColumn = 'internal_id';

    /** @var string[] names of model's attributes to identity the same model */
    public $identityAttr = ['internal_id', 'practice_id', 'problem_text'];

    /** @var string[] names of model's attributes to compare with other same models */
    public $compareAttr = ['problem_active_date', 'problem_end_date', 'problem_list_status', 'icd10'];

    /** @var Problems */
    private $model;

    /**
     * @inheritdoc
     */
    public function load($resources)
    {
        $portals = $this->getPortals($resources);
        foreach($portals as $portalID){
            $practice = \Yii::$app->perfectParser->getPractice($portalID);
            $portalResources = $this->getPortalResources($resources, $portalID);
            $portalFiltredResources = $this->filterResourses($portalResources, get_called_class());
            $this->log("Processing Condition resources for practice #{$practice->practice_id} (".count($portalFiltredResources)." records of ".count($portalResources).")");
            foreach ($portalFiltredResources as $i => $resource) {
                /** @var $resource RCondition|RProblem */

                $this->log("%BIteration ". (1+$i)." of ".count($portalFiltredResources).'%n');
                $this->incPrefix();

                $status = $resource->getStatus();
                if (empty($status) || !in_array($status, [self::STATUS_REFUTED, self::STATUS_ENTERED_IN_ERROR])) {

                    $problemText = $resource->getProblemText();
                    if (!empty($problemText)) {
                        $this->log("Processing '$problemText' record");

                        $this->setModel($portalID);
                        $this->set('problem_text', $problemText);
                        $this->set('problem_list_status', $resource->getProblemListStatus());
                        $this->set('problem_active_date', $resource->getProblemActiveDate());
                        $this->set('problem_end_date', $resource->getProblemEndDate());
                        $this->set('icd10', $resource->getProblemIcd10());

                        $this->log("%GAdd Problems record '{$this->model->problem_text}'%n");
                        $this->add($this->model);

                    } else { 
                        $this->log("Skip Problems record with empty Problem Text");
                    }
                } else { 
                    $this->log("Skip record by status = ".$resource->verificationStatus->getValue());
                }
                $this->decPrefix();
            }
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
            $this->error("Problems model do not have attribute named '{$field}'");
        }
    }

    /**
     * @param $portalID
     *
     * @return Problems
     */
    protected function setModel($portalID)
    {
        $practice = \Yii::$app->perfectParser->getPractice($portalID);
        $model = new Problems();
        $model->internal_id = \Yii::$app->perfectParser->patient->internal_id;
        $model->practice_id = $practice->practice_id;
        $model->display = $this->patient->display_by_default;

        $this->model = $model;
        return $this->model;
    }
}
