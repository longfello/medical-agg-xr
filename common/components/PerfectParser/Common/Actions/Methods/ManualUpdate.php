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
use common\models\PatientMeta;
use Yii;
use yii\db\Expression;


/**
 * Class MedInfo
 * @package common\components\PerfectParser
 */
class ManualUpdate extends RestActionMethod
{
    /** Inheritdoc */
    public static $name = "Force manual patient's medical data update";

    /** @inheritdoc
     * @return array
     * @throws \Throwable
     */
    protected function run(){
        $html = '';
        $success = true;


        $request = \Yii::$app->request;

        $hash = $request->get('hash', null);
        $forceNextUpdate = $request->get('forceNextUpdate', 0);

        if ($hash) {
            $patient = Patient::findOne(['internal_id_hash' => $hash]);
        } else {
            $patient = null;
        }

        if ($patient){
            if ($forceNextUpdate == 1) {
                $patient->mf_next_check = new Expression('NOW()');
                $patient->save();
            }

            $limit = 100;
            $current = 0;
            $json = $patient->meta->getValue(PatientMeta::MEDFUSION_MANUAL_UPDATE_COUNT);

            if ($json){
                try {
                    $updates = json_decode($json, JSON_OBJECT_AS_ARRAY);
                    if (isset($updates[date('Y-m-d')])) {
                        $current = (int)$updates[date('Y-m-d')];
                    }
                } catch(\Exception $e){
                    $current = 0;
                }
            }
            $current++;

            $message = "Success!";
            $msgType = "success";
            if ($current <= $limit){
                if(!empty($patient->stripe_subscription_id)){
                    $patient->meta->saveValue(PatientMeta::MEDFUSION_MANUAL_UPDATE_COUNT, json_encode([date('Y-m-d') => $current]));
                    try{
                        \Yii::$app->perfectParser->setPatient($patient);
                        \Yii::$app->perfectParser->import();

                    } catch(\Exception $e){
                        Yii::error($e->getMessage().$e->getTraceAsString(), 'MedFusion manual update');
                        $message = "Error occured during your data sources processing...";
                        $msgType = "danger";
                        $success = false;
                    }
                }else{
                    $message = "You can't update data without subscription.";
                    $msgType = "danger";
                    $success = false;
                }
            } else {
                $message = "You exceeded daily update limit";
                $msgType = "warning";
                $success = false;
            }

            if ($success) {
                $html .= "<script type='text/javascript'>document.location.href = document.location.href;</script>";
            }
            $html .= "<div class='text-center'><span class='label label-{$msgType}'>{$message}</span></div>";
        } else {
            $success = false;
            $html = "<div class='text-center'><span class='label label-danger'>Patient not found</span></div>";
        }

        return [
            'result' => $success,
            'html' => $html
        ];
    }
}