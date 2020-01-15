<?php
/**
 * Yii2 Shortcuts
 * @author Eugene Terentev <eugene@terentev.net>
 * -----
 * This file is just an example and a place where you can add your own shortcuts,
 * it doesn't pretend to be a full list of available possibilities
 * -----
 */

/**
 * @return int|string
 */
function getMyId()
{
    return Yii::$app->user->getId();
}

/**
 * @param string $view
 * @param array $params
 * @return string
 */
function render($view, $params = [])
{
    return Yii::$app->controller->render($view, $params);
}

/**
 * @param $url
 * @param int $statusCode
 * @return \yii\web\Response
 */
function redirect($url, $statusCode = 302)
{
    return Yii::$app->controller->redirect($url, $statusCode);
}

/**
 * @param $form \yii\widgets\ActiveForm
 * @param $model
 * @param $attribute
 * @param array $inputOptions
 * @param array $fieldOptions
 * @return string
 */
function activeTextinput($form, $model, $attribute, $inputOptions = [], $fieldOptions = [])
{
    return $form->field($model, $attribute, $fieldOptions)->textInput($inputOptions);
}

/**
 * @param string $key
 * @param mixed $default
 * @param bool $cloneToDB
 *
 * @param bool $getFile
 * @return mixed
 */
function env($key, $default = null, $cloneToDB = false, $getFile = true) {
    $value = false;
    $low_level_keys = ['DEBUG_PANEL_ENABLED',
                       'SERVER_ROLE',
                       'WWW_SCHEME',
                       'ROUTER_HOST',
                       'PROFILE_HOST',
                       'ENROLL_HOST',
                       'PORTAL_HOST',
                       'ADMIN_EMAIL',
                       'ROBOT_EMAIL',
                       'UGLIFY_RESOURCES',
                       'DEBUG_GEOLOCATION',
                       'BITLY_ACCESS_TOKEN',
                       'WWW_HOST',
                       'SLID_DOMAIN_SHORT_LINK',
                      ];
    if ($hasNoLowParams = !in_array($key, $low_level_keys)){
        $value = \common\models\Settings::get( strtolower( $key ) );
    }

    if (empty($value)) {
        if( $getFile ){
            $value = getenv( strtoupper( $key ) ) ? getenv( strtoupper( $key ) ) : $default;
        }
        if ($cloneToDB && $hasNoLowParams) {
          \common\models\Settings::set(strtolower($key), $value);
        }
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
        case '1':
            return true;
        case 'false':
        case '(false)':
        case '0':
            return false;
    }

    return $value;
}