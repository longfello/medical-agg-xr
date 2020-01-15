<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;
use common\components\NotificationManager\messages\callbacks\SubmitEmailSended;

/**
 * Class ChangeEmail
 */
class SubmitEmail extends BaseMessage
{
    /**
     * @var string
     */
    public $callback = SubmitEmailSended::class;

    /**
     * @var string
     */
    public $validateUrl;

    /**
     * @var string
     */
    public $oldEmail;

    /**
     * @var string
     */
    public $newEmail;

}
