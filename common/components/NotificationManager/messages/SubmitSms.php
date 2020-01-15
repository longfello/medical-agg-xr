<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;
use common\components\NotificationManager\messages\callbacks\SubmitSmsSended;

/**
 * Class ChangeEmail
 */
class SubmitSms extends BaseMessage
{
    /**
     * {@inheritdoc}
     */
    public $callback = SubmitSmsSended::class;

    /** {@inheritdoc} */
    public $forceSend = true;

    /** @var string */
    public $oldSms;

    /** @var string */
    public $newSms;

    /** @var string */
    public $validateUrl;
}
