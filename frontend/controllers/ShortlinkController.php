<?php
/**
 * Created by PhpStorm.
 * User: roma
 * Date: 04.01.2019
 * Time: 3:32
 */

namespace frontend\controllers;


use common\models\LifeShortLink;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Request;

/**
 * Class ShortlinkController
 * @package frontend\controllers
 */
class ShortlinkController extends \common\components\Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Display a page by using hash of the shortlink
     *
     * @param $hash
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\console\Exception
     */
    public function actionDisplayShortLink($hash)
    {
        $this->layout = false;

        /** @var  LifeShortLink $shortLink */
        $shortLink = LifeShortLink::find()->where(['LIKE BINARY', 'hash', $hash])->one();
        if (!$shortLink){
            throw new NotFoundHttpException('Page not found.');
        }

        //update time live and attempts
        $shortLink->created_at = time();
        $shortLink->count_of_use++;
        $shortLink->save();

        $request = $this->getChangedRequest($shortLink->original_link);
        $route = Yii::$app->getUrlManager()->parseRequest($request);
        $params = array_merge($route[1], $this->getParamsFromLink($shortLink->original_link));

        return $this->renderContent(Yii::$app->runAction($route[0], $params));
    }

    /**
     * This method create new object of yii/web/Request and changes $pathInfo, $hostInfo to data based on $newLink
     *
     * @param string $newLink
     * @return Request
     */
    private function getChangedRequest($newLink)
    {
        $request = new Request();
        $hostInfo = env('WWW_SCHEME').'://'.env('WWW_HOST');
        $request->pathInfo = parse_url($newLink, PHP_URL_PATH);
        $request->hostInfo = $hostInfo;

        return $request;
    }

    /**
     * This method parse $link and returns GET params as associative array
     *
     * @param string $link
     * @return array
     */
    private function getParamsFromLink($link)
    {
        $querystring = parse_url($link, PHP_URL_QUERY);
        $arrayParams = explode("&", $querystring);
        $result = [];
        if (!(count($arrayParams) == 1 && $arrayParams[0] == "")) {
            foreach ($arrayParams as $key => $value) {
                $b = explode("=", $value);
                $result[$b[0]] = $b[1];
            }
            return $result;
        } else {
            return [];
        }
    }
}