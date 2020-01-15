<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class InvoicePaymentFailed
 */
class InvoicePaymentFailed extends BaseMessage
{
    /** @var integer */
    public $total;

    /** @var string */
    public $href;

    /** @var integer */
    public $weeks;

}
