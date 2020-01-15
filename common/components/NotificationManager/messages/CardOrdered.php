<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class CardOrdered
 */
class CardOrdered extends BaseMessage
{
    /** @var string */
    public $manageCardUrl;
}
