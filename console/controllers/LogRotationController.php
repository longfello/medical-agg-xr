<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 13.09.18
 * Time: 21:16
 */

namespace console\controllers;


use common\components\LogRotation\LogRotation;
use common\components\LogRotation\LogRotationEntity;
use console\components\Controller;
use common\models\Settings;
use common\components\NotificationManager\channels\Email\Email;
use common\components\NotificationManager\messages\LogRotationFailure;

/**
 * Class LogRotationController
 * @package console\controllers
 */
class LogRotationController extends Controller
{
    /**
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionIndex()
    {
        // check log rotation table connection
        try {
            $connection = new \yii\db\Connection([
                'dsn' => \Yii::$app->log_rotation_db->dsn,
                'username' => \Yii::$app->log_rotation_db->username,
                'password' => \Yii::$app->log_rotation_db->password,
            ]);

            $connection->open();
            $connection->createCommand("SELECT version();")->execute();
            $connection->close();
        } catch (\Exception $exception){
            $errorText = 'No connection for rotation database. ' . $exception->getMessage();
            echo "\nError! {$errorText}\n";
            // send mail
            $emails = explode(',', env(Settings::LOG_ROTATION_FAILURE_NOTIFICATION_EMAIL, null, true));
            foreach ($emails as $email) {
                $mailModel = new LogRotationFailure([
                    'table' => 'NA',
                    'errorText' => $errorText
                ]);
                $mailModel->send($email, true, Email::getID());
            }
            return;
        }

        $logRotation = new LogRotation();
        /** @var LogRotationEntity $rotationEntity */
        foreach($logRotation->rotationEntities as $rotationEntity) {
            $rotationEntity->checkRotate();
        }
        foreach ($logRotation->rotationEntities as $rotationEntity) {
            $rotationEntity->rotateTemporaryTables();
        }
    }
}