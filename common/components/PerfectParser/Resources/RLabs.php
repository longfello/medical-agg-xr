<?php
namespace common\components\PerfectParser\Resources;

use common\components\PerfectParser\DataSources\CCDA\resources\labs\RLab;
use common\components\PerfectParser\DataSources\CCDA\resources\labs\RRequest;
use common\components\PerfectParser\DataSources\MedFusion\resources\labs\RNte;
use common\models\LabsComplex;
use common\models\LabsComplexReq;
use common\models\LabsComplexObs;
use common\models\LabsComplexNte;
use common\models\LabsObservations;
use common\models\LabsReports;
use common\models\LabsRequests;
use common\models\Practices;
use common\models\Partners;
use common\models\LabsObservationsFlags;
use ReflectionClass;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;


/**
 * Class RLabs
 * @package common\components\PerfectParser
 */
class RLabs extends RResource
{
    /** @var string class name with namespace */
    public $modelClass = LabsComplex::class;

    /** @var string Field which identity patient */
    public $patientIdentityColumn = 'lab_report_internal_id';

    /** @var string[] names of model's attributes to identity the same model */
    public $identityAttr = ['practice_id', 'lab_report_internal_id', 'identity'];

    /**
     * @var array
     */
    public $identityAttrReq = ['lab_request_identifier', 'lab_request_observation_date']; // check this attributes!

    /**
     * @var array
     */
    public $identityAttrObs = ['lab_observation_identifier', 'lab_observation_date']; // check this attributes!

    /**
     * @var array
     */
    public $identityAttrNte = ['lab_nte'];

    /** @var string[] names of model's attributes to compare with other same models */
    public $compareAttr = ['lab_ordered_by', 'lab_source', 'lab_report_date' ,
        'lab_request_id_text', 'lab_request_id_coding', 'lab_request_id_system', 'lab_request_date', 'lab_request_observation_end_date',
        'lab_observation_id_text', 'lab_observation_id_coding', 'lab_observation_id_system', 'lab_observation_value', 'lab_observation_units', 'lab_observation_range', 'lab_observation_abnormal_flags', 'lab_observation_result_status',
        'lab_nte'];


