<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 12:56
 */

namespace common\components\NotificationManager;


use common\components\NotificationManager\components\BaseChannel;
use common\components\NotificationManager\components\BaseMessage;
use common\models\Messages;
use common\models\MessagesBlocking;
use common\models\Patient;
use yii\base\BaseObject;
use yii\helpers\Html;

/**
 * Class manager
 * @package common\components\NotificationManager
  */
class manager extends BaseObject
{

    /**
     * @var BaseChannel[] $channels
     */
    public $channels;
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        $config += require (__DIR__ . '/config.php');
        parent::__construct($config);
        $this->initChannels();
    }

    /**
     * Proccess message sending
     *
     * @param string|int|Patient|\frontend\modules\patient\components\Patient $to
     * @param BaseMessage $message
     * @param bool $immediately - sends message immediately if true
     * @param null|string|BaseChannel $channel
     *
     * @throws \yii\base\Exception
     *
     * @return bool
     */
    public function send($to, BaseMessage $message, $immediately = false, $channel = null)
    {
        try {
            $channelModel = $this->findChannel($channel, $message);
            if ($channelModel) {
                $model = new Messages();
                $model->fill($to, $channelModel, $message);
                if ($model->save()) {
                    if (!MessagesBlocking::createContactHash($model->to, $channelModel->getContactType())) {
                        $this->log('Error creating entry in '.MessagesBlocking::tableName().'. Contact = '.$model->to);
                        return false;
                    }
                    if ($immediately) {
                        return $this->processSend($model);
                    } else {
                        return true;
                    }
                } else {
                    $this->log("Errors occurs while save message: ".strip_tags(Html::errorSummary($model)));
                }
            } else {
                $this->log("No channel available for message ".$message->ID);
            }
        } catch (\Exception $e) {
            $this->log("Error sending message", $e->getTraceAsString());
        }
        return false;
    }

    /**
     * Processing message queue
     * @param Messages $model
     * @return bool
     */
    public function processMessage(Messages $model)
    {
        return $this->processSend($model);
    }

    /**
     * Internal real send process
     * @param Messages $model
     * @return bool
     */
    private function processSend($model){
        try {
            $model->attempts++;
            $model->status = Messages::STATUS_IN_PROCESS;
            $model->save();
            $channel = $model->getChannel();

            return $channel->send($model);

        } catch (\Throwable $e){
            $model->last_failure_reason = $e->getMessage();
            $this->log("Errors occurs while sent message: ".$e->getMessage(), $e->getTraceAsString());
        }
        $model->status = Messages::STATUS_WAITING;
        $model->save();

        return false;
    }

    /**
     * Finds and returns channel for message
     * @param null|string|BaseChannel $channel
     * @param null|BaseMessage $message
     * @return BaseChannel|null
     */
    protected function findChannel($channel = null, $message = null){
        if (is_object($channel) && $channel instanceof BaseChannel){
            foreach ($this->channels as $one){
                if ($one->ID == $channel->ID){
                    return $channel;
                }
            }
            return $this->findChannel(null, $message);
        } elseif (is_string($channel)) {
            foreach ($this->channels as $one){
                if (($one->ID == $channel) || (get_class($one) == $channel)){
                    return $one;
                }
            }
            return $this->findChannel(null, $message);
        } elseif ($message && $message instanceof BaseMessage) {
            foreach ($this->channels as $one){
                foreach ($message->availableChannels as $messageChannel){
                    if (($one->ID == $messageChannel) || (get_class($one) == $messageChannel)){
                        return $one;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Create Channel instances in priority order from channel's config
     */
    private function initChannels()
    {
        $channels = [];
        $channelsCfg = $this->channels;
        usort($channelsCfg, function($a, $b) {
            if (isset($a['priority']) && isset($b['priority'])) {
                if ($a['priority'] == $b['priority']) {
                    return 0;
                }
                return ($a['priority'] > $b['priority'] ? 1 : -1);
            }
            return 0;
        });
        foreach ($channelsCfg as $cfg) {
            if (isset($cfg['enabled']) && $cfg['enabled']) {
                $channelClass = $cfg['class'];
                $channels[] = new $channelClass();
            }
        }
        $this->channels = $channels;
    }

    /**
     * 
     * @param mixed $message
     * @param type $trace null|boolean|string
     */
    public function log($message, $trace = null)
    {
        if (is_null($trace)) { $trace = YII_ENV_DEV; }

        $log = ['message' => print_r($message, true)];
        if ($trace) {
            $log['trace'] = ($trace === true ? (new \Exception())->getTraceAsString() : $trace);
        }
        \Yii::warning($log, 'Notification Manager');
    }

}
