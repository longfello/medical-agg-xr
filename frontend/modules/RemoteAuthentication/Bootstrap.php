<?php

namespace frontend\modules\RemoteAuthentication;

use yii\base\BootstrapInterface;

/**
 * patient module definition class
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        \Yii::$app->getUrlManager()->addRules(require(__DIR__ . '/url_rules.php'), false);
    }
}
