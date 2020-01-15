<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 07.06.18
 * Time: 15:45
 */

namespace common\components\PerfectParser\Common\Actions\Methods;

use common\components\PerfectParser\Common\Prototype\RestActionMethod;
use Yii;
use yii\web\Response;


/**
 * Class MedInfo
 * @package common\components\PerfectParser
 */
class Help extends RestActionMethod
{
    /** Inheritdoc */
    public static $name = "List of available methods";

    /**
     * @inheritdoc
     */
protected function run(){
        $this->setFormat(Response::FORMAT_HTML);
        return Yii::$app->controller->renderFile(Yii::getAlias('@common/components/PerfectParser/Common/Actions/Help/Layout.php'), [
            'dataSource' => Yii::$app->perfectParser->dataSource,
            'method'     => $this
        ]);
    }

}