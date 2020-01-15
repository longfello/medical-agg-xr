<?php
namespace common\components\PerfectParser\DataSources\CCDA\resources;

use common\components\PerfectParser\DataSources\CCDA\resources\labs\RLab;
use SimpleXMLElement;

/**
 * Class RResource
 * @package common\components\PerfectParser\DataSources\CCDA\resources
 */
abstract class RResource extends ResourceSet
{
    /** @var string */
    public $identity;

    /**
     * Constants for Medication Status
     */
    /** @var string  */
    const FIRST_TEMPLATE_ROOT_STATUS = '2.16.840.1.113883.10.20.1.47';
    /** @var string  */
    const SECOND_TEMPLATE_ROOT_STATUS = '2.16.840.1.113883.10.20.1.57';
    /** @var string  */
    const CODE_STATUS = '33999-4';
    /** @var string  */
    const CODE_SYSTEM_STATUS = '2.16.840.1.113883.6.1';
    /** @var string  */
    const VALUE_CODE_SYSTEM_STATUS = '2.16.840.1.113883.6.96';
    /** @var string  */
    const VALUE_CODE_ACTIVE_STATUS = '55561003';
    /** @var string  */
    const VALUE_CODE_ACTIVE_COMPLETED = '73425007';

    /** @const string[] List of relations, 'resouce title' => 'External resource class name'*/
    const MAP = [
        'current_medications' => RMedication::class,
        '10160-0@2.16.840.1.113883.6.1' => RMedication::class,
        '10183-2@2.16.840.1.113883.6.1' => RMedication::class,
        '11450-4@2.16.840.1.113883.6.1' => RProblem::class,
        '48765-2@2.16.840.1.113883.6.1' => RAllergy::class,
        '30954-2@2.16.840.1.113883.6.1' => RLab::class,

        //'allergies'          => RAllergies::class,
        //'active problems'    => RProblems::class,
        // ...
    ];

    /** @const string */
    const FORMAT_DATE = 'php:Y-m-d';

    /** @const string */
    const FORMAT_TIME = 'php:H:i:s';

    /** @const string */
    const FORMAT_DATETIME = 'php:Y-m-d H:i:s';


    /** @var string Name of the property that contains resource data in the resource entries */
    public $containerName;

    /** @var TElement */
    public $portalID;

    /** @var SimpleXMLElement */
    public $author;

    /** @var SimpleXMLElement */
    public $statusCode;

    /** @var SimpleXMLElement */
    public $containerElement;

    /**
     *
     * @param string $resourceName
     * @param SimpleXMLElement|null $references
     * @return RMedication|RAllergies|etc...
     */
    public static function create($resourceName, $references = null)
    {
        $name = strtolower($resourceName);
        if (array_key_exists($name, static::MAP)) {
            $resourceClass = static::MAP[$name];
            return new $resourceClass(['references' => $references]);
        } else {
            \Yii::$app->perfectParser->log("Skip unknown resource: {$resourceName}");
        }
        return null;
    }

    /**
     * Get resource set id from section element
     * @param SimpleXMLElement $section
     * @return null|string
     */
    public static function getResourceSetId($section) {
        $result = null;

        if (!empty($section->code['code']) && !empty($section->code['codeSystem'])) {
            $result = (string) $section->code['code'].'@'.$section->code['codeSystem'];
        } else if(!empty($section->title)) {
            $result = (string) $section->title;
        } else {
            \Yii::$app->perfectParser->error('Could not get Resource ID from section.');
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $entryRelationshipOrigin = $this->entryRelationship;
        // entity not array - convert in array
        if (!is_array($entryRelationshipOrigin)) {
            $entryRelationshipOrigin = [$entryRelationshipOrigin];
        }

        foreach ($entryRelationshipOrigin as $entryRelationship) {
            $status = $this->tryFetchStatus($entryRelationship);
            if ($status) {
                return $status;
            }
        }

        if (isset($this->statusCode['code'])) {
            $code = $this->statusCode['code'];
            $code = strtolower($code);

            if ($code == 'active' || $code == 'completed') {
                return $code;
            }
        }


        return null;
    }

    /**
     *
     * @param string $value Source date/time value
     * @param string $format Destination format (as 'php:...')
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function asDate($value, $format = null)
    {
        if (is_null($format)) {
            $format = self::FORMAT_DATE;
        }

        return \Yii::$app->formatter->asDate($value, $format);
    }

    /**
     *
     * @param string $value Source date/time value
     * @param string $format Destination format (as 'php:...')
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function asDatetime($value, $format = null)
    {
        if (is_null($format)) {
            $format = self::FORMAT_DATETIME;
        }

        return \Yii::$app->formatter->asDatetime($value, $format);
    }

    /**
     * @param $dateStr
     * @return bool|string
     */
    public function formatDate($dateStr)
    {
        $result = $dateStr;

        // remove milliseconds (.000)
        $millisecondsPosition = strpos($dateStr, '.');
        if ($millisecondsPosition) {
            $result = substr($dateStr, 0, $millisecondsPosition);
            $result .= substr($dateStr, $millisecondsPosition+4);
        }

        // if timezone available and hhmmss == 000000 - then remove timezone
        $timezoneMinusPos = strpos($result, '-');
        $timezonePlusPos = strpos($result, '+');
        $timezonePos = $timezoneMinusPos ? $timezoneMinusPos : $timezonePlusPos;
        if ($timezonePos) {
            if (substr($result, $timezonePos-6, 6) == '000000') { // time not available
                $result = substr($result, 0, mb_strlen($result)-5); // remove timezone
            }
        }

        // if timezone not set - append default
        if (!strpos($result, '-') && !strpos($result, '+')) {
            $result .= '-0000';
        }

        return $result;
    }

    /**
     * Try fetch status
     * @param $entryRelationship
     * @return bool|string
     */
    public function tryFetchStatus($entryRelationship)
    {
        $result = false;

        // check template_id
        if (
            isset($entryRelationship->observation->templateId['root']) &&
            in_array($entryRelationship->observation->templateId['root'], [self::FIRST_TEMPLATE_ROOT_STATUS, self::SECOND_TEMPLATE_ROOT_STATUS])
        ) {
            //check code
            if (
                isset($entryRelationship->observation->code['code']) &&
                isset($entryRelationship->observation->code['codeSystem']) &&
                $entryRelationship->observation->code['code'] == self::CODE_STATUS &&
                $entryRelationship->observation->code['codeSystem'] == self::CODE_SYSTEM_STATUS
            ) {
                // check value
                if (isset($entryRelationship->observation->value)) {
                    $valueElement = $entryRelationship->observation->value;

                    if (
                        isset($valueElement['code'])
                    ) {
                        // determine status
                        if ($valueElement['code'] == self::VALUE_CODE_ACTIVE_STATUS) {
                            $result = 'active';
                        } elseif ($valueElement['code'] == self::VALUE_CODE_ACTIVE_COMPLETED) {
                            $result ='completed';
                        }
                    }
                }
            }
        }

        return $result;
    }
}
