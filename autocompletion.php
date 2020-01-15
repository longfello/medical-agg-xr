<?php
/**
 * Yii bootstrap file.
 * Used for enhanced IDE code autocompletion.
 * Note: To avoid "Multiple Implementations" PHPStorm warning and make autocomplete faster
 * exclude or "Mark as Plain Text" vendor/yiisoft/yii2/Yii.php file
 */
class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication|WebApplication|ConsoleApplication|\common\components\Application the application instance
     */
    public static $app;
}

/**
 * Class BaseApplication
 * Used for properties that are identical for both WebApplication and ConsoleApplication
 *
 * @property yii\web\UrlManager $urlManagerFrontend UrlManager for frontend application.
 * @property yii\web\UrlManager $urlManagerBackend UrlManager for backend application.
 * @property yii\web\UrlManager $urlManagerStorage UrlManager for storage application.
 * @property yii\web\UrlManager $urlManagerProfile UrlManager for profile application.
 * @property yii\web\UrlManager $urlManagerPortal UrlManager for profile application.
 * @property yii\web\UrlManager $urlManagerEnroll UrlManager for enroll application.
 * @property common\components\NotificationManager\manager $notificationManager
 */
abstract class BaseApplication extends yii\base\Application
{
}

/**
 * Class WebApplication
 * Include only Web application related components here
 * @property \frontend\modules\patient\components\Patient $patient
 * @property \common\components\GuestSession $guestSession
 * @property \common\components\Controller $controller
 * @property \common\components\Stripe $stripe
 * @property \common\components\AWS $AWS
 * @property Mobile_Detect $devicedetect
 * @property \profile\components\ScanComponent|\scanroute\components\ScanComponent|null $scanComponent
 * @property \portal\components\Rest\RestComponent|null $rest
 * @property \common\components\Encryption $encryption
 * @property \common\components\Nexmo $nexmo
 * @property \yii\swiftmailer\Mailer $mailer
 * @property \common\components\Comparer $comparer
 * @property \common\components\View $view
 * @property \common\components\Request $request
 * @property \common\components\PerfectParser\PerfectParser $perfectParser
 * @property \common\components\shortUrl\ShortUrl $ShortUrl
 * @property \yii\caching\MemCache $memcache
 * @property \enroll\components\Enroller $enroller
 * @property \common\components\DbConnection $log_rotation_db
 * @property \common\components\fcm\Fcm $fcm
 */
class WebApplication extends yii\web\Application
{
}

/**
 * Class ConsoleApplication
 * Include only Console application related components here
 */
class ConsoleApplication extends yii\console\Application
{
}

