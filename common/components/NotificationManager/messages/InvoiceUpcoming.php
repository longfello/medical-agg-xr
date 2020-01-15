<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class InvoiceUpcoming
 */
class InvoiceUpcoming extends BaseMessage
{
    /** @var float */
    public $amount;

    /** @var integer */
    public $start;

    /** @var integer */
    public $weeks;

}
