<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 27.07.18
 * Time: 16:22
 */

namespace common\helpers;


use common\components\fileDb\CacheID;
use yii\helpers\FileHelper;

/**
 * Class GarbageCollector
 * @package common\helpers
 */
class GarbageCollector
{
    /**
     *
     */
    const ALIASES = ['frontend', 'portal', 'profile', 'scanroute', 'enroll'];

    /**
     * Clear assets resources
     * @return false|string Error
     */
    public static function clearAssets(){
        $error = false;
        try {
            foreach (self::ALIASES as $alias)
            {
                $path = \Yii::getAlias('@' . $alias) . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
                if (is_dir($path)) {
                    $files = array_diff(scandir($path), array('.', '..'));
                    foreach ($files as $file)
                    {
                        FileHelper::removeDirectory($path . $file);
                    }

                }
            }
            (new CacheID())->resetCacheID();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return $error;
    }

    /**
     * Clear minified assets resources
     * @return false|string Error
     */
    public static function clearMinifiedAssets(){
        $error = false;
        try {
            foreach (self::ALIASES as $alias)
            {
                if ($path = \Yii::getAlias('@' . $alias) . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'minify' . DIRECTORY_SEPARATOR){
                    if (is_dir($path)){
                        // Clear Minified Assets
                        $files = array_diff(scandir($path), array('.', '..'));
                        foreach ($files as $file)
                        {
                            if (is_file($path . $file)){
                                unlink($path . $file);
                            } else {
                                FileHelper::removeDirectory($path . $file);
                            }
                        }
                    }
                }
            }
            (new CacheID())->resetCacheID();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return $error;
    }
}