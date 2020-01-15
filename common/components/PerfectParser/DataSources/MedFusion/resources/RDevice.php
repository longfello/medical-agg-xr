<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:02
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAnnotation;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TContactPoint;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TReference;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TDateTime;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUri;

/**
 * Class RDevice
 * @package common\components\PerfectParser
 */
class RDevice extends RResource
{
    /** @var TIdentifier[] Instance id from manufacturer, owner, and others */
    public $identifier;

    /** @var TString|null  */
    public $resourceType;

    /** @var TAnnotation Device notes and comments */
    public $note;

    /** @var TCodeableConcept What kind of device this is */
    public $type;

    /** @var TCode available | not-available | entered-in-error */
    public $status;

    /** @var TString Name of device manufacturer */
    public $manufacturer;

    /** @var TString Model id assigned by the manufacturer */
    public $model;

    /** @var TString Version number (i.e. software) */
    public $version;

    /** @var TDateTime Manufacture date */
    public $manufactureDate;

    /** @var TDateTime Date and time of expiry of this device (if applicable) */
    public $expiry;

    /** @var TString FDA mandated Unique Device Identifier */
    public $udi;

    /** @var TString Lot number of manufacture */
    public $lotNumber;

    /** @var TString Organization responsible for device */
    public $owner;

    /** @var TString Where the resource is found */
    public $location;

    /** @var TString If the resource is affixed to a person */
    public $patient;

    /** @var TContactPoint Details for human/organization for support */
    public $contact;

    /** @var TUri Network address to contact device */
    public $url;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['identifier', [TIdentifier::class]],
            ['resourceType', TString::class],
            ['type', TCodeableConcept::class],
            ['note', [TAnnotation::class]],
            ['status', TCode::class],
            ['contact', TContactPoint::class],
            ['url', TUri::class],
            [['owner', 'location', 'patient'], TReference::class],
            [['manufactureDate', 'expiry'], TDateTime::class],
            [['manufacturer', 'model', 'version', 'udi', 'lotNumber'], TString::class],
        ];
    }}