<?php
namespace common\components\PerfectParser\Common\Actions;

use common\components\PerfectParser\Common\Prototype\RestActionMethod;
use Yii;
use yii\helpers\BaseInflector;

/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 08.06.18
 * Time: 18:51
 */
class RestAction extends \yii\base\Action
{
    /**
     * @inheritdoc
     */
    public function run($source, $method)
    {
        Yii::$app->response->headers->add("Cache-Control", "no-store, no-cache, must-revalidate");

        $methodClass = 'common\components\PerfectParser\Common\Actions\Methods\\'. BaseInflector::camelize($method);
        if (class_exists($methodClass)){
            $methodHandler = new $methodClass(['dataSourceID' => BaseInflector::camelize($source)]);
            /** @var $methodHandler RestActionMethod */
            return $methodHandler->runMethod();
        } else {
            $methodHandler = new RestActionMethod(['dataSourceID' => $source]);
            $methodHandler->throwError("Method not found", 404, ['method' => $method]);
        }
        return false;
    }
}