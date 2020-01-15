<?php

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class EmergencyContact
 */
class EmergencyContact extends BaseMessage
{
    /** @var string */
    public $declineUrl;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

}
