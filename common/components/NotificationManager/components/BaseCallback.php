<?php
namespace common\components\NotificationManager\components;
use common\models\MailQueue;
use common\models\SmsQueue;

/**
 * Class Callback
 * @package common\components\NotificationManager\components
 */
abstract class BaseCallback extends \yii\base\BaseObject
{
    /** @var string */
    public $log = '';

    /** @var string */
    public $message_id;

    /** @var SmsQueue|MailQueue Queue model */
    protected $_model;

    /** @var array */
    protected $_data;

    /** @var BaseChannel $_channel */
    protected $_channel;

    /**
     * prototype constructor.
     *
     * @param SmsQueue|MailQueue $model
     */
    public function __construct($model)
    {
        $this->_model = $model;
        $this->_data = $model->data;
        $this->_channel = $model->message->channel;

        parent::__construct([]);
    }

    /**
     * Calls _run() method and in case of error writes logs
     * @return bool|string
     */
    public function run()
    {
        ob_start();
        try {
            $this->_run();
        } catch (\Exception $e) {
            $this->_model->log(constant($this->_model->logClass.'::LOGEVENT_CALLBACK'), [], [$this->_channel->transport::getID() => $e->getMessage()]);
            $this->log = ("Error occured: " . $e->getMessage() . ' in ' . $e->getFile() . " [" . $e->getLine() . "]");
            ob_end_clean();
            return false;
        }
        $this->log = ob_get_contents();
        ob_end_clean();
        return $this->log;
    }

    /**
     * Method that needs to be defined in child class
     */
    abstract protected function _run();
}
