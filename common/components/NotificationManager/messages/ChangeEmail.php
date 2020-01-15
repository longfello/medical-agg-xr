<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class ChangeEmail
 */
class ChangeEmail extends BaseMessage
{
    /**
     * @var string
     */
    public $oldEmail;

    /**
     * @var string
     */
    public $newEmail;

}
