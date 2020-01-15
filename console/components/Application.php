<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 27.04.17
 * Time: 17:00
 */

namespace console\components;


/**
 * Class Application
 * @package console\components
 */
class Application extends \yii\console\Application
{
    /** @var bool Showing is maintenance mode running or not */
    public $isMaintenance = false;
    /** @var bool Showing is DB available or not */
    public $isDbFree = false;
    /** @var bool Control is SMS/email/other outgoing messages enabled or not */
    public $outgoingMessagesEnabled = true;

    /**
     * Enable (true) or disable (false) all outgoing messages
     * @param bool $enabled
     */
    public function setOutgoingMessages($enabled)
    {
        $this->outgoingMessagesEnabled = (bool)$enabled;
    }
}