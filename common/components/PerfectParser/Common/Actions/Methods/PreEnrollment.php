<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 07.06.18
 * Time: 15:45
 */

namespace common\components\PerfectParser\Common\Actions\Methods;

use common\components\PerfectParser\Common\Prototype\RestActionMethod;


/**
 * Class MedInfo
 * @package common\components\PerfectParser
 */
class PreEnrollment extends RestActionMethod
{
    /** Inheritdoc */
    public static $name = "Pre-enroll patient's profile";

    /**
     * @inheritdoc
     */
protected function run(){
        return 123;
    }

    /**
     * @inheritdoc
     */
    public static function help(){
        return 'sfasf';
    }
}