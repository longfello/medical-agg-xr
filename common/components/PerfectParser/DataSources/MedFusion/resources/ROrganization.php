<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:02
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Organization\TContact;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAddress;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TContactPoint;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TIdentifier;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TBoolean;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\models\States;

/**
 * Class ROrganization
 * @package common\components\PerfectParser
 */
class ROrganization extends RResource
{
    /** @var TString[]|TArray|null */
    public $id;
    /** @var TIdentifier[]|null Whether the organization's record is still in active use */
    public $identifier;
    /** @var TBoolean|null Whether the organization's record is still in active use */
    public $active;
    /** @var TCodeableConcept|null Kind of organization */
    public $type;
    /** @var TString|null Name used for the organization */
    public $name;
    /** @var TContactPoint[]|TArray|null A contact detail for the organization */
    public $telecom;
    /** @var TAddress[]|null An address for the organization */
    public $address;
    /** @var TContact|null Contact for the organization for a certain purpose */
    public $contact;
    /** @var TString|string|null  */
    public $resourceType;


    /** @const string */
    const ADDRESS_LINE = 'line';

    /** @const string */
    const ADDRESS_CITY = 'city';

    /** @const string */
    const ADDRESS_STATE = 'state';

    /** @const string */
    const ADDRESS_ZIP = 'postalCode';


    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['id', [TString::class]],
            ['identifier', [TIdentifier::class]],
            ['active', TBoolean::class],
            ['type', TCodeableConcept::class],
            ['name', TString::class],
            ['telecom', [TContactPoint::class]],
            ['address', [TAddress::class]],
            ['contact', TContact::class],
            ['resourceType', TString::class]
        ];
    }

    /**
     * Return first phone number by given type
     * @param string $phone_type
     * @param bool $order_by_rank
     *
     * @return string|null
     */
    public function getPhone($phone_type = TContactPoint::PHONE_MOBILE, $order_by_rank = false) {
        $source = $this->telecom;
        if ($source){
            $source = $source->filterByField('system', TContactPoint::PHONE);
            if ($phone_type) {
                $phones = $source->filterByField('use', $phone_type);
            } else {
                $phones = $source;
            }

            if ($order_by_rank) {
                $phones = $phones->filterByLowerField('rank', false);
            }

            foreach ($phones as $phone){
                /** @var $phone TContactPoint */
                if ($value = trim($phone->value->getValue())){
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * Return first email
     *
     * @param bool $order_by_rank
     *
     * @return string|null
     */
    public function getEmail($order_by_rank = false){
        if ($this->telecom){
            $emails = $this->telecom->filterByField('system', TContactPoint::EMAIL);
            if ($order_by_rank) {
                $emails = $emails->filterByLowerField('rank', false);
            }

            foreach($emails as $email){
                /** @var $email TContactPoint */
                $value = trim($email->value->getValue());
                if ($value) {
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * Gets Organization's name
     * @return string|null
     */
    public function getName()
    {
        if (isset($this->name) && ($name = $this->name->getValue())) {
            return $name;
        }
        return null;
    }

    /**
     * Gets Organization's address as first specified element of first address item
     * @param string $element
     * @return string|null
     */
    public function getAddress($element)
    {
        if (isset($this->address)) {
            if (isset($this->address[0]->$element)) {
                $result = trim($this->address[0]->$element instanceof TArray ? $this->address[0]->$element[0] : $this->address[0]->$element);
                if ($element == self::ADDRESS_STATE) {
                    if (strlen($result) == 2) {
                        return strtoupper($result);
                    } else {
                        $result = ucwords(strtolower($result));
                        foreach (States::getStates() as $st => $state) {
                            if ($state == $result) {
                                return $st;
                            }
                        }
                    }
                } else {
                    return $result;
                }
            }
        }
        return null;
    }

}
