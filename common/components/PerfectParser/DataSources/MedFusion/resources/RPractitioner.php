<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:02
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Practitioner\TQualification;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Practitioner\TPractitionerRole;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAddress;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAttachment;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TContactPoint;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\THumanName;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDate;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class RPractitioner
 * @package common\components\PerfectParser
 */
class RPractitioner extends RResource
{
    /** @var TString[]|TArray|null */
    public $id;

    /** @var TIdentifier A identifier for the person as this agent */
    public $identifier;

    /** @var TBoolean Whether this practitioner's record is in active use */
    public $active;

    /** @var THumanName A name associated with the person */
    public $name;

    /** @var TContactPoint[] A contact detail for the practitioner */
    public $telecom;

    /** @var TAddress[] Where practitioner can be found/visited */
    public $address;

    /** @var TCode male | female | other | unknown */
    public $gender;

    /** @var TDate The date on which the practitioner was born */
    public $birthDate;

    /** @var TAttachment Image of the person */
    public $photo;

    /** @var TQualification Qualifications obtained by training and certification */
    public $qualification;

    /** @var TPractitionerRole[] Roles/organizations the practitioner is associated with */
    public $practitionerRole;

    /** @var TQualification Qualifications obtained by training and certification */
    public $communication;

    /** @var TString|null  */
    public $resourceType;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['id', [TString::class]],
            ['identifier', [TIdentifier::class]],
            ['active', TBoolean::class ],
            ['name', THumanName::class],
            ['telecom', [TContactPoint::class]],
            ['address', [TAddress::class]],
            ['gender', TCode::class],
            ['birthDate', TDate::class],
            ['photo', TAttachment::class],
            ['qualification', TQualification::class],
            ['practitionerRole', [TPractitionerRole::class]],
            ['communication', TCodeableConcept::class],
            ['resourceType', TString::class],
        ];
    }

    /**
     * Gets Practitioner's name
     * @return string|null
     */
    public function getName()
    {
        if (isset($this->name) && ($name = $this->name->format("{name()}"))) {
            return $name;
        }
        return null;
    }

}
