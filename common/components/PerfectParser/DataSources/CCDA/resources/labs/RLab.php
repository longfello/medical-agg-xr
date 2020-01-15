<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 21.10.18
 * Time: 15:39
 */

namespace common\components\PerfectParser\DataSources\CCDA\resources\labs;


use common\components\PerfectParser\DataSources\CCDA\resources\RResource;
use common\helpers\Convert;
use SimpleXMLElement;

/**
 * Class RLab
 * @package common\components\PerfectParser\DataSources\CCDA\resources\labs
 */
class RLab extends RResource
{
    /** @var string  */
    const INVALID_NULL_FLAVOR = 'NI';

    /** @var null  */
    public $containerName = null;

    /** @var SimpleXMLElement */
    public $effectiveTime;

    /** @var SimpleXMLElement */
    public $act;

    /** @var SimpleXMLElement */
    public $organizer;


    /**
     * Get external requests resources
     * @return array
     */
    public function getRequests()
    {
        $result = [];
        $organizer = null;

        /**
         * Get organizer
         */
        // first try
        if (!empty($this->organizer)) {
            $organizerDefault = !is_array($this->organizer) ? [$this->organizer] : $this->organizer;
            $organizerDefaultFirstItem = $organizerDefault[0];
            if (isset($organizerDefaultFirstItem['classCode']) && in_array($organizerDefaultFirstItem['classCode'], ['BATTERY', 'CLUSTER'])) {
                $organizer = $organizerDefault;
            }
        }
        // second try
        if (empty($organizer) && !empty($this->act->organizer)) {
            $organizerDefault = $this->act->organizer;

            if (isset($organizerDefault['classCode']) && in_array($organizerDefault['classCode'], ['BATTERY', 'CLUSTER'])) {
                $organizer = $organizerDefault;
            }
        }

        if (!empty($organizer)) { // execute first & second try
            foreach ($organizer as $key => $item) {
                $request = new RRequest($item); // creating organizer external entity
                $request->references = $this->references;
                $result[] = $request;
            }

            return $result;
        }

        // third try
        if (
            !empty($this->act->entryRelationship->organizer) &&
            isset($this->act->entryRelationship->organizer['classCode']) &&
            in_array($this->act->entryRelationship->organizer['classCode'], ['BATTERY', 'CLUSTER'])
        ) {
            foreach ($this->act->entryRelationship as $key => $item) {
                $organizer = $item->organizer[0];

                if (isset($organizer['classCode']) && in_array($organizer['classCode'], ['BATTERY', 'CLUSTER'])) {
                    $request = new RRequest($organizer); // creating organizer external entity
                    $request->references = $this->references;
                    $result[] = $request;
                }
            }
        }

        if (!empty($organizer)) {

        }


        return $result;
    }

    /**
     * @return null|string
     */
    public function getReportDate()
    {
       $result = null;

       // exist act => main element is act
        $mainElement = $this;
        if (!empty($this->act)) {
            $mainElement = $this->act;
        }

       // first try
       if (isset($mainElement->effectiveTime['value'])) {
           $resultRaw = (string)$mainElement->effectiveTime['value'];

           $result = Convert::optimalFormatDate($resultRaw);
       }

       // second try
        if (empty($result) && isset($mainElement->effectiveTime->low['value'])) {
            $resultRaw = (string)$mainElement->effectiveTime->low['value'];

            $result = Convert::optimalFormatDate($resultRaw);
        }

        return $result;
    }

    /**
     * @return null
     */
    public function getOrderedBy()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function checkIsLab()
    {
        if (
            $this->parentSection->getName() == 'section' &&
            isset($this->parentSection['nullFlavor']) &&
            strval($this->parentSection['nullFlavor']) == self::INVALID_NULL_FLAVOR
        ) {
            return false;
        }
        return true;
    }


    /**
     * @return null
     * @throws \yii\base\Exception
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return SimpleXMLElement
     */
    public function getResourceContent()
    {
        return $this->containerElement;
    }

    /**
     * @param $resourceContent
     * @return string
     */
    public function getLabSource($resourceContent)
    {
        return md5($resourceContent).'.xml';
    }
}