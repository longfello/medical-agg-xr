<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 8/2/14
 * Time: 11:40 AM
 */

namespace common\assets;

use yii\web\AssetBundle;

/**
 * Class AdminLte
 * @package common\assets
 */
class AdminLte extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/admin-lte/dist';
    /**
     * @var array
     */
    public $js = [
        'js/app.min.js'
    ];
    /**
     * @var array
     */
    public $css = [
        'css/AdminLTE.min.css',
        'css/skins/_all-skins.min.css'
    ];
    /**
     * @var array
     */
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'common\assets\FontAwesome',
        'common\assets\JquerySlimScroll'
    ];
}
