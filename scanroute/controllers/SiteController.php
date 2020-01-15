<?php

namespace scanroute\controllers;

/**
 * Class SiteController
 * @package scanroute\controllers
 */
class SiteController extends \common\components\Controller {

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'countdown' => [
                'class' => 'common\actions\CountdownAction'
            ]
        ];
    }

}
