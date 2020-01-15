<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class Updates
 */
class Updates extends BaseMessage
{
    /** @var string[] */
    public $changes;

    /** @var string */
    public $updateUrl;
}
