<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:11
 */

namespace common\components\PerfectParser\Resources;


use common\models\SurgicalHistory;
use common\components\PerfectParser\DataSources\MedFusion\resources\RProcedure;
use common\components\PerfectParser\DataSources\MedFusion\resources\ROrganization;
use common\components\PerfectParser\DataSources\MedFusion\resources\RPractitioner;

/**
 * Class RSurgicalHistory
 * @package common\components\PerfectParser
 */
class RSurgicalHistory extends RResource
{
    /** @var string class name with namespace */
    public $modelClass = SurgicalHistory::class;

    /** @var string Field which identity patient */
    public $patientIdentityColumn = 'internal_id';

    /** @var string[] names of model's attributes to identity the same model */
    public $identityAttr = ['internal_id', 'description', 'practice_id', 'date_onset'];

    /** @var string[] names of model's attributes to compare with other same models */
    public $compareAttr = ['performing_physician', 'performing_location'];

    /**
     * @inheritdoc
     */
    public function load($resources) {
        $portals = $this->getPortals($resources);
        foreach($portals as $portalID) {
            $practice = \Yii::$app->perfectParser->getPractice($portalID);
            $portalResources = $this->getPortalResources($resources, $portalID);
            $portalFiltredResources = $this->filterResourses($portalResources, get_called_class());
            $this->log("Processing Procedure resources for practice #{$practice->practice_id} (".count($portalFiltredResources)." records of ".count($portalResources).")");
            foreach ($portalFiltredResources as $i => $resource) {
                /** @var $resource RProcedure */
                $this->log("%BIteration ". (1+$i)." of ".count($portalFiltredResources).'%n');
                $this->incPrefix();
                if ($resource->code) {
                    $model = $this->getModel($portalID);
                    if ($model->description = $resource->code->getValue()) {
                        $this->log("Found valid value, set description to '{$model->description}'");

                        if (isset($resource->performedDateTime)) {
                            $model->date_onset = $resource->performedDateTime->asDate();
                            $this->log("Found valid value from performedDateTime, set date_onset to '{$model->date_onset}'");
                        } elseif(isset($resource->performedPeriod)) {
                            $model->date_onset = $resource->performedPeriod->asDateTimePeriod();
                            $this->log("Found valid value from performedPeriod, set date_onset to '{$model->date_onset}'");
                        }

                        $model->performing_location  = (string)$this->getPerformLocation($resource);
                        $model->performing_physician = (string)$this->getPerformPhysician($resource);

                        $this->add($model);
                        $this->log("%GAdd SurgicalHistory record '{$model->description}'%n");

                    } else { $this->log("Skip record because code is empty"); }
                } else { $this->log("Skip record because code is absent"); }
                $this->decPrefix();
            }
            $this->log("");
        }
    }

    /**
     *
     * @param \common\components\PerfectParser\DataSources\MedFusion\resources\RResource $resource
     * @return string | null
     */
    protected function getPerformLocation($resource)
    {
        $result = null;
        if (isset($resource->location)) {
            $result = $resource->location->getValue(); // TODO: get via reference, and may be other method specific for location resource type instead of getValue()
        } elseif (isset($resource->performer)) {

            foreach ($resource->performer as $performer) {
                if (isset($performer->actor)) {
                    $reference = $resource->getReferencedResource($performer->actor);
                    if ($reference instanceof ROrganization) {
                        $result = $reference->name->getValue();

                        if ($reference->address){
                            foreach ($reference->address as $addr) {
                                if (isset($addr->city)) {
                                    $result .= ' ('.$addr->city;
                                    if (isset($addr->state)) {
                                        $result .= ', '.$addr->state.')';
                                    } else {
                                        $result .= ')';
                                    }
                                    break;
                                } elseif (isset($addr->state)) {
                                    $result .= ' ('.$addr->state.')';
                                    break;
                                }
                            }
                        }
                        $this->log("Found valid value, set performing_location to '{$result}'");
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     *
     * @param \common\components\PerfectParser\DataSources\MedFusion\resources\RResource $resource
     * @return string | null
     */
    protected function getPerformPhysician($resource)
    {
        $result = null;
        if (isset($resource->performer)) {
            $practitioners = [];
            foreach ($resource->performer as $performer) {
                if (isset($performer->actor)) {
                    $reference = $resource->getReferencedResource($performer->actor);
                    if ($reference instanceof RPractitioner) {

                        if ($reference->name && ($name = $reference->name->format())) {
                           $practitioners[] = $name;
                        }
                    }
                }
            }

            $result = '';
            $last = (count($practitioners) - 1);
            foreach ($practitioners as $index => $one) {
                if ($index and $index == $last) {
                    $result .= " and ";
                } elseif ($index > 0) {
                    $result .= ", ";
                }
                $result .= $one;
            }

            if ($result){
                $this->log("Found valid value, set performing_physician to '{$result}'");
            }
        }
        return mb_substr($result, 0, 100);
    }

    /**
     * @param $portalID
     *
     * @return SurgicalHistory
     */
    protected function getModel($portalID){
        $practice = \Yii::$app->perfectParser->getPractice($portalID);
        $model = new SurgicalHistory();
        $model->internal_id = \Yii::$app->perfectParser->patient->internal_id;
        $model->practice_id = $practice->practice_id;
        $model->display = $this->patient->display_by_default;

        return $model;
    }

}
