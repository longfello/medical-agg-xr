<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 13:48
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Class TString
 * @package common\components\PerfectParser
 */
/**
 * Class TString
 * @package common\components\PerfectParser
 */
class TString extends TElement
{
    /**
     * 1mb in bytes
     */
    const STRING_LIMIT = 1000000;

    /**
     * @param $params
     * @return bool
     */
    protected function _validate($params = null)
    {
        try{
            $out = $this->getOriginalValue();
            $out = str_replace("\t", '  ', $out);
            $out = str_replace(["\n", "\r"], "<br>", $out);

            preg_match('/[\t\n\r]/', $out, $matches);

            if ($matches) {
                $this->log("%YString do not contain \\u0009 (horizontal tab), \\u0010 (carriage return) and \\u0013 (line feed)%n");
            }

            if (mb_strlen($out, "8bit") > self::STRING_LIMIT) {
                $this->log("%YString size max 1MB!%n");
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param bool $trim
     *
     * @return mixed|string
     */
    public function getValue($trim = false){
        $value = parent::getValue();
        $value = str_replace([':DisplayName', ':OriginalText'], '', $value);
        return $trim?trim($value):$value;
    }

    /**
     * @inheritdoc
     * @return mixed|string
     */
    public function __toString()
    {
        return $this->getValue();
    }
}