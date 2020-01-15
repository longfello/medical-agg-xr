<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 13:06
 */

namespace common\components\NotificationManager\components;


use common\models\Messages;
use common\models\Patient;
use common\models\MessagesBlocking;
use yii\base\BaseObject;
use yii\helpers\StringHelper;

/**
 * Class BaseChannel
 * @package common\components\NotificationManager\components
 *
 * @property-read string $name
 * @property-read string $ID
 * @property-read string $contactType
 * @property-read BaseTransport $transport
 */
abstract class BaseChannel extends BaseObject
{
    /** @var bool */
    public $enabled;

    /** @var bool */
    public $useShortUrl = false;

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

    /**
     * email or phone of patient
     * @var string|int $to
     */
    public $to;

    /**
     * Available transports for this channel
     * @var BaseTransport[]
     */
    public $transports = [];

    /**
     *  Method that needs to be defined in child class
     * @return string
     */
    abstract public function getName();
    /**
     *  Method that needs to be defined in child class
     * @return string
     */
    abstract public static function getID();

    /**
     * Checks if transport is available and sends message
     * @param Messages $message
     * @return bool
     */
    protected function __send($message){
        $transport = $this->getTransport();
        if ($transport){
            return $transport->send($message);
        } else {
            $message->last_failure_reason = "No transport available";
            $message->save();
        }
        return false;
    }

    /**
     * Return Contact Type. Basicaly - as constant of MessagesBlocking::CONTACT_TYPE_PHONE or MessagesBlocking::CONTACT_TYPE_EMAIL
     * @return string|false
     */
    abstract public function getContactType();

    /**
     * Is given message recipient unsubscribed from recieving messages via this channel
     * @param Messages $message
     * @return boolean
     */
    public function isUnsubscribed($message)
    {
        if ($message->message->allowUnsubscribe) {
            $contactItem = MessagesBlocking::findOne(['contact' => $message->to, 'type' => $this->contactType]);
            return (boolean) ($contactItem && $contactItem->block_status);
        }
        return false;
    }

    /**
     * Checks if sending is enabled and sends message
     * @param Messages $message
     * @return bool
     */
    public final function send($message)
    {
        $blocked = false;
        if ($this->isUnsubscribed($message)) {
            $message->last_failure_reason = "Channel disable send by unsubscription";
            \Yii::$app->notificationManager->log("Error sending message (id = ".$message->id.") via channel '".static::getID()."'");
            $blocked = true;
        } else {
            if ($message->getMessage()->beforeSend()) {
                if ($this->beforeSend()) {
                    if (\Yii::$app->outgoingMessagesEnabled) {
                        return $this->__send($message);
                    }

                    if (\Yii::$app->perfectParser->isTest()) {
                        \Yii::$app->perfectParser->log('Sending notifications: disabled');
                    }

                    $message->last_failure_reason = 'Outgoing messages are disabled on application config';
                    $message->status = Messages::STATUS_DISABLED;
                    $message->save();

                    return true;
                } else {
                    $message->last_failure_reason = "Channel disable send by beforeSend()";
                }
            } else {
                $message->last_failure_reason = "Message disable send by beforeSend()";
            }
        }
        $message->status = Messages::STATUS_DISABLED;
        $message->save();

        return ($blocked ? null : false);
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
     * Constructor
     * @inheritdoc
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        $channelBaseClass = StringHelper::basename(get_called_class());
        $config += require (__DIR__ . '/../channels/'.$channelBaseClass.'/config/main.php');
        parent::__construct($config);
        $this->initTransports();
    }

    /**
     * Method will be processed before sending. If return false - will be not sent
     * @return boolean
     */
    public function beforeSend()
    {
        return true;
    }

    /**
     * Choose transport from available
     * @return BaseTransport|null
     */
    public function getTransport(){
        foreach ($this->transports as $transport){
            if ($transport->enabled){
                return $transport;
            }
        }
        \Yii::$app->notificationManager->log("No transport available for channel: ".$this->ID);
        return null;
    }

    /**
     * Retrieve url for unsubscribing from sms notifications for current recipient
     * @param string $to - email or phone of patient
     * @param Patient|null $patient for get additional info for link
     * @return string | false - url or false
     */
    public function generateUnsubscribeUrl($to, $patient = null)
    {
        if ($type = $this->getContactType()) {
            $contactItem = MessagesBlocking::findOne(['contact' => $to, 'type' => $type]);
            if ($contactItem) {
                if ($this->useShortUrl && $patient){
                    $url = \Yii::$app->urlManagerFrontend->createAbsoluteUrl(['/unsubscribe', 'hash' => $contactItem->hash, 'phoneHash' => md5($patient->cell_phone)]);
                } else {
                    $url = \Yii::$app->urlManagerFrontend->createAbsoluteUrl(['/unsubscribe', 'hash' => $contactItem->hash]);
                }

                return ($this->useShortUrl ? \Yii::$app->ShortUrl->get($url) : $url);
            }
        }
        return false;
    }

    /**
     * Create Transport instances in priority order from transport's config
     */
    private function initTransports()
    {
        $transports = [];
        $transportsCfg = $this->transports;
        usort($transportsCfg, function($a, $b) {
            if (isset($a['priority']) && isset($b['priority'])) {
                if ($a['priority'] == $b['priority']) {
                    return 0;
                }
                return ($a['priority'] > $b['priority'] ? 1 : -1);
            }
            return 0;
        });
        foreach ($transportsCfg as $cfg) {
            if (isset($cfg['enabled']) && $cfg['enabled']) {
                $transportClass = $cfg['class'];
                $transports[] = new $transportClass();
            }
        }
        $this->transports = $transports;
    }

}