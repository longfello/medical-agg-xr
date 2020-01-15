<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 27.02.18
 * Time: 16:06
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Patient;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TAddress;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TCodeableConcept;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TComplex;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TContactPoint;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\THumanName;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TPeriod;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;

/**
 * Class TContact
 * @package common\components\PerfectParser
 *
 * @property null|string $preferred
 * @property null|string $email
 */
class TContact extends TComplex
{
    /** @var TCodeableConcept[]|TArray The kind of relationship */
    public $relationship;

    /** @var THumanName[]|TArray A name associated with the contact person */
    public $name;

    /** @var TContactPoint[]|TArray A contact detail for the person */
    public $telecom;

    /** @var TAddress[]|TArray Address for the contact person */
    public $address;

    /** @var TCode male | female | other | unknown */
    public $gender;

    /** @var TPeriod The period during which this contact person or organization is valid to be contacted relating to this patient */
    public $period;

    /**
     * @inheritdoc
     * @return array
     */
    public function structure()
    {
        return [
            ['relationship', [TCodeableConcept::class]],
            ['name', [THumanName::class]],
            ['telecom', [TContactPoint::class]],
            ['address', [TAddress::class]],
            ['gender', TCode::class],
            ['period', TPeriod::class],
        ];
    }

    /**
     * Return name by rules from https://commlifesolutions.atlassian.net/wiki/spaces/MT/pages/9535543/MedFusion+Integration
     * @return array|string
     */
    public function getName(){
        $names = $this->name;
        /** @var $names TArray|THumanName[] */
        $name = false;
        if ($names) {
            $name = $names->implodeField('text');
            if (!$name) {
                $name   = [];
                $name[] = $names->implodeField('prefix');
                $name[] = $names->implodeField('given');
                $name[] = $names->implodeField('family');
                $name[] = $names->implodeField('suffix');
                $name = array_filter($name);
                $name = implode(' ', $name);
            }
        }
        return $name;
    }

    /**
     * Return first phone number by given type
     * @param string $phone_type
     *
     * @return string|null
     */
    public function getPhone($phone_type = TContactPoint::PHONE_MOBILE){
        $source = $this->telecom;
        if ($source){
            $source = $source->filterByField('system', TContactPoint::PHONE);
            $phones = $source->filterByField('use', $phone_type);
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
     * @return string|null
     */
    public function getEmail(){
        if ($this->telecom){
            $emails = $this->telecom->filterByField('system', TContactPoint::EMAIL);
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
     * @return null|string
     */
    public function getPreferred(){
        $elements = [];
        foreach($this->telecom as $one){
            /** @var $one TContactPoint */
            if ($one->usable()){
                $elements[] = $one;
            }
        }
        $elements = new TArray($elements);

        $elements = $elements->filterByLowerField('rank');
        if (count($elements)){
            $values = [];
            foreach($elements as $one){
                /** @var $one TContactPoint */
                if ($one->use && $one->system){
                    $els = [trim($one->use->getValue()), trim($one->system->getValue())];
                    $values[] =  trim(implode(' ', $els)) ;
                }
            }
            if ($values) {
                return implode('|', $values);
            }
        }
        return '';
    }
}