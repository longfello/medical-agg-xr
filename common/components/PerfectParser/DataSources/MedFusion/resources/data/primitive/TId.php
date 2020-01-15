<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:02
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Class TId
 * @package common\components\PerfectParser
 */
class TId extends TElement
{
    /**
     * @inheritdoc
     */
    protected function _validate($params = null)
    {
        try {
            $value = $this->getOriginalValue();
            $matches = [];
            preg_match('/[A-Za-z0-9\-\.]{1,64}/', $value, $matches);

            if ($matches) {
                return true;
            }
            else {
                $this->log('%YId is not valid.%n');
            }
        }
        catch (\Exception $e) {
            $this->log('%Y'.$e->getMessage().'%n');
        }

        return false;
    }
}