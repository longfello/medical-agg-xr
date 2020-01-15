<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:10
 */

namespace common\components\PerfectParser\Resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Patient\TContact;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TContactPoint;
use common\components\PerfectParser\DataSources\MedFusion\resources\RPatient;
use common\models\EmergencyContacts;

/**
 * Class REmergencyContacts
 * @package common\components\PerfectParser
 */
class REmergencyContacts extends RResource
{
    /** @var string class name with namespace */
    public $modelClass = EmergencyContacts::class;

    /** @var string Field which identity patient */
    public $patientIdentityColumn = 'internal_id';

    /** @var string[] names of model's attributes to identity the same model */
    public $identityAttr = ['internal_id', 'contact_name', 'practice_id'];

    /** @var string[] names of model's attributes to compare with other same models */
    public $compareAttr = ['contact_cell', 'contact_email', 'notify_email', 'notify_cell', 'contact_preferred', 'invisible_entry'];


    /**
     * @inheritdoc
     */
    public function load($resources){
        $portals = $this->getPortals($resources);
        foreach($portals as $portalID){
            $practice = \Yii::$app->perfectParser->getPractice($portalID);
            $portalResources = $this->getPortalResources($resources, $portalID);
            $portalFiltredResources = $this->filterResourses($portalResources, get_called_class());
            $this->log("Processing Patient resources for practice #{$practice->practice_id} (".count($portalFiltredResources)." records of ".count($portalResources).")");
            foreach ($portalFiltredResources as $i => $resource) {
                /** @var $resource RPatient */
                $this->log("%BIteration ". (1+$i)." of ".count($portalFiltredResources).'%n');
                $this->incPrefix();
                if ($resource->contact){
                    $this->log("Found ".count($resource->contact)." contacts. Processing...");
                    foreach ($resource->contact as $contact){
                        /** @var $contact TContact */
                        $model = new EmergencyContacts();
                        $model->practice_id = $practice->practice_id;
                        $model->internal_id = $this->patient->internal_id;
                        $model->contact_name = $contact->getName();
                        if ($model->contact_name){
                            $model->contact_phone = (string)$contact->getPhone(TContactPoint::PHONE_HOME);
                            $model->contact_cell  = (string)$contact->getPhone(TContactPoint::PHONE_MOBILE);
                            $model->contact_email = (string)$contact->getEmail();
                            $model->contact_preferred = (string)$contact->getPreferred();
                            $model->notify_cell = 0;
                            $model->notify_email = 0;
                            $model->added_by_user = 0;
                            $model->invisible_entry = 0;
                            $model->display = $this->patient->display_by_default;
                            $this->log("%GAdd EmergencyContact record '{$model->contact_name}'%n");
                            $this->add($model);
                        } else {
                            $this->log("Skip contact because no contact_name given");
                        }
                    }
                } else $this->log("%RSkip contact because no contact given%n");
                $this->decPrefix();
            }
        }
    }

}
