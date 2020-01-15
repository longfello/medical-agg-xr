<?php
namespace frontend\Modules\jslog;

use Yii;
use yii\base\BootstrapInterface;

/**
 * Class Module
 * @package frontend\Modules\jslog
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @var string
     */
    public static $moduleName = 'jslog';
    /**
     * @var string
     */
    public $tableName = 'system_log';
    /**
     * @var string
     */
    public $controllerNamespace = 'frontend\modules\jslog\controllers';

    /**
     * @var string
     */
    private $js = <<<'JS'
        window.onerror = function(msg, url, line, column, error){
            var message = "";

            if (msg) {
                message += "err-message:;\n"+msg;
            } else {
                message += "err-message:\n (unknown; error;)";
            }

            var userAgent = navigator.userAgent || "(unknown)";
            message += "user-agent: "+userAgent+";\nscreen: "+window.screen.availWidth+";x;"+window.screen.availHeight+";\n";

            if (url) {
                message += "Location: "+url+";\n";
            }
            message += "URL: "+window.location.href+";\n";
            if (line) {
                var col = (column === undefined ? "(unknown)" : column);
                message += "err-position: [row: "+line+", col: "+col+"];\n";
            }
            if (error) {
                if (error.stack) {
                    message += "err-message:;\n"+error.stack+";\n";
                } else {
                    message += "err-message:;\n"+error+";\n";
                }
            }
            send2server(1, message);            
        };

        function send2server(level, message){
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "/%path%/add", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.setRequestHeader("X-Csrf-Token", "%csrf%");

            xhr.send("level="+encodeURIComponent(level)+"&category=%category%&log_time=%log_time%&prefix="+encodeURIComponent("%prefix%")+"&message="+encodeURIComponent(message));
        }
JS;

    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->layout = Yii::$app->layout;
        $this->layoutPath = Yii::$app->layoutPath;
    }

    /**
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        $app->getModule(self::$moduleName)->registerErrorHandler();
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function registerErrorHandler()
    {
        $js = strtr($this->js, [
            '%path%' => $this::$moduleName,
            '%category%' => 'JavaScript ErrorHandler',
            '%log_time%' => microtime(true),
            '%prefix%' => '[frontend]['.Yii::$app->getRequest()->getUrl().']',
            '%csrf%' => Yii::$app->request->getCsrfToken(),
        ]);

        \Yii::$app->getView()->registerJs($js, \yii\web\View::POS_HEAD);
    }
}
