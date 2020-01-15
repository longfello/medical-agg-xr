<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 07.06.18
 * Time: 15:45
 */

namespace common\components\PerfectParser\Common\Actions\Methods;

use common\components\PerfectParser\Common\Prototype\RestActionMethod;
use common\models\Patient;
use Yii;
use yii\helpers\VarDumper;


/**
 * Class MedInfo
 * @package common\components\PerfectParser
 */
class MedInfo extends RestActionMethod
{
    /** Inheritdoc */
    public static $name = "Processes patient's medical data";

    /** @inheritdoc
     * @return array
     * @throws \Throwable
     * @throws \yii\web\ServerErrorHttpException
     */
    protected function run(){
        $request = \Yii::$app->request;
        $internal_id = $request->get('slid', null);

        $patient = Patient::findOne(['internal_id' => $internal_id]);
        if ($patient){
            Yii::$app->perfectParser->setPatient($patient);

            $data = $request->getRawBody();
            $result = Yii::$app->perfectParser->import($data);
            Yii::$app->perfectParser->log($result?"Success":"Fail");
        } else {
            $result = false;
            \Yii::$app->perfectParser->error("Unable to find patient with internal_id == ".VarDumper::dumpAsString($internal_id));
        }

        $return = [
            'result' => $result,
            'errors' => Yii::$app->perfectParser->errors,
        ];

        if (Yii::$app->perfectParser->isTest()){
            $return['log'] = Yii::$app->perfectParser->parseLogContent;
        }
        return $return;
    }
}