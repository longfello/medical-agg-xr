<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:10
 */

namespace common\components\PerfectParser\Resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TContactPoint;
use common\components\PerfectParser\DataSources\MedFusion\resources\ROrganization;
use common\components\PerfectParser\DataSources\MedFusion\resources\RPatient;
use common\models\Partners;
use common\models\PatientInfo;
use common\models\Practices;
use yii\db\ActiveQuery;
use yii\helpers\StringHelper;

/**
 * Class RPractices
 * @package common\components\PerfectParser
 */
class RPractices extends RResource
{
    /** @var string class name with namespace */
    public $modelClass = Practices::class;

    /**
     * @var bool
     */
    public $processScenario = false;

    /** @var string[] names of model's attributes to identity the same model */
    public $identityAttr = ['practice_umr_id'];

    /** @var string[] names of model's attributes to compare with other same models */
    public $compareAttr = ['practice_name', 'cell_phone'];


    /**
     * @inheritdoc
     */
protected function getCurrentModels(){
        if (class_exists($this->modelClass)){
            $query = call_user_func(array($this->modelClass, 'find'));
            /** @var $query ActiveQuery */
            return $query->innerJoin(PatientInfo::tableName().' lpi', 'lpi.practice_id = life_practices.practice_id' )
                         ->where([
                             'lpi.patients_id' => $this->patient->patients_id,
                             'life_practices.partner_id' => Partners::PARTNER_MEDFUSION
                         ])->all();
        } else {
            throw new \Exception("Class not found: ".$this->modelClass, 500);
        }
    }

    /**
     * @inheritdoc
     *
     * @param \common\components\PerfectParser\DataSources\MedFusion\resources\RResource[] $resources
     *
     * @throws \yii\base\Exception
     * @throws \Exception
     */
    public function load($resources){
        $new = 0;
        $portals = $this->getPortals($resources);
        foreach ($portals as $portalID) {
            $portalResources = $this->getPortalResources($resources, $portalID);
            $portalFiltredResources = $this->filterResourses($portalResources, get_called_class());
            $this->log("Processing Patient[] resources for portal #{$portalID} (".count($portalFiltredResources)." records of ".count($portalResources).")");
            $practiceName = $practicePhone = '';
            foreach ($portalFiltredResources as $i => $resource) {
                /** @var $resource RPatient */
                $this->log("%BIteration " . (1 + $i) . " of " . count($portalFiltredResources) . ", resource base class = " . StringHelper::basename(get_class($resource)) . '%n');
                $this->incPrefix();
                if ($resource->managingOrganization ){
                    $organization = $resource->getReferencedResource($resource->managingOrganization);
                    if ($organization){
                        /** @var $organization ROrganization */
                        if ($organization->name){
                            $name = $organization->name->getValue(true);
                            if ($name) {
                                $practiceName = $name;
                                $this->log("Found Organization Name '{$practiceName}'");
                            }
                        }
                        $phone = $organization->getPhone(TContactPoint::PHONE_MOBILE);
                        if ($phone){
                            $practicePhone = $phone;
                            $this->log("Found Organization Mobile Phone '{$practicePhone}'");
                        }
                    }
                }
                $this->decPrefix();
            }

            $practice = \Yii::$app->perfectParser->getPractice($portalID);
            if (!$practice) {
                $practice = \Yii::$app->perfectParser->createPractice($portalID);
                $new++;
            }
            if ($practiceName) {
                $practice->practice_name = $practiceName;
            }
            if ($practicePhone) {
                $practice->cell_phone = $practicePhone;
            }

            if (!$practice->practice_umr_id) $practice->practice_umr_id = $portalID;
            if (!$practice->auth_user)       $practice->auth_user = \Yii::$app->security->generateRandomString(50);

            if (!$practice->save()){
                \Yii::$app->perfectParser->error($practice->getErrors());
            }

            $this->log("%GAdd Practice record '{$practice->practice_name}' with umrID = '$practice->practice_umr_id'%n");
            $this->add($practice);
        }
        $this->log("Found {$new} new of ".count($portals)." total entries in ".count($resources)." blocks");
    }

}