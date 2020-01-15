<?php
/**
 * Created by PhpStorm.
 * User: Jury
 * Date: 06.02.2018
 * Time: 16:43
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive;

/**
 * Interface TElementInterface
 * @package common\components\PerfectParser
 */
interface TElementInterface
{
    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return string
     */
    public function __toString();

    /**
     * @param $data
     * @return mixed
     */
    public function load($data);
}