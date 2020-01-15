<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.02.18
 * Time: 18:01
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Bundle\TEntry;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\Bundle\TLink;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\complex\TSignature;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TUnsignedInt;

/**
 * Class RBundle
 * @package common\components\PerfectParser
 *
 * @property null|string|mixed $medicationDirectionToPatient
 * @property null|string $medicationEndDate
 * @property null|mixed $medicationText
 * @property null|string $medicationRoute
 * @property null|mixed $medicationStatus
 * @property array $medicationNames
 * @property null|mixed|int $medicationNumRefills
 * @property null|string $medicationStrange
 * @property null|bool|string $medicationDoseTiming
 * @property null|bool|string $medicationDuration
 * @property null|string $medicationDatePrescribed
 * @property null|string $medicationDoseUnit
 * @property null|mixed $status
 */
class RBundle extends RResource
{
   /** @var TCode document | message | transaction | transaction-response | batch | batch-response | history | searchset | collection */
    public $type;

   /** @var TUnsignedInt If search, the total number of matches */
    public $total;

   /** @var TLink[]|TArray|null Links related to this Bundle */
    public $link;

   /**  @var TEntry[]|TArray|null Entry in the bundle - will have a resource, or information must be a resource unless there's a request or response. The fullUrl element must be present when a resource is present, and not present otherwise */
    public $entry;

   /** @var TSignature Digital Signature */
    public $signature;

    /** @var TString|null  */
    public $resourceType;

    /** @var string */
    public $identity;

    /**
     * @return array
     */
    public function structure()
   {
        return [
            ['resourceType', TString::class],
            ['type', TCode::class, self::REQUIRED],
            ['total', TUnsignedInt::class],
            ['link', [TLink::class]],
            ['entry', [TEntry::class]],
            ['signature', TSignature::class]
        ];
   }

    /**
     * @return mixed|null
     */
    public function getStatus(){
       try{
           if ($this->entry){
               foreach ($this->entry as $entry){
                   /** @var $entry TEntry */
                   if ($resource = $entry->resource){
                       /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                       $status = $resource->getStatus();
                       if (!is_null($status)){
                           return $status;
                       }
                   }
               }
           }
       } catch(\Exception $e){}

       return null;
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function explodeResourseByMedicationName(){
       $datas = [];
       $names = $this->getMedicationNames();
       switch(count($names)){
           case 0:
               $datas = [];
               break;
           default:
               foreach ($names as $name){
                   $data = $this->extractResourseByMedicationName($name);
                   if ($data){
                       $datas[] = $data;
                   }
               }
       }

       return $datas;
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function getMedicationNames(){
        $names = [];
        try{
            if ($this->entry){
                foreach ($this->entry as $entry){
                    /** @var $entry TEntry */
                    if ($resource = $entry->resource){

                        /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                        $name = $resource->getMedicationText();
                        if ($name){
                            $names[$name] = $name;
                        }
                    }
                }
            }
        } catch(\Exception $e){}

        return $names;
    }

    /**
     * @param $name
     *
     * @return RBundle|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function extractResourseByMedicationName($name){
        $data = new RBundle();
        $data->load($this->getOriginalValue(), true);

        try{
            if ($data->entry){
                foreach ($data->entry->getValue() as $index => $entry){
                    /** @var $entry TEntry */
                    if ($resource = $entry->resource){
                        /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                        $resource_name = $resource->getMedicationText();
                        if ($resource_name !== $name){
                            unset($data->entry[$index]);
                        }
                    } else $this->log('%YEmpty resource%n');
                }
            }
        } catch(\Exception $e){
            $data = null;
        }
        return $data;
    }

    /**
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function getMedicationText(){
       $names = $this->getMedicationNames();
       if (count($names) > 0) {
           return current($names);
       }
       return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationStrange(){
        try{
            if ($this->entry){
                foreach ($this->entry->getValue() as $index => $entry){
                    /** @var $entry TEntry */
                    if ($resource = $entry->resource){
                        /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                        if ($resource->medicationReference){
                            $subresource = $resource->getReferencedResource($resource->medicationReference);
                            if ($subresource){
                                /** @var $subresource RMedication */
                                if ($data = $subresource->getStrange()) return $data;
                            }
                        }

                    } else $this->log('%YEmpty resource%n');
                }
            }
        } catch(\Exception $e){}
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationRoute(){
        $order = [RMedicationOrder::class, RMedicationDispense::class, RMedicationAdministration::class, RMedicationStatement::class];
        foreach($order as $class) {
            try {
                if ($this->entry) {
                    foreach ($this->entry->getValue() as $index => $entry) {
                        /** @var $entry TEntry */
                        if ($resource = $entry->resource) {
                            if ($resource instanceof $class) {
                                /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                                if ($data = $resource->getMedicationRoute()) {
                                    return $data;
                                }
                            }
                        } else $this->log('%YEmpty resource%n');
                    }
                }
            } catch (\Exception $e) {
            }
        }
        return null;
    }

    /**
     * @return bool|null|string
     */
    public function getMedicationDuration()
    {
        try{
            if ($this->entry){
                foreach ($this->entry->getValue() as $index => $entry){
                    /** @var $entry TEntry */
                    if ($resource = $entry->resource){
                        /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                        if ($data = $resource->getMedicationDuration()) return $data;
                    } else $this->log('%YEmpty resource%n');
                }
            }
        } catch(\Exception $e){}
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationDoseUnit()
    {
        try{
            if ($this->entry){
                foreach ($this->entry->getValue() as $index => $entry){
                    /** @var $entry TEntry */
                    if ($resource = $entry->resource){
                        /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                        if ($data = $resource->getMedicationDoseUnit()) return $data;
                    } else $this->log('%YEmpty resource%n');
                }
            }
        } catch(\Exception $e){}
        return null;
    }

    /**
     * @return bool|null|string
     */
    public function getMedicationDoseTiming()
    {
        $order = [RMedicationOrder::class, RMedicationDispense::class, RMedicationStatement::class];
        foreach($order as $class){
            try{
                if ($this->entry){
                    foreach ($this->entry->getValue() as $index => $entry){
                        /** @var $entry TEntry */
                        if ($resource = $entry->resource){
                            if ($resource instanceof $class) {
                                /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                                if ($data = $resource->getMedicationDoseTiming()) return $data;
                            }
                        } else $this->log('%YEmpty resource%n');
                    }
                }
            } catch(\Exception $e){}
        }
        return null;
    }

    /**
     * @return int|mixed|null
     */
    public function getMedicationNumRefills()
    {
        $order = [RMedicationOrder::class];
        foreach($order as $class){
            try{
                if ($this->entry){
                    foreach ($this->entry->getValue() as $index => $entry){
                        /** @var $entry TEntry */
                        if ($resource = $entry->resource){
                            if ($resource instanceof $class) {
                                /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                                if ($data = $resource->getMedicationNumRefills()) return $data;
                            }
                        } else $this->log('%YEmpty resource%n');
                    }
                }
            } catch(\Exception $e){}
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationDatePrescribed()
    {
        $order = [RMedicationOrder::class, RMedicationDispense::class, RMedicationAdministration::class, RMedicationStatement::class];
        foreach($order as $class){
            try{
                if ($this->entry){
                    foreach ($this->entry->getValue() as $index => $entry){
                        /** @var $entry TEntry */
                        if ($resource = $entry->resource){
                            if ($resource instanceof $class) {
                                /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                                if ($data = $resource->getMedicationDatePrescribed()) return $data;
                            }
                        } else $this->log('%YEmpty resource%n');
                    }
                }
            } catch(\Exception $e){}
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getMedicationEndDate()
    {
        $order = [RMedicationStatement::class, RMedicationAdministration::class];
        foreach($order as $class){
            try{
                if ($this->entry){
                    foreach ($this->entry->getValue() as $index => $entry){
                        /** @var $entry TEntry */
                        if ($resource = $entry->resource){
                            if ($resource instanceof $class) {
                                /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                                if ($data = $resource->getMedicationEndDate()) return $data;
                            }
                        } else $this->log('%YEmpty resource%n');
                    }
                }
            } catch(\Exception $e){}
        }
        return null;
    }

    /**
     * @return mixed|null
     */
    public function getMedicationStatus()
    {
        $order = [RMedicationOrder::class, RMedicationDispense::class, RMedicationAdministration::class, RMedicationStatement::class];
        foreach($order as $class){
            try{
                if ($this->entry){
                    foreach ($this->entry->getValue() as $index => $entry){
                        /** @var $entry TEntry */
                        if ($resource = $entry->resource){
                            if ($resource instanceof $class) {
                                /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                                if ($data = $resource->getMedicationStatus()) return $data;
                            }
                        } else $this->log('%YEmpty resource%n');
                    }
                }
            } catch(\Exception $e){}
        }
        return null;
    }

    /**
     * @return mixed|null|string
     */
    public function getMedicationDirectionToPatient()
    {
        $order = [RMedicationOrder::class, RMedicationDispense::class, RMedicationAdministration::class, RMedicationStatement::class];
        foreach($order as $class){
            try{
                if ($this->entry){
                    foreach ($this->entry->getValue() as $index => $entry){
                        /** @var $entry TEntry */
                        if ($resource = $entry->resource){
                            if ($resource instanceof $class) {
                                /** @var $resource RMedicationOrder|RMedicationDispense|RMedicationAdministration|RMedicationStatement */
                                if ($data = $resource->getMedicationDirectionToPatient()) return $data;
                            }
                        } else $this->log('%YEmpty resource%n');
                    }
                }
            } catch(\Exception $e){}
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function load($data, $turnOffLogging = false)
    {
        $oldDebug = $this->debug;
        if ($turnOffLogging) $this->debug = false;
        $data = parent::load($data);
        $this->debug = $oldDebug;
        return $data;
    }
}