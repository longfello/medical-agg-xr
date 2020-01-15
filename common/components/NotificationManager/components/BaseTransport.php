<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 13:06
 */

namespace common\components\NotificationManager\components;


use common\models\Messages;
use common\models\Settings;
use yii\base\BaseObject;
use yii\helpers\StringHelper;

/**
 * Class BaseTransport
 * @package common\components\NotificationManager\components
 *
 * @property-read string $name
 * @property-read string $ID
 */
abstract class BaseTransport extends BaseObject
{
    /** @var bool */
    public $enabled = true;

    /**
     * @var bool send messages debug is turn ON
     */
    public $debug;

    /**
     * @var int Error code for processing
     */
    public $errorCode;

    /**
     * @var string Error message for processing
     */
    public $errorMessage;


    /** @var int  */
    public $priority = 100;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->debug = Settings::get(Settings::OPTION_DEBUG_MESSAGES);
    }

    /**
     * Method that needs to be defined in child class
     * @return string
     */
    abstract public function getName();

    /**
     * Method that needs to be defined in child class
     * @return string
     */
    abstract public static function getID();

    /**
     * Method that needs to be defined in child class to process sending
     * @param Messages $message
     * @return string|bool
     */
    abstract protected function __send($message);

    /**
     * Sending message if beforeSend() returns true
     * @param Messages $message
     * @return string
     */
    public function send($message){
        $result = false;
        $message->last_failure_reason = '';
        if ($this->beforeSend($message)) {
            $result = $this->__send($message);
        } else {
            $this->setError(0, $message->last_failure_reason);
            $this->setError(0, 'Transport disable send by beforeSend()');
            $message->status = $message::STATUS_DISABLED;
        }
        $message->refresh();
        $message->last_failure_reason .= $this->compileErrorMessage();
        $message->save();

        return $result;
    }

    /**
     * Set processing error code and message
     * @param int $code
     * @param string $message
     * @param string $trace
     *
     * @return false
     */
    protected function setError($code = 0, $message = '', $trace = '')
    {
        $this->errorCode = $code;
        $this->errorMessage = $message;

        if (YII_ENV_DEV) {
            \Yii::$app->notificationManager->log($message, (empty($trace) ? true : $trace));
        }

        return false;
    }

    /**
     * Return compiled error string
     * @return string
     */
    protected function compileErrorMessage()
    {
        if (empty($this->errorCode) && empty($this->errorMessage)) {
            return '';
        }
        return '#'.$this->errorCode.' - '.$this->errorMessage;
    }

    /**
     * Constructor
     * @inheritdoc
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        $transportBaseClass = StringHelper::basename(get_called_class());
        $config += require (__DIR__ . '/../transports/'.$transportBaseClass.'/config/main.php');
        parent::__construct($config);
    }

    /**
     * Method will be processed before sending. If return false - will be not sent
     * @param Messages $message
     * @return boolean
     */
    public function beforeSend($message = null)
    {
        return true;
    }

}