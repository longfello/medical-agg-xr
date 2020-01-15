<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 28.02.18
 * Time: 18:19
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Appointment;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;

/**
 * Class TParticipant
 * @package common\components\PerfectParser
 */
class TParticipant extends TComplex
{
    /** @var TCodeableConcept Role of participant in the appointment */
    public $type;

    /** @var TReference Person, Location/HealthcareService or Device */
    public $actor;

    /** @var TCode required | optional | information-only */
    public $required;

    /** @var TCode accepted | declined | tentative | needs-action */
    public $status;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['type', TCodeableConcept::class],
            ['actor', TReference::class],
            ['required', TCode::class],
            ['role', TCode::class],
        ];
    }

}