<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class InvoicePaymentFailedLast
 */
class InvoicePaymentFailedLast extends BaseMessage
{
    /** @var integer */
    public $total;

    /** @var string */
    public $href;

}
