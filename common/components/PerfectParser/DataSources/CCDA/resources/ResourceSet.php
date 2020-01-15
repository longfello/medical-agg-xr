<?php
namespace common\components\PerfectParser\DataSources\CCDA\resources;

use yii\base\BaseObject;
use common\components\PerfectParser\DataSources\CCDA\resources\type\TElement;

/**
 * Class ResourceSet
 * @package common\components\PerfectParser\DataSources\CCDA\resources
 */
class ResourceSet extends BaseObject
{
    /** @var string Name of resource set (title) */
    public $name;

    /** @var SimpleXMLElement List of contained relations */
    public $references;

    /** @var SimpleXMLElement List of resource entries */
    public $entry;

    /**
     * @var
     */
    public $parentSection;
    /**
     * @property TElement $portalID
     * @return array
     * @throws \Exception
     */
    public function createResources($portalID)
    {
        $resources = [];
        foreach ($this->entry as $resourceContent) {
            $resource = RResource::create($this->name, $this->references);
            if ($resource) {
                $this->fillResource($resource, $resourceContent);
                $resource->parentSection = $this->parentSection;
                $resource->portalID = $portalID;
                $resource->identity = md5($this->references->asXML() . $resourceContent->asXML());
                $resources[] = $resource;
            }
        }
        return $resources;
    }

    /**
     *
     * @param RResource $resource
     * @param SimpleXMLElement $resourceContent
     *
     * @throws \Exception
     */
    private function fillResource($resource, $resourceContent)
    {
        $resourceContentForFill = null;

        // use container name
        if ($containerName = $resource->containerName) {
            if ($resourceContent->$containerName) {
                $resource->containerElement = $resourceContent->$containerName;
                $resourceContentForFill = $resourceContent->$containerName;
            }
        } else {
            $resourceContentForFill = [$resourceContent];
        }


        if ($resourceContentForFill) {
            foreach ($resourceContentForFill as $resourceData) {
                foreach ($resourceData as $property => $value) {
                    if (property_exists($resource, $property)) {
                        // is first item - set as single entity
                        if (empty($resource->$property)) {
                            $resource->$property = $value;

                            // property already set - push in array
                        } else {
                            // push
                            if (is_array($resource->$property)) {
                                array_push($resource->$property, $value);
                                // convert in array and push
                            } else {
                                $resource->$property = [$resource->$property, $value];
                            }
                        }
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
