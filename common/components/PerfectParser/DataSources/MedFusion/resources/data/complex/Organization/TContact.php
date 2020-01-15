<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 14.02.18
 * Time: 12:07
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Organization;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAddress;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TContactPoint;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\THumanName;

/**
 * Class TContact
 * @package common\components\PerfectParser
 */
class TContact extends TComplex
{
    /** @var TCodeableConcept The type of contact */
    public $purpose;
    /** @var THumanName A name associated with the contact */
    public $name;
    /** @var TContactPoint Contact details (telephone, email, etc.) for a contact */
    public $telecom;
    /** @var TAddress Visiting or postal addresses for the contact */
    public $address;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['purpose', TCodeableConcept::class],
            ['name', THumanName::class],
            ['telecom', TContactPoint::class],
            ['address', TAddress::class]
        ];
    }
}