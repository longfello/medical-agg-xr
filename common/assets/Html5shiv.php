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
 * Class Html5shiv
 * @package common\assets
 */
class Html5shiv extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/html5shiv';
    /**
     * @var array
     */
    public $js = [
        'dist/html5shiv.min.js'
    ];

    /**
     * @var array
     */
    public $jsOptions = [
        'condition'=>'lt IE 9'
    ];
}
