<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 13:26
 */

namespace common\components\NotificationManager\transports\File;


use common\components\NotificationManager\components\BaseTransport;
use yii\base\Exception;

/**
 * Class SMTPTransport
 * @package common\components\NotificationManager\transports\File
 */
class File extends BaseTransport
{
    /**
     * @return string
     */
    public $output_directory;

    /**
     * @return string
     */
    public function getName()
    {
        return 'File';
    }

    /**
     * @return string
     */
    public static function getID()
    {
        return 'file';
    }

    /**
     * creates and checks rights to $output_directory
     * @inheritdoc
     * @throws Exception
     */
    public function init(){
        if (!is_dir($this->output_directory)){
            mkdir($this->output_directory, 0775, true);
        }

        if (!is_writable($this->output_directory)){
            throw new Exception("Directory {$this->output_directory} is not writable.");
        }
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    protected function __send($message){
        try {
            $model = $message->getMessage();
            /** @var \common\components\NotificationManager\components\BaseMessage $model */
            $model->unsubscribeLink = $message->getChannel()->generateUnsubscribeUrl($message->to);

            $text  = "Message to: {$message->to}\n";
            $text .= "Subject: {$model->getSubject($model::LAYOUT_HTML)}\n";
            $text .= "Unsubscribe: {$model->unsubscribeLink}\n";
            $text .= "Content: ".$model->compose($model::LAYOUT_HTML, $model->unserialize($message->data));

            $success = file_put_contents($this->output_directory.DIRECTORY_SEPARATOR.date('Ymd-His').'-'.uniqid().'.msg', $text);
            if ($success) {
                return $message->close();
            }
        } catch (\Throwable $e){
            return $this->setError($e->getCode(), $e->getMessage(). $e->getTraceAsString());
        }
        return false;
    }
}