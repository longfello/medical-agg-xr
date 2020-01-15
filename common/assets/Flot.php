<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 7/3/14
 * Time: 8:16 PM
 */

namespace common\assets;

use yii\web\AssetBundle;

/**
 * Class Flot
 * @package common\assets
 */
class Flot extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/flot';
    /**
     * @var array
     */
    public $js = [
        'jquery.flot.js'
    ];

    /**
     * @var array
     */
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}
