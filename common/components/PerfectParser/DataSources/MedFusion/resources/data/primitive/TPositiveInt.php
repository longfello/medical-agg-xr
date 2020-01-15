<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:03
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Class TPositiveInt
 * @package common\components\PerfectParser
 */
/**
 * Class TPositiveInt
 * @package common\components\PerfectParser
 */
class TPositiveInt extends TInteger
{
    /**
     * @inheritdoc
     */
    protected function _validate($params = null)
    {
        try {
            $value = $this->getOriginalValue();

            if (is_numeric($value)) {
                if ((int)$value >= 1) {
                    $matches = [];
                    preg_match('/\+?[1-9][0-9]*/', $value, $matches);

                    if (!$matches) {
                        $this->log('%YInteger does not match regexp.%n');
                    }
                } else $this->log('%YInteger is not positive.%n');
              } else $this->log('%YNot integer%n');

            return true;
        }
        catch (\Exception $e) {
            $this->log('%Y'.$e->getMessage().'%n');
        }

        return false;
    }

    /**
     * @return int|mixed|null
     */
    public function getValue(){
        $value = parent::getValue();
        if (is_numeric($value)) return (int)$value;
        return null;
    }
}