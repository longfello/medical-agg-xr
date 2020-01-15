<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 23.10.18
 * Time: 16:24
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\labs;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class RNte
 * @package common\components\PerfectParser\DataSources\MedFusion\resources\labs
 */
class RNte extends SubResource
{
    /** @var TString Comments about result */
    public $comments;

    /**
     * @return null|string
     */
    public function getNte()
    {
        $result = null;

        if (isset($this->comments)) {
            $result = $this->comments->getValue();
        }

        return $result;
    }
}