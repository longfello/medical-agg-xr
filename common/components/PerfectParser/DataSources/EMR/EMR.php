<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 30.05.18
 * Time: 16:08
 */

namespace common\components\PerfectParser\DataSources\EMR;


use common\components\PerfectParser\Common\Prototype\DataSource;
use common\models\Partners;
use common\models\Practices;

/**
 * Class source
 * @package common\components\PerfectParser
 */
class EMR extends DataSource
{
    /**
     * @inheritdoc
     */
const ID = 'Emr';

    /**
     * @inheritdoc
     */
const PARTNER_ID=Partners::PARTNER_EMR;

    /**
     * @inheritdoc
     */
    public function init(){
        parent::init();

        if (\Yii::$app->perfectParser->isTest()) {
            if (\Yii::$app->perfectParser->testParams->isNotificationParameterPresent()) {
                \Yii::$app->setOutgoingMessages(\Yii::$app->perfectParser->testParams->notificationEnabled);
            } else {
                \Yii::$app->setOutgoingMessages(true);
            }
        } else {
            \Yii::$app->setOutgoingMessages(true);
        }
    }

    /**
     * @inheritdoc
     */
    public function auth(){
        if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
            //check user/pass
            return Practices::auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        }
        return false;
    }
}