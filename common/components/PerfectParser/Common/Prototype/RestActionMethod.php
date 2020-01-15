<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 07.06.18
 * Time: 15:44
 */

namespace common\components\PerfectParser\Common\Prototype;

use common\components\PerfectParser\PerfectParser;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\BaseInflector;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use yii\web\Response;


/**
 * Class RestActionMethod
 * @package common\components\PerfectParser
 */
class RestActionMethod extends BaseObject
{
    /** @var string method's short description/name */
    public static $name;

    /** @var string $dataSourceID DataSource slug (alphabetical identification string) */
    public $dataSourceID;

    /**
     * @var string[] List of available formats of requests and responses
     */
    public $availableFormats = [
        Response::FORMAT_JSON,
        Response::FORMAT_XML,
        Response::FORMAT_RAW,
        Response::FORMAT_HTML
    ];

    /** @var string Current request and response format */
    public $format = Response::FORMAT_JSON;

    /** @var string Default request and response format */
    public $defaultFormat = Response::FORMAT_JSON;

    /**
     * @inheritdoc
     */
    public function init(){
        parent::init();
        $this->format = $this->defaultFormat;
        $format = \Yii::$app->request->get('format', $this->defaultFormat);
        $this->setFormat($format);
    }

    /**
     * External interface to run method
     * @return mixed
     * @throws
     */
    public function runMethod(){
        $content = null;

        try {
            \Yii::$app->perfectParser->setDataSource($this->dataSourceID);
        } catch (\Throwable $e){
            $code = $e->getCode()?$e->getCode():500;
            $this->throwError($e->getMessage(), $code);
        }

        if (\Yii::$app->request->get('test', false) === 'true'){
            \Yii::$app->perfectParser->setEnvironment(PerfectParser::ENV_TEST);
        }

        $methodsEnabled = \Yii::$app->perfectParser->isTest()
            ? \Yii::$app->perfectParser->dataSource->restTestMethodsAvailable
            : \Yii::$app->perfectParser->dataSource->restMethodsAvailable;
        $method = BaseInflector::camel2id(StringHelper::basename(get_called_class()));

        if (in_array($method, $methodsEnabled)){

            try {
                if ($this->beforeRun()){
                    $content = $this->run();
                    $content = $this->afterRun($content);
                } else {
                    $this->throwError("Execution disabled by pre-execution handler");
                }
            } catch (\Throwable $e){
                $code = $e->getCode()?$e->getCode():500;
                $this->throwError($e->getMessage(), $code, ['trace' => $e->getTraceAsString()]);
            }

        } else {

            $this->throwError("Method disabled", 0, [
                'method' => $method,
                'test-mode' => \Yii::$app->perfectParser->isTest(),
                'available-methods' => $methodsEnabled
            ]);

        }

        return $content;
    }

    /**
     * Callback before execution method
     * @return boolean true = enable execution, false - disable
     */
    public function beforeRun(){
        return true;
    }

    /**
     * Callback after execution method
     * @param mixed $content - content returned by method execution
     * @return mixed $content
     */
    public function afterRun($content){
        return $content;
    }

    /**
     * Run method
     * @return string return values
     * @throws \Exception
     */
    protected function run(){
        return "Not ready yet...";
    }

    /**
     * Set request and response format
     * @param string $format
     */
    public function setFormat($format){
        if (in_array($format, $this->availableFormats)){
            $this->format                = $format;
            \Yii::$app->response->format = $format;
        } else {
            \Yii::$app->response->format = $this->format;
            $this->throwError("Unknown data format given", 0, ['given' => $format, 'available' => $this->availableFormats]);
        }
    }

    /**
     * Throws exception
     * @param string $message
     * @param int $errorCode
     * @param array $additionalInfo
     * @throws
     */
    public function throwError($message, $errorCode = 0, array $additionalInfo = []){
        $e = new Exception();
        \Yii::$app->response->data = [
            'result'  => 'error',
            'data'    => $message,
            'trace'   => $e->getTraceAsString(),
            'code'    => $errorCode
        ];
        if ($additionalInfo){
            \Yii::$app->response->data = array_merge(\Yii::$app->response->data, $additionalInfo);
        }
        switch (\Yii::$app->response->format){
            case Response::FORMAT_HTML:
                \Yii::$app->response->data = VarDumper::dumpAsString(\Yii::$app->response->data, 10, true);
                break;
            case Response::FORMAT_RAW:
                \Yii::$app->response->data = print_r(\Yii::$app->response->data, true);
                break;
        }
        \Yii::$app->response->send();
        \Yii::$app->end();
    }

    /**
     * Return overall description of method and params
     * @return string
     */
    public static function help(){
        $viewName = BaseInflector::camelize(StringHelper::basename(get_called_class()));
        $viewFile = \Yii::getAlias('@common/components/PerfectParser/Common/Actions/Help/Methods/'.$viewName.'.php');

        if (file_exists($viewFile)){
            return \Yii::$app->controller->renderFile($viewFile, [
                'dataSource' => \Yii::$app->perfectParser->dataSource,
            ]);
        } else return "No description available yet.";
    }
}