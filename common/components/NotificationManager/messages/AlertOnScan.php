<?php
namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;
use common\components\NotificationManager\messages\callbacks\AlertOnScanSended;


/**
 * Class AlertOnScan
 * @package common\components\NotificationManager\messages
 */
class AlertOnScan extends BaseMessage
{
    /** {@inheritdoc} */
    public $callback = AlertOnScanSended::class;

    /** @var string $card_slid Patient's SLID card */
    public $card_slid;

    /** @var string $display_date Date when the card was scanned */
    public $display_date;

}
