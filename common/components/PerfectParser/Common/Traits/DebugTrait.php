<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 08.01.18
 * Time: 13:39
 */

namespace common\components\PerfectParser\Common\Traits;

use yii\helpers\Console;

/**
 * Trait DebugTrait Purpose debug logging functionality
 * @package common\components\PerfectParser
 */
trait DebugTrait
{
    /** @var string $prefix Prefix */
    public $prefix = '';
    /**
     * Is debugging turned ON
     * @var bool
     */
    public $debug = true;
    /**
     * Messages Category
     * @var string
     */
    public $category = 'PerfectParser';

    /**
     * @var string $logComponent
     */
    public $logComponent = 'perfectParser';

    /**
     * @var string $logField
     */
    public $logField = 'errors';

    /**
     * Add Info record
     * @param $message string|mixed Message to log
     * @param $asTitle bool Set that message as Title in log
     */
    public function log($message, $asTitle = false){
        if (!$this->debug) return;
        $message = is_string($message)?$message:json_encode($message);
        if (\Yii::$app->request->isConsoleRequest){
            echo(Console::renderColoredString(\Yii::$app->perfectParser->prefix.$message.PHP_EOL));
        }

        $logItem = Console::ansiToHtml(Console::renderColoredString($message));
        if ($asTitle) {
            \Yii::$app->perfectParser->parseLogTitleId++;
            $titleId = "log-item-".\Yii::$app->perfectParser->parseLogTitleId;
            \Yii::$app->perfectParser->parseLogTitles[] = "<li  role=\"presentation\"><a href=#{$titleId}>". strip_tags($logItem) ."</a></li>";
            $logItem = "<h3 id={$titleId}>".strip_tags($logItem)."</h3>";
        }

        \Yii::$app->perfectParser->parseLogContent .= "<br>".$logItem;
    }

    /**
     * Add Error message
     * @param string|\Throwable|mixed $message
     * @param bool $force
     */
    public function error($message, $force = true){
        if (!$this->debug && !$force) return;

        $trace = (new \Exception)->getTraceAsString();
        if (is_object($message)){
            if ($message instanceof \Throwable){
                $trace   = $message->getTraceAsString();
                $message = $message->getMessage();
            }
        }

        $message = is_string($message)?$message:json_encode($message);

        $this->putError(['error' => $message, 'trace' => $trace]);

        if (\Yii::$app->request->isConsoleRequest){
            echo( Console::renderColoredString('%R'.\Yii::$app->perfectParser->prefix.$message.PHP_EOL."%n", true));
        } else {
            \Yii::error($message, $this->category);
        }
        \Yii::$app->perfectParser->parseLogContent .= "<br><strong>Error:</strong> ".Console::ansiToHtml(Console::renderColoredString('%R'.$message));
    }

    /**
     * Add conditional Info message
     * @param $condition
     * @param $message
     */
    public function conditionalLog($condition, $message){
        if (!$condition) return;
        if (\Yii::$app->request->isConsoleRequest){
            echo(Console::renderColoredString(\Yii::$app->perfectParser->prefix.$message.PHP_EOL, true));
        } else {
            \Yii::info($message, $this->category);
        }
    }

    /**
     * Log All Request variables
     */
    public function logRequest(){
        if (!$this->debug) return;
        $message = '';
        $message .= "GET:\n\t" . print_r($_GET, true) ;
        $message .= "POST:\n\t" . print_r($_POST, true) ;
        $message .= "FILES:\n\t" . print_r($_FILES, true) ;
        $message .= "\n===\n";

        if (!\Yii::$app->request->isConsoleRequest){
            \Yii::info($message, $this->category);
        }
    }

    /**
     * Add log structure level
     */
    public function incPrefix(){
        \Yii::$app->perfectParser->prefix .= '| ';
        \Yii::$app->perfectParser->parseLogContent .= "<div class='log-block'>";
    }

    /**
     * Dec log structure level
     */
    public function decPrefix(){
        \Yii::$app->perfectParser->prefix = substr(\Yii::$app->perfectParser->prefix, 0, -2);
        \Yii::$app->perfectParser->parseLogContent .= "</div>";
    }

    /**
     * Put error to array log component
     * @param $message
     */
    private function putError($message)
    {
        try {
            \Yii::$app->{$this->logComponent}->{$this->logField}[] = $message;
        }
        catch (\Exception $e) {

        }
    }

    /**
     * Save parsing log
     * @return mixed
     */
    public function saveParseLog()
    {
        $titles = "<h2>Table of Contents:</h2><ul class='nav nav-pills nav-stacked'>".implode('', \Yii::$app->perfectParser->parseLogTitles)."</ul><hr>";
        $content = "<h2>Log Content:</h2>".str_replace("<div class='log-block'></div>", '', \Yii::$app->perfectParser->parseLogContent);
        $content = str_replace("<h3", '</div><h3', $content);
        $content = str_replace("</h3>", '</h3><div class="log-block">', $content);
        $content .= "</div>";
        $content = "<div class='parser-log-content'><div>{$content}</div>";
        $data    = $titles.$content;

        $content = "\x1f\x8b\x08\x00".gzcompress($data, 9);

        $log = \Yii::$app->perfectParser->parseLog;
        $log->content = $content;
        $log->save();

        return $log->id;
    }

}
