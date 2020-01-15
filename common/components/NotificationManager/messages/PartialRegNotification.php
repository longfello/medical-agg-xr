<?php
namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class PartialRegNotification
 */
class PartialRegNotification extends BaseMessage
{
    /** @var string */
    public $usernameHash;

    /** @var string */
    public $externalReferrer;
}
