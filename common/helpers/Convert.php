<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 14.11.17
 * Time: 15:34
 */

namespace common\helpers;


use yii\db\Exception;
use yii\validators\DateValidator;

/**
 * Class Convert
 * @package common\helpers
 */
class Convert
{
    /**
     *
     */
    const HEIGHT_AUTO = 'auto';

    /**
     *
     */
    const UNIT_IN = 'in';
    /**
     *
     */
    const UNIT_IN_DOT = 'in.';
    /**
     *
     */
    const UNIT_INCH = 'inch';
    /**
     *
     */
    const UNIT_INCH_DOT = 'inch.';
    /**
     *
     */
    const UNIT_INCHS = 'inchs';
    /**
     *
     */
    const UNIT_INCHS_DOT = 'inchs.';
    /**
     *
     */
    const UNIT_INCHES = 'inches';
    /**
     *
     */
    const UNIT_INCHES_DOT = 'inches.';

    /**
     *
     */
    const UNIT_CM = 'cm';
    /**
     *
     */
    const UNIT_CM_DOT = 'cm.';
    /**
     *
     */
    const UNIT_CMS = 'cms';
    /**
     *
     */
    const UNIT_CMS_DOT = 'cms.';
    /**
     *
     */
    const UNIT_CANTIMETR = 'centimeter';
    /**
     *
     */
    const UNIT_CANTIMETR_DOT = 'centimeter.';
    /**
     *
     */
    const UNIT_CANTIMETRES = 'centimeters';
    /**
     *
     */
    const UNIT_CANTIMETRES_DOT = 'centimeters.';

    /**
     *
     */
    const UNIT_M = 'm';
    /**
     *
     */
    const UNIT_M_DOT = 'm.';
    /**
     *
     */
    const UNIT_METR_DOT = 'meter.';
    /**
     *
     */
    const UNIT_METR = 'meter';
    /**
     *
     */
    const UNIT_METRES = 'meters';


    /**
     * @var array
     */
    public static $inches = [
        self::UNIT_IN,
        self::UNIT_IN_DOT,
        self::UNIT_INCH,
        self::UNIT_INCH_DOT,
        self::UNIT_INCHS,
        self::UNIT_INCHS_DOT,
        self::UNIT_INCHES,
        self::UNIT_INCHES_DOT,
    ];
    /**
     * @var array
     */
    public static $cantimetres = [
        self::UNIT_CM,
        self::UNIT_CM_DOT,
        self::UNIT_CMS,
        self::UNIT_CMS_DOT,
        self::UNIT_CANTIMETR,
        self::UNIT_CANTIMETR_DOT,
        self::UNIT_CANTIMETRES,
        self::UNIT_CANTIMETRES_DOT,
    ];
    /**
     * @var array
     */
    public static $metres = [
        self::UNIT_M,
        self::UNIT_M_DOT,
        self::UNIT_METR,
        self::UNIT_METR_DOT,
        self::UNIT_METRES,
    ];

    /**
     * @param $value
     * @param null $units
     * @param string $to
     *
     * @return string
     */
    public static function height($value, $units = null, $to = self::HEIGHT_AUTO){
        $value = trim($value);
        $units = trim($units);
        switch ($to) {
            case self::HEIGHT_AUTO:
                if (is_null($units)){
                    return $value;
                } elseif (in_array($units, self::$cantimetres)){
                    if (is_numeric($value)) {
                        return $value.' '.$units;
                    } else {
                        return $value;
                    }
                } elseif (in_array($units, self::$metres)){
                    if (is_numeric($value)) {
                        return $value.' '.$units;
                    } else {
                        return $value;
                    }
                } elseif (in_array($units, self::$inches)){
                    if (is_numeric($value)) {
                        return $value.' '.$units;
                    } else {
                        return $value;
                    }
                }
        }
        return $to;
    }

