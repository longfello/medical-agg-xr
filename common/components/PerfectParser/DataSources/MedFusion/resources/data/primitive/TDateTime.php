<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 13:53
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Class TDateTime
 * @package common\components\PerfectParser
 *
 * @property $timestamp int
 */
/**
 * Class TDateTime
 * @package common\components\PerfectParser
 *
 * @property string $timestamp
 */
class TDateTime extends TElement
{
    /**
     *
     */
    const FORMAT_MMM_DD_YYYY = 'LLL dd, yyyy';
    /**
     *
     */
    const FORMAT_MM_DD_YYYY = 'php:m/d/Y';

    /**
     * @var string
     */
    public $format = 'php:Y-m-d H:i:s';
    /**
     * @var string
     */
    public $dateFormat = 'php:M d, Y';
    /**
     * @var bool
     */
    public $isJsTimestamp = false;
    /**
     * @inheritdoc
     */
    protected function _validate($params = null, $silent = false)
    {
        try {
            $value = $this->getOriginalValue();
            $matches = [];
            preg_match('/-?[0-9]{4}(-(0[1-9]|1[0-2])(-(0[0-9]|[1-2][0-9]|3[0-1])(T([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9](\.[0-9]+)?(Z|(\+|-)((0[0-9]|1[0-3]):[0-5][0-9]|14:00)))?)?)?/', $value, $matches);

            if ($matches && count($matches) > 1) {
                return true;
            }
            else {
                if (!$silent) $this->log('%YDateTime is not valid.%n');
            }


        }
        catch (\Exception $e) {
            if (!$silent) $this->log('%Y'.$e->getMessage().'%n');
        }

        return false;
    }

    /**
     * @return string
     */
    public function getTimestamp(){
        $value = $this->isJsTimestamp?$this->getValue()/1000:$this->getValue();
        return \Yii::$app->formatter->asTimestamp($value);
    }

    /**
     * @param $format
     * @return string
     */
    public function asDatetime($format = null)
    {
        $format = is_null($format)?$this->format:$format;
        $value = $this->isJsTimestamp?$this->getValue()/1000:$this->getValue();
        try {
            \Yii::$app->formatter->timeZone = 'UTC';
            $result = ($value ? \Yii::$app->formatter->asDatetime($value, $format) : null);
        } catch (\Exception $e) {
            $result = $this->getValue();
        }
        return $result;
    }

    /**
     * @param $format
     * @return string
     */
    public function asDate($format = null)
    {
        if (is_null($format)) {
            $format = $this->dateFormat;
        }
        try {
            if ($this->_validate(null, true)){
                $result = \Yii::$app->formatter->asDate($this->getValue(), $format);
            } else {
                $result = $this->getValue();
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            $result = $this->getValue();
        }
        return $result;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->asDatetime($this->format);
    }
}