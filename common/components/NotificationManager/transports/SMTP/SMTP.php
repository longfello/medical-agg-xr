<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 29.11.18
 * Time: 13:26
 */

namespace common\components\NotificationManager\transports\SMTP;


use common\components\NotificationManager\components\BaseTransport;
use common\models\MailQueue;

/**
 * Class SMTPTransport
 * @package common\components\NotificationManager\transports\SMTP
 */
class SMTP extends BaseTransport
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'SMTP';
    }

    /**
     * @return string
     */
    public static function getID()
    {
        return 'smtp';
    }

    /**
     * {@inheritdoc}
     */
    public function __send($message)
    {
        try {
            $queue = MailQueue::add($message);
            if ($queue) {
                return $queue->sendEmail();
            }
        } catch (\Throwable $e) {
            return $this->setError($e->getCode(), $e->getMessage(), $e->getTraceAsString());
        }
        return false;
    }
}
