<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 17:14
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;

use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TInteger;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use yii\helpers\StringHelper;

/**
 * Class RResource
 * @package common\components\PerfectParser
 */
abstract class RResource extends TComplex
{
    /** @var TInteger PortalID */
    public $portalID;
    /** @var TDateTime Last update date/time */
    public $updated_at;
    /** @var TString Resource Type */
    public $resourceType;
    /** @var string */
    public $identity;

    /**
     * @param $resourceName
     * @param null $elName
     * @param null|string|TArray $identity
     *
     * @return RResource|null
     */
    public static function create($resourceName, $elName = null, $identity = null){
        $className = 'common\components\PerfectParser\DataSources\MedFusion\resources\R'.$resourceName;
        if (class_exists($className)){
            /** @var $resourceModel RResource */
            $resourceModel = new $className();

            $elName = is_null($elName)? StringHelper::basename($resourceName) :$elName;

            $resourceModel->elName = $elName;
            if ($identity) {
                $resourceModel->identity = (is_string($identity) ? $identity : $identity[0]->getValue());
            }

            return $resourceModel;
        } else {
            return null;
        }
    }

    /**
     * Get referenced resource, contained in current resource and described in given reference
     * @param TReference $reference
     * @return RResource | null
     */
    public function getReferencedResource(TReference $reference)
    {
        if (substr($reference->reference->getValue(), 0, 1) == '#') { // this reference is related to contained resource
            $referenceId = substr($reference->reference->getValue(), 1);
            if (isset($this->contained)) {
                foreach ($this->contained as $referenceItem) {
                    if ($referenceItem->id){
                        $ids = ($referenceItem->id instanceof TArray)?$referenceItem->id:new TArray($referenceItem->id);
                        foreach ($ids as $id) {
                            $idValue = is_object($id)?$id->getValue():$id;
                            if ($idValue == $referenceId) {
                                return $referenceItem;
                            }
                        }
                    }
                }
            }
        }
        return null;
    }

}
