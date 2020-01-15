<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:11
 */

namespace common\components\PerfectParser\Resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\RImmunization;
use common\models\Vaccinations;

/**
 * Class RVaccinations
 * @package common\components\PerfectParser
 */
class RVaccinations extends RResource
{
    /** @var string class name with namespace */
    public $modelClass = Vaccinations::class;

    /** @var string Field which identity patient */
    public $patientIdentityColumn = 'internal_id';
    
    /** @var string[] names of model's attributes to identity the same model */
    public $identityAttr = ['internal_id', 'practice_id', 'vaccination', 'vaccination_date'];

    /** @var string[] names of model's attributes to compare with other same models */
    public $compareAttr = [];


    /**
     * @inheritdoc
     */
    public function load($resources){
        $portals = $this->getPortals($resources);
        foreach($portals as $portalID){
            $practice = \Yii::$app->perfectParser->getPractice($portalID);
            $portalResources = $this->getPortalResources($resources, $portalID);
            $portalFiltredResources = $this->filterResourses($portalResources, get_called_class());
            $this->log("Processing Immunization resources for practice #{$practice->practice_id} (".count($portalFiltredResources)." records of ".count($portalResources).")");
            foreach ($portalFiltredResources as $i => $resource) {
                /** @var $resource RImmunization */
                $this->log("%BIteration ". (1+$i)." of ".count($portalFiltredResources).'%n');
                $this->incPrefix();
                if (is_null($resource->status) || $resource->status->getValue() != RImmunization::STATUS_ENTERED_IN_ERROR){
                    if ($resource->vaccineCode) {
                        $model = $this->getModel($portalID);
                        if ($model->vaccination = $resource->vaccineCode->getValue()){
                            if ($resource->date) {
                                $model->vaccination_date = $resource->date->asDate();
                                $this->log("Found valid value, set vaccination_date to '{$model->vaccination_date}'");
                            }
                            $this->log("Found valid value, set vaccination to '{$model->vaccination}'");
                            $this->log("%GAdd Vacctination record '{$model->vaccination}' at '$model->vaccination_date'%n");
                            $this->add($model);
                        } else $this->log("Skip record because vaccineCode is empty");
                    } else $this->log("Skip record because vaccineCode is absent");
                } else $this->log("Skip record by status = ".$resource->status->getValue());
                $this->decPrefix();
            }
            $this->log("");
        }
    }

    /**
     * @param $portalID
     *
     * @return Vaccinations
     */
    protected function getModel($portalID){
        $practice = \Yii::$app->perfectParser->getPractice($portalID);
        $model = new Vaccinations();
        $model->internal_id = \Yii::$app->perfectParser->patient->internal_id;
        $model->practice_id = $practice->practice_id;
        $model->display = $this->patient->display_by_default;

        return $model;
    }

}
