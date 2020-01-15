<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 13:53
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Class TDate
 * @package common\components\PerfectParser
 */
/**
 * Class TDate
 * @package common\components\PerfectParser
 */
class TDate extends TElement
{
    /**
     * @var string
     */
    public $format = 'php:M d, Y';

    /**
     * @inheritdoc
     */
    protected function _validate($params = null)
    {
        try {
            $value = $this->getOriginalValue();
            $matches = [];
            preg_match('/-?[0-9]{4}(-(0[1-9]|1[0-2])(-(0[0-9]|[1-2][0-9]|3[0-1]))?)?/', $value, $matches);

            if ($matches) {
                return true;
            }
            else {
                $this->log('%YDate is not valid.%n');
            }


        }
        catch (\Exception $e) {
            $this->log('%Y'.$e->getMessage().'%n');
        }

        return false;
    }

    /**
     * @param $format
     * @return string
     */
    public function asDate($format = null)
    {
        if (is_null($format)) {
            $format = $this->format;
        }
        try {
            $result = \Yii::$app->formatter->asDate($this->getValue(), $format);
        } catch (\Exception $e) {
            $result = $this->getValue();
        }
        return $result;
    }
}