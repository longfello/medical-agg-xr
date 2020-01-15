<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 22.10.18
 * Time: 15:05
 */

namespace common\components\PerfectParser\DataSources\CCDA\resources\labs;

use SimpleXMLElement;

/**
 * Class SubResource
 * @package common\components\PerfectParser\DataSources\CCDA\resources\labs
 */
abstract class SubResource
{
    /** @var SimpleXMLElement */
    public $references;

    /** @const string */
    const FORMAT_DATE = 'php:Y-m-d';

    /** @const string */
    const FORMAT_TIME = 'php:H:i:s';

    /** @const string */
    const FORMAT_DATETIME = 'php:Y-m-d H:i:s';

    /** @var SimpleXMLElement */
    protected $contentRaw;

    /**
     * SubResource constructor.
     * @param SimpleXMLElement $content
     */
    public function __construct(SimpleXMLElement $content)
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
            if (property_exists($this, $property)) {
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
     * @param string $reference
     * @return string
     */
    protected function getReferencedData($reference)
    {
        $referenceId = (isset($reference[0]) && $reference[0] == '#') ? substr($reference, 1) : $reference;
        $referenceData = $this->references->xpath('*//*[@ID="'.$referenceId.'"]');
        return isset($referenceData[0]) ? $referenceData[0] : null;
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
}