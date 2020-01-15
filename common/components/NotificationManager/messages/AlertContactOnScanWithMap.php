<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;
use common\components\NotificationManager\messages\callbacks\AlertContactOnScanSended;

/**
 * Class AlertContactOnScanWithMap
 */
class AlertContactOnScanWithMap extends BaseMessage
{
    /**
     * {@inheritdoc}
     */
    public $callback = AlertContactOnScanSended::class;

    /** @var string */
    public $display_date;

    /** @var string */
    public $patient_name;

    /** @var string */
    public $notification_name;

    /** @var string */
    public $contact_id;

    /** @var string */
    public $lat;

    /** @var string */
    public $long;

    /** @var string */
    public $placeIDUrl;
}
