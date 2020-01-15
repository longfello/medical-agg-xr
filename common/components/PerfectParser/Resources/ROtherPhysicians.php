<?php

namespace common\components\PerfectParser\Resources;

use common\models\OtherPhysicians;
use common\components\PerfectParser\DataSources\MedFusion\resources\RPatient;
use common\components\PerfectParser\DataSources\MedFusion\resources\ROrganization;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TContactPoint;

/**
 * Class ROtherPhysicians
 * @package common\components\PerfectParser
 */
class ROtherPhysicians extends RResource
{
    /** @var string class name with namespace */
    public $modelClass = OtherPhysicians::class;

    /** @var string Field which identity patient */
    public $patientIdentityColumn = 'internal_id';
    
    /** @var string[] names of model's attributes to identity the same model */
    public $identityAttr = ['internal_id', 'practice_id', 'physician_name'];

    /** @var string[] names of model's attributes to compare with other same models */
    public $compareAttr = ['physician_phone', 'physician_specialty', 'address', 'city', 'state', 'zip', 'office_phone', 'cell_phone', 'email'];


    /**
     * @inheritdoc
     */
    public function load($resources){
        $portals = $this->getPortals($resources);
        foreach($portals as $portalID){
            $practice = \Yii::$app->perfectParser->getPractice($portalID);
            $portalResources = $this->getPortalResources($resources, $portalID);
            $portalFiltredResources = $this->filterResourses($portalResources, get_called_class());
            $this->log("Processing Organization resources for practice #{$practice->practice_id} (".count($portalFiltredResources)." records of ".count($portalResources).")");

            foreach ($portalFiltredResources as $i => $resource) {
                /** @var $resource RPatient */
                $this->log("%BIteration ". (1+$i)." of ".count($portalFiltredResources).'%n');
                $this->incPrefix();

                if (isset($resource->managingOrganization)) {
                    $organization = $resource->getReferencedResource($resource->managingOrganization);
                    if ($organization && $organization instanceof ROrganization && $organization->name && $physicianName = $organization->name->getValue()) {
                        $this->log("%cFound referenced Organization %C {$physicianName}%n");
                        $model = $this->getModel($portalID);
                        
                        $model->physician_name = $physicianName;
                        $model->physician_phone = $organization->getPhone('', true);
                        $model->physician_specialty = '';
                        $model->address = $organization->getAddress(ROrganization::ADDRESS_LINE);
                        $model->city = $organization->getAddress(ROrganization::ADDRESS_CITY);
                        $model->state = $organization->getAddress(ROrganization::ADDRESS_STATE);
                        $model->zip = $organization->getAddress(ROrganization::ADDRESS_ZIP);
                        $model->office_phone = $organization->getPhone(TContactPoint::PHONE_WORK, true);
                        $model->cell_phone = $organization->getPhone(TContactPoint::PHONE_MOBILE, true);
                        $model->email = $organization->getEmail(true);

                        $this->add($model);
                    } else {
                        $this->log("%cReferenced Organization not found in this resource%n");
                    }
                } else {
                    $this->log("%cReferenced Organization not found in this resource%n");
                }
                $this->decPrefix();
                $this->log("");
            }
        }
    }

    /**
     * @param $portalID
     *
     * @return OtherPhysicians
     */
    protected function getModel($portalID){
        $practice = \Yii::$app->perfectParser->getPractice($portalID);
        $model = new OtherPhysicians();
        $model->internal_id = \Yii::$app->perfectParser->patient->internal_id;
        $model->practice_id = $practice->practice_id;
        $model->main_physician = 0;
        $model->allow_alerts = 0;
        $model->display = $this->patient->display_by_default;

        return $model;
    }

}