    /**
     * @param \common\components\PerfectParser\DataSources\MedFusion\resources\RResource[] $resources
     *
     * @throws \yii\base\Exception
     * @throws \ReflectionException
     */
    public function load($resources)
    {
        $portals = $this->getPortals($resources);
        foreach ($portals as $portalID) {
            $practice = \Yii::$app->perfectParser->getPractice($portalID);
            $portalResources = $this->getPortalResources($resources, $portalID);
            $portalFiltredResources = $this->filterResourses($portalResources, get_called_class());
            $this->log("Processing Labs resources for practice #{$practice->practice_id} (".count($portalFiltredResources)." records of ".count($portalResources).")");

            $isSkipReport = false;

            foreach ($portalFiltredResources as $i => $resource) {
                /** @var $resource \common\components\PerfectParser\DataSources\MedFusion\resources\labs\RLab|RLab */
                $this->log("%BIteration ". (1+$i)." of ".count($portalFiltredResources).'%n');

                $model = $this->getModel($portalID);

                if ($resource->checkIsLab()) {
                    /**
                     * Report
                     */
                    $this->logBlockTitleByModel($model);
                    $this->set($model, 'identity', $resource->getIdentity());
                    $this->set($model, 'lab_ordered_by', $resource->getOrderedBy());
                    $this->set($model, 'lab_report_date', $resource->getReportDate());

                    /**
                     * Requests
                     */
                    foreach ($resource->getRequests() as $outerRequest) {
                        /** @var RRequest $outerRequest */

                        if ($outerRequest->IsSkipReport()) {
                            $isSkipReport = true;
                            break;
                        }

                        $innerRequest = new LabsComplexReq();
                        $innerRequest->lab_request_date = null;

                        $this->incPrefix();
                        $this->logBlockTitleByModel($innerRequest);

                        $this->set($innerRequest, 'lab_request_id_coding', $outerRequest->getRequestIdCoding());
                        $this->set($innerRequest, 'lab_request_id_system', $outerRequest->getRequestIdSystem());
                        $this->set($innerRequest, 'lab_request_identifier', $outerRequest->getRequestIdentifier());
                        $this->set($innerRequest, 'lab_request_id_text', $outerRequest->getRequestIdText());
                        $this->set($innerRequest, 'lab_request_observation_date', $outerRequest->getRequestObservationDate());
                        $this->set($innerRequest, 'lab_request_observation_end_date', $outerRequest->getRequestObservationEndDate());


                        if ($this->checkNoEmpty($innerRequest, $this->identityAttrReq)) {

                            /**
                             * Observers
                             */
                            foreach($outerRequest->getObservations() as $outerObservation) {
                                /** @var \common\components\PerfectParser\DataSources\MedFusion\resources\labs\RObservation $outerObservation */
                                $innerObservation = new LabsComplexObs();

                                $this->incPrefix();
                                $this->logBlockTitleByModel($innerObservation);

                                $this->set($innerObservation, 'lab_observation_identifier', $outerObservation->getObservationIdentifier());
                                $this->set($innerObservation, 'lab_observation_id_coding', $outerObservation->getObservationIdCoding());
                                $this->set($innerObservation, 'lab_observation_id_system', $outerObservation->getObservationIdSystem());
                                $this->set($innerObservation, 'lab_observation_date', $outerObservation->getObservationDate());
                                $this->set($innerObservation, 'lab_observation_id_text', $outerObservation->getObservationIdText());
                                $this->set($innerObservation, 'lab_observation_value', $outerObservation->getObservationValue());
                                $this->set($innerObservation, 'lab_observation_units', $outerObservation->getObservationUnits());
                                $this->set($innerObservation, 'lab_observation_range', $outerObservation->getObservationRange());
                                $this->set($innerObservation, 'lab_observation_result_status', $outerObservation->getObservationResultStatus());



                                if ($this->checkNoEmpty($innerObservation, $this->identityAttrObs)) {
                                    /**
                                     * Abnormal flag
                                     */
                                    $flagId = $this->getAbnormalFlagId(
                                        $outerObservation->getObservationFlagCode(),
                                        $outerObservation->getObservationFlagDescription()
                                    );

                                    $this->set($innerObservation, 'lab_observation_abnormal_flags', $flagId);

                                    /**
                                     * Ntes
                                     */
                                    foreach($outerObservation->getNtes() as $outerNte) {
                                        /** @var RNte $outerNte */
                                        $innerNte = new LabsComplexNte();

                                        $this->incPrefix();
                                        $this->logBlockTitleByModel($innerNte);

                                        $this->set($innerNte, 'lab_nte', $outerNte->getNte());

                                        if ($this->checkNoEmpty($innerNte, $this->identityAttrNte)) {
                                            $innerObservation->ntes[] = $innerNte;
                                        }

                                        $this->decPrefix();
                                    }


                                    $innerRequest->observations[] = $innerObservation;
                                }

                                $this->decPrefix();
                            }
                            $model->requests[] = $innerRequest;
                        }

                        $this->decPrefix();
                    }



                    if ($this->checkNoEmpty($model, $this->identityAttr) && !$isSkipReport) {
                        $model->resourceContent = $resource->getResourceContent();
                        $model->lab_source = $resource->getLabSource($model->resourceContent);

                        $this->add($model);
                        $this->log("%GAdd new Lab%n");
                    } else {
                        $this->log("Skip record by empty identity values");
                    }
                } else {
                    $this->log('Skip observation resourse becouse it is not lab result');
                }
            }
            $this->log("");
        }
    }

    /**
     * @param $code
     * @param $description
     * @return integer
     */
    public function getAbnormalFlagId($code, $description)
    {
        if($code && $description) {
            $row = LabsObservationsFlags::findOne(['observation_flag_code' => $code]);

            if(!$row) {
                $model = new LabsObservationsFlags();
                $model->observation_flag_code        = $code;
                $model->observation_flag_description = $description;
                $model->observation_flag_status      = LabsObservationsFlags::FLAG_STATUS_ABNORMAL;

                if(!$model->save()) {
                    foreach($model->getErrors() as $error) {
                        $this->log("LabsObservationsFlags errors: $error");
                    }

                    return LabsObservationsFlags::DEFAULT_CODE_ID;
                }

                return $model->id;
            }

            return $row->id;
        }

        return LabsObservationsFlags::DEFAULT_CODE_ID;
    }

    /**
     * @param $model
     * @throws \ReflectionException
     */
    public function logBlockTitleByModel($model)
    {
        $className = (new ReflectionClass($model))->getShortName();
        $this->log("%gLoad $className...%n");
    }