    /**
     * Converts datetime from mysql timezone to $timezone ("America/New_York" as default)
     * with output formatting ($format="m/d/y \a\t H:i:s A" as default).
     * @param  string $datetime
     * @param  string $format
     * @param  string $timezone
     * @param  bool $forceConvertFromDB
     * @return string | bool
     * @throws Exception
     */
    public static function datetimeToTZ($datetime, $format = 'n/j/y h:i:s A', $timezone = 'America/New_York', $forceConvertFromDB = true)
    {
        if (empty($datetime) || !($datetime = strtotime($datetime))) return false;
        if ($forceConvertFromDB){
            $datetime = date('Y-m-d H:i:s', $datetime + \Yii::$app->db->createCommand('SELECT EXTRACT(HOUR FROM (TIMEDIFF(NOW(), UTC_TIMESTAMP))) * 60 + IF(EXTRACT(MINUTE FROM (TIMEDIFF(NOW(), UTC_TIMESTAMP))) > 0, EXTRACT(MINUTE FROM (TIMEDIFF(NOW(), UTC_TIMESTAMP))), 0)')->queryScalar() * -60);
        } else {
            $datetime = date('Y-m-d H:i:s', $datetime);
        }
        if ($timezone === 'UTC') return (new \DateTime($datetime, new \DateTimeZone("UTC")))->format($format);
        return (new \DateTime($datetime, new \DateTimeZone("UTC")))->setTimezone(new \DateTimeZone($timezone))->format($format);
    }

    /**
     * @param $value string
     *
     * @return string as is or date in Y-m-d format
     * @throws \yii\base\InvalidConfigException
     */
    public static function date($value)
    {
        $validator = new DateValidator(['format' => 'php:m/d/Y']);
        if ($validator->validate($value)){
            return \Yii::$app->formatter->asDate($value, 'php:Y-m-d');
        }
        return $value;
    }

    /**
     * @param $string string
     *
     * @return string
     */
    public static function str2HtmlId($string)
    {
        $string = strtolower($string);
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }

    /**
     * @param $dateStr string
     *
     * @return string
     */
    public static function optimalFormatDate($dateStr)
    {
        $fullFormat = 'php:Y-m-d H:i:s';
        $shortFormat = 'php:Y-m-d';
        $result = $dateStr;

        try {
            // remove milliseconds (.000)
            $millisecondsPosition = strpos($dateStr, '.');
            if ($millisecondsPosition) {
                $dateStr = substr($dateStr, 0, $millisecondsPosition);
                $dateStr .= substr($dateStr, $millisecondsPosition+4);
            }

            // if timezone available and hhmmss == 000000 - then remove timezone
            $timezoneMinusPos = strpos($dateStr, '-');
            $timezonePlusPos = strpos($dateStr, '+');
            $timezonePos = $timezoneMinusPos ? $timezoneMinusPos : $timezonePlusPos;
            if ($timezonePos) {
                if (substr($dateStr, $timezonePos-6, 6) == '000000') { // time not available
                    $dateStr = substr($dateStr, 0, mb_strlen($dateStr)-5); // remove timezone
                }
            }

            // if timezone not set - append default
            if (!empty($dateStr) && !strpos($dateStr, '-') && !strpos($dateStr, '+')) {
                $dateStr .= '-0000';

                // choose format
                $His = \Yii::$app->formatter->asDatetime($dateStr, 'php:His');
                $usingFormat = $His == '000000'  ? $shortFormat : $fullFormat;

                $result = \Yii::$app->formatter->asDatetime($dateStr, $usingFormat);
            }
        } catch (\Exception $e) { /** ignore */ }

        return $result;
    }

    /**
     * Return timezone abbreviation daylight saving time
     * @param $tz - timezone string ('+7', '-1', ...)
     * @return string - timezone abbreviation
     */
    public static function convertTZToAbbreviation($tz){
        $secondSundayMarch = date("d-M-Y", strtotime("second sunday ".date("Y")."-03"));
        $firstSundayNovember = date("d-M-Y", strtotime("first sunday ".date("Y")."-11"));
        $timezone = '';
        if(date("d-M-Y")>=$secondSundayMarch && date("d-M-Y")<$firstSundayNovember){
            switch ($tz){
                case "-4":
                    $timezone .= " (EDT)";
                    break;
                case "-5":
                    $timezone .= " (CDT)";
                    break;
                case "-6":
                    $timezone .= " (MDT)";
                    break;
                case "-7":
                    $timezone .= " (PDT)";
                    break;
                case "-8":
                    $timezone .= " (AKDT)";
                    break;
                case "-9":
                    $timezone .= " (HDT)";
                    break;
            }
        }
        if(date("d-M-Y")<$secondSundayMarch || date("d-M-Y")>=$firstSundayNovember){
            switch ($tz){
                case "-5":
                    $timezone .= " (EST)";
                    break;
                case "-6":
                    $timezone .= " (CST)";
                    break;
                case "-7":
                    $timezone .= " (MST)";
                    break;
                case "-8":
                    $timezone .= " (PST)";
                    break;
                case "-9":
                    $timezone .= " (AKST)";
                    break;
                case "-10":
                    $timezone .= " (HST)";
                    break;
            }
        }

        return $timezone;
    }
}