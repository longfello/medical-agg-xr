<?php

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Practitioner;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod;
use common\components\PerfectParser\DataSources\MedFusion\resources\RResource;
use common\components\PerfectParser\DataSources\MedFusion\resources\ROrganization;

/**
 * Class TPractitionerRole
 * @package common\components\PerfectParser
 */
class TPractitionerRole extends TComplex
{
    /** @var TReference|null Organization where the roles are performed */
    public $managingOrganization;

    /** @var TCodeableConcept|null Roles which this practitioner may perform */
    public $role;

    /** @var TCodeableConcept[]|null Specific specialty of the practitioner */
    public $specialty;

    /** @var TPeriod|null The period during which the practitioner is authorized to perform in these role(s) */
    public $period;

    /** @var TReference[]|null The location(s) at which this practitioner provides care */
    public $location;

    /** @var TReference[]|null The list of healthcare services that this worker provides for this role's Organization/Location(s) */
    public $healthcareService;


    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['managingOrganization', TReference::class],
            ['role', [TCodeableConcept::class]],
            ['specialty', [TCodeableConcept::class]],
            ['period', TPeriod::class],
            ['location', [TReference::class]],
            ['healthcareService', [TReference::class]],
        ];
    }

    /**
     * Gets name of the managing Organization,
     *  defined as nested reference in parent resource
     *  and related from current Practitioner
     *
     * @param RResource $parentResource
     * @return string|null
     */
    public function getManagingOrganizationName(RResource $parentResource)
    {
        if (isset($this->managingOrganization)) {
            $nestedReference = $parentResource->getReferencedResource($this->managingOrganization);
            if ($nestedReference instanceof ROrganization) {
                if (isset($nestedReference->name) && ($name = $nestedReference->name->getValue())) {
                    return $name;
                }
            }
        }
        return null;
    }

}
