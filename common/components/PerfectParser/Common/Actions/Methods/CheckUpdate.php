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
class CheckUpdate extends RestActionMethod
{
    /** Inheritdoc */
    public static $name = "Check if new patient's medical data available";

    /**
     * @inheritdoc
     * @throws \Throwable
     */
     protected function run(){
        $result = false;
        $request = \Yii::$app->request;
        $hash = $request->get('hash', null);
        if ($hash) {
            $patient = Patient::findOne(['internal_id_hash' => $hash]);

            if ($patient) {
                \Yii::$app->perfectParser->setPatient($patient);
                $result = Yii::$app->perfectParser->dataSource->isNewDataAvailable();
            } else {
                $this->throwError("Unable to find patient", 404, ['hash' => $hash]);
                \Yii::$app->perfectParser->error("Unable to find patient with internal_id_hash == ".VarDumper::dumpAsString($hash));
            }
        } else {
            $this->throwError("Required parameter internal_id_hash absent");
        }

        return [
            'result' => $result
        ];
    }
}