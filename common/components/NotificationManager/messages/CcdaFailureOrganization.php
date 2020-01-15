<?php
namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;

/**
 * Class CcdaFailure
 */
class CcdaFailureOrganization extends BaseMessage
{

    /** @var string */
    public $slid;

    /** @var string */
    public $server;

    /** @var string */
    public $href2file;

    /** @var bool */
    public $allowUnsubscribe = false;
}
