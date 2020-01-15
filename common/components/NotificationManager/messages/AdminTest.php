<?php
namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;
use common\components\NotificationManager\messages\callbacks\AdminTestSend;

/**
 * Class AdminTest
 * @package common\components\NotificationManager\messages
 */
class AdminTest extends BaseMessage
{
    /** {@inheritdoc} */
    public $callback = AdminTestSend::class;

    /** @var string */
    public $body;

    /** @var integer */
    public $test_id;

}