    /**
     * @param Model $model
     * @param $field
     * @param $value
     */
    public function set(Model $model, $field, $value)
    {
        if ($model->hasProperty($field)) {
            $valueText = empty($value)?"%PEMPTY%n":"'%G{$value}%n'";
            $this->log("Set `{$field}` to {$valueText}");
            $model->$field = $value;
        } else {
            $this->error("Problems, model do not have attribute named '{$field}'");
        }
    }


    /**
     * @param $model
     * @param $identities
     *
     * @return bool
     */
    private function checkNoEmpty($model, $identities)
    {
        foreach ($identities as $attr) {
            if (!in_array($attr, ['practice_id', 'lab_report_internal_id']) && (!empty($model->$attr)) || $model->$attr === '0') {
                return true;
            }
        }
        return false;
    }

    /**
     * @param integer $portalID
     *
     * @return LabsComplex
     */
    protected function getModel($portalID){
        $practice = \Yii::$app->perfectParser->getPractice($portalID);
        $model = new LabsComplex([
            'lab_report_internal_id' => \Yii::$app->perfectParser->patient->internal_id,
            'practice_id' => $practice->practice_id,
            'display'     => $this->patient->display_by_default,
        ]);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPatientIdentity()
    {
        return [$this->patientIdentityColumn => $this->patient->internal_id];
    }

    /**
     * Compare specified attributes in two models
     *
     * @param LabsObservations|LabsReports|LabsRequests $model1 new model
     * @param LabsObservations|LabsReports|LabsRequests $model2 current model
     * @param string[] $compareAttributes
     * @param bool $identity
     *
     * @return boolean
     */
    protected function compareRecords($model1, $model2, $compareAttributes, $identity = false)
    {
        if ($identity) {
            return $this->identityRecords($model1, $model2);
        }

        foreach ($model1->getAttributes() as $attr => $val) {
            if (is_array($val)) {
                if (count($val) != count($model2->$attr)) {
                    return false;
                }
                foreach ($val as $subModel1) {
                    foreach ($model2->$attr as $subModel2) {
                        if ($this->compareRecords($subModel1, $subModel2, $compareAttributes)) {
                            return true;
                        }
                    }
                    return false;
                }
            } else {
                if (in_array($attr, $compareAttributes) && $model1->$attr !== $model2->$attr && !(is_null($model1->$attr) && is_null($model2->$attr))) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param LabsObservations|LabsReports $model1
     * @param LabsObservations|LabsReports $model2
     *
     * @return bool
     */
    private function identityRecords($model1, $model2)
    {
        foreach ($model1->attributes() as $attr1) {
            if ($attr1 == 'requests') {
                foreach ($model1->requests[0]->attributes() as $attr2) {
                    if ($attr2 == 'observations') {
                        foreach ($model1->requests[0]->observations as $observation1) {
                            foreach ($model2->requests[0]->observations as $observation2) {
                                if ($this->compareRecords($observation1, $observation2, $this->identityAttrObs)) {
                                    return true;
                                }
                            }
                            return false;
                        }
                    } else {
                        if (!$this->compareRecords($model1->requests[0], $model2->requests[0], $this->identityAttrReq)) {
                            return false;
                        }
                    }
                }
            } else {
                if (in_array($attr1, $this->identityAttr) && $model1->$attr1 !== $model2->$attr1 && !(is_null($model1->$attr1) && is_null($model2->$attr1))) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param ActiveRecord $currentModel
     * @param ActiveRecord $newModel
     *
     * @return array
     * @throws \Exception
     */
    protected function compileUpdateData($currentModel, $newModel){
        $data = array_udiff_assoc($newModel->getAttributes(), $currentModel->getAttributes(), function($a, $b){
            return (!is_array($a) && $a === $b) ? 0 : 1;
        });

        foreach ($data as $key => $value){
            if (!in_array($key, $this->compareAttr) && !is_array($value)) {
                unset($data[$key]);
            }
        }

        foreach($currentModel->primaryKey() as $key) {
            unset($data[$key]);
        }
        if (isset($newModel->resourceContent)){
            $data['resourceContent'] = $newModel->resourceContent;
        } else {
            throw new \Exception("Model " . get_class($newModel) . " must have resourceContent field ");
        }

        return $data ;
    }
}
