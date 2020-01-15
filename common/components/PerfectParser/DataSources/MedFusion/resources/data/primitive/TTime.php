<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 13:54
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

/**
 * Class TTime
 * @package common\components\PerfectParser
 */
class TTime extends TElement
{
    /**
     * @var string
     */
    public $defaultFormat = 'php:H:i:s';

    /**
     * @inheritdoc
     */
    protected function _validate($params = null)
    {
        try {
            $value = $this->getOriginalValue();
            $matches = [];
            preg_match('/([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9](\.[0-9]+)?/', $value, $matches);

            if ($matches) {
                return true;
            }
            else {
                $this->log('%YTime is not valid.%n');
            }
        }
        catch (\Exception $e) {
            $this->log('%Y'.$e->getMessage().'%n');
        }

        return false;
    }

    /**
     * @param string $format
     * @return string
     * @throws InvalidArgumentException if the input value can not be evaluated as a date value.
     * @throws InvalidConfigException if the date format is invalid.
     */
    public function format($format = null)
    {
        $format = is_null($format)?$this->defaultFormat:$format;
        return \Yii::$app->formatter->asTime($this->getValue(), $format);
    }
}