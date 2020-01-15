<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 22.10.18
 * Time: 15:05
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\labs;

use SimpleXMLElement;

/**
 * Class SubResource
 * @package common\components\PerfectParser\DataSources\CCDA\resources\labs
 */
abstract class SubResource
{
    /** @var SimpleXMLElement */
    protected $contentRaw;

    /**
     * RRequest constructor.
     * @param $content
     */
    public function __construct($content)
    {
        $this->contentRaw = $content;
        $this->_fillSubResource($content);
    }

    /**
     * @param $content
     */
    protected function _fillSubResource($content)
    {
        foreach ($content as $property => $value) {
            if (property_exists($content, $property)) {
                // is first item - set as single entity
                if (empty($this->$property)) {
                    $this->$property = $value;
                } else { // property already set - push in array
                    // push
                    if (is_array($this->$property)) {
                        array_push($this->$property, $value);
                    } else { // convert in array and push
                        $this->$property = [$this->$property, $value];
                    }
                }
            }
        }
    }

    /**
     *
     * @param string $reference
     * @return string
     */
    protected function getReferencedData($reference)
    {
        $referenceId = (isset($reference[0]) && $reference[0] == '#') ? substr($reference, 1) : $reference;
        $referenceData = $this->references->xpath('*//*[@ID="'.$referenceId.'"]');
        return isset($referenceData[0]) ? $referenceData[0] : null;
    }
}