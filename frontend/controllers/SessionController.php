<?php
/**
 * Created by PhpStorm.
 * User: jury
 * Date: 21.08.17
 * Time: 16:31
 */

namespace frontend\controllers;

use yii\web\Controller;
use common\actions\CountdownAction;

/**
 * Class SessionController
 * @package frontend\controllers
 */
class SessionController extends Controller
{
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'countdown' => [
                'class'    => CountdownAction::class,
                'userType' => CountdownAction::LOGIN_PATIENT,
            ]
        ];
    }

}
