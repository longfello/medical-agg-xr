<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;
use common\components\NotificationManager\messages\callbacks\ForgotUsernameSended;

/***
 * Class ForgotUsername
 */
class ForgotUsername extends BaseMessage
{
    /**
     * @var string
     */
    public $callback = ForgotUsernameSended::class;

}
