<?php
namespace common\assets;

use yii\web\AssetBundle;

/**
 * Class JquerySlimScroll
 * @package common\assets
 * @author Eugene Terentev <eugene@terentev.net>
 */
class JquerySlimScroll extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/jquery-slimscroll';
    /**
     * @var array
     */
    public $js = [
        'jquery.slimscroll.min.js'
    ];
    /**
     * @var array
     */
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}
