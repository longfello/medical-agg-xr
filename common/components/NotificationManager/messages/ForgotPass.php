<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 04.12.18
 * Time: 11:06
 */

namespace common\components\NotificationManager\messages;

use common\components\NotificationManager\components\BaseMessage;
use common\components\NotificationManager\messages\callbacks\ForgotPassSended;

/**
 * Class ForgotPass
 * @package common\components\NotificationManager\messages
 */
class ForgotPass extends BaseMessage
{
    /** {@inheritdoc} */
    public $callback = ForgotPassSended::class;

    /** @var string */
    public $changePasswordUrl;
}
