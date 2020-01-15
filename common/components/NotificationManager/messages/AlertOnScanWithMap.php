<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;
use common\components\NotificationManager\messages\callbacks\AlertOnScanSended;

/**
 * Class AlertOnScanWithMap
 */
class AlertOnScanWithMap extends BaseMessage
{

    /**
     * {@inheritdoc}
     */
    public $callback = AlertOnScanSended::class;

    /** @var string */
    public $display_date;

    /** @var string */
    public $card_slid;

    /** @var string */
    public $lat;

    /** @var string */
    public $long;

    /** @var string */
    public $placeIDUrl;
}
