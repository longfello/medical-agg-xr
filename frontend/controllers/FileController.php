<?php

namespace frontend\controllers;
use common\components\ProtectedLink;
use yii\web\NotFoundHttpException;

/**
 * Class FileController
 * @package frontend\controllers
 */
class FileController extends \yii\web\Controller {
    /**
     * Displays homepage.
     *
     * @param $code
     *
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\web\HttpException
     */
    public function actionIndex($code) {
        $link = new ProtectedLink();
        if (!$url = $link->decodeLink($code)){
            throw new NotFoundHttpException();
        }

        if ($link->proxy) {
            header("Cache-Control: must-revalidate");
            header("Cache-Control: no-store");
            header("Cache-Control: no-cache");
            header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
            header("Pragma: no-cache");
            header("Expires: 0");
            readfile($url);
        } else {
            return $this->redirect($url);
        }
    }
}
