<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 14.03.18
 * Time: 12:40
 */

namespace common\components\PerfectParser\Common\Traits;


use common\components\AWS;
use common\components\fileDb\NotificationsLimits;
use common\components\PerfectParser\DataSources\MedFusion\MedFusion;
use common\components\PerfectParser\DataSources\CCDA\CCDA;
use common\helpers\FileExtensionHelper;
use common\models\EnrollmentFailures;
use Exception;
use common\components\NotificationManager\channels\Email\Email;
use common\components\NotificationManager\messages\MFFailure;
use common\components\NotificationManager\messages\CcdaFailure;
use common\components\NotificationManager\messages\CcdaFailureOrganization;


/**
 * ReportTrait send MF JSON or CCDA XML to S3 and send email (if need) if any errors exists
 * @package common\components\PerfectParser
 */
trait ReportTrait
{
    /**
     * @param array|string $input
     * @param array $errorsLog
     *
     * @return bool
     */
    public function sendReport($input, array $errorsLog)
    {
        switch (get_class(\Yii::$app->perfectParser->dataSource)) {
            case MedFusion::class:
                $result = $this->sendReportMF($input, $errorsLog);
                break;
            case CCDA::class:
                $result = $this->sendReportCCDA($input, $errorsLog);
                break;
            default:
                \Yii::$app->perfectParser->error('Unknown dataSource for send report of parsing');
                $result = false;
        }

        if (!empty($errorsLog)) {
            \Yii::$app->perfectParser->incPrefix();
            \Yii::$app->perfectParser->log("Errors:<br><pre>". print_r($errorsLog, true) ."</pre>");
            \Yii::$app->perfectParser->decPrefix();
        }

        return $result;
    }

    /**
     * @param $input
     * @param array $errorsLog
     * @return bool
     */
    private function sendReportMF($input, array $errorsLog)
    {
        try {
            if (!$input) {
                return false;
            }
            else if (is_string($input)) { // if received already encoded JSON
                $body = $input;
            }
            else {
                $body = json_encode($input);
            }

            $fileName = $errorsLog?'failure.json':'latest.json';
            $key = \Yii::$app->perfectParser->patient->internal_id . '/' . $fileName;

            $send = $errorsLog ? ($body != AWS::get(AWS::BUCKET_MEDFUSION, $key)) : true;

            \Yii::$app->perfectParser->log("Uploading $key to AWS");
            AWS::set(AWS::BUCKET_MEDFUSION, $key, $body);

            //
            // Upload original files if no errors
            //
            if (!$errorsLog) {
                $documents = \Yii::$app->perfectParser->dataSource->api->documents->getDocuments();

                foreach ($documents as $documentItem) {
                    $documentId = isset($documentItem['documentId']) ? $documentItem['documentId'] : '';
                    $mimeType = isset($documentItem['mimetype']) ? $documentItem['mimetype'] : '';

                    if ($documentId && $mimeType) {
                        $documentExtension = FileExtensionHelper::getFileExtension($mimeType); // get file extension
                        $documentData = \Yii::$app->perfectParser->dataSource->api->documents->getDocument($documentId);

                        // uploading file to AWS server
                        if ($documentExtension && $documentData) {
                            $key = \Yii::$app->perfectParser->patient->internal_id . "/originals/$documentId$documentExtension";
                            \Yii::$app->perfectParser->log("Uploading $key to AWS");

                            AWS::set(AWS::BUCKET_MEDFUSION, $key, $documentData);
                        }
                    }
                }
            }

            if ($errorsLog) {
                if (!\Yii::$app->perfectParser->isTest() || \Yii::$app->perfectParser->testParams->failureReportEnabled) {
                    if ($send) {
                        \Yii::$app->perfectParser->log("Sending error log");

                        $signedUrl = AWS::getUrl(AWS::BUCKET_MEDFUSION, $key);
                        $uniqueErrors = $this->filterUniqueErrors($errorsLog, MFFailure::TRACE_LEVEL);
                        $notificationsLimits = new NotificationsLimits();

                        $emails = explode(',', env('MEDFUSION_EMAIL', null, true));
                        foreach ($emails as $email) {
                            if ($notificationsLimits->isNeedNotify(NotificationsLimits::NOTIFY_TYPE_MF_PARSING_FAILURE, $email)) {
                                $email = trim($email);

                                $notify = new MFFailure([
                                    'patient'   => \Yii::$app->perfectParser->patient,
                                    'errors'    => $uniqueErrors,
                                    'slid'      => \Yii::$app->perfectParser->patient->internal_id,
                                    'server'    => \Yii::$app->urlManagerFrontend->createAbsoluteUrl('/'),
                                    'href2file' => $signedUrl,
                                    'lastLog'   => \Yii::$app->perfectParser->lastParseLogId,
                                ]);
                                $notify->send($email, true, Email::getID());
                                $notificationsLimits->setNotificationIsSent(NotificationsLimits::NOTIFY_TYPE_MF_PARSING_FAILURE, $email);
                            }
                        }
                    } else {
                        \Yii::$app->perfectParser->log("Skip sending error log because already on S3");
                    }
                } else {
                    \Yii::$app->perfectParser->log("Skip sending error log because failure report is disabled in test environment");
                }
            }
        } catch (Exception $e) {
            \Yii::error($e->getMessage(), $this->category);
            return false;
        }

        return true;
    }

    /**
     * @param $input
     * @param array $errorsLog
     * @return bool
     */
    private function sendReportCCDA($input, array $errorsLog)
    {
        try {
            if (!is_string($input)) {
                $input = var_export($input, true);
            }

            if ($errorsLog) {
                $fileName = 'failure.xml';

                if (\Yii::$app->id == 'enroll') {
                    EnrollmentFailures::log(
                        json_encode($errorsLog),
                        $input,
                        \Yii::$app->session->get('enroll-source-filename'),
                        \Yii::$app->perfectParser->patient->internal_id
                    );
                }
            } else {
                $fileName = 'latest.xml';
            }

            $practiceDir = (isset(\Yii::$app->enroller->model) ? '/practice_' . \Yii::$app->enroller->model->practice_id : '');
            $key = 'ccda/'. \Yii::$app->perfectParser->patient->internal_id . $practiceDir . '/' . $fileName;

            if ($errorsLog) {
                if (!\Yii::$app->perfectParser->isTest() || \Yii::$app->perfectParser->testParams->failureReportEnabled) {
                    \Yii::$app->perfectParser->log("Sending error log");
                    $signedUrl = AWS::getUrl(AWS::BUCKET_IMAGE, $key);
                    $serverUrl = \Yii::$app->urlManagerFrontend->createAbsoluteUrl('/');

                    $uniqueErrors = $this->filterUniqueErrors($errorsLog, CcdaFailure::TRACE_LEVEL);
                    $practiceError = false;
                    foreach (array_keys($uniqueErrors) as $err) {
                        if (strpos($err, CCDA::ERR_PRACTICE_SECTIONS) !== false) {
                            $practiceError = true;
                            break;
                        }
                    }

                    $emails = explode(',', env('CCDA_IMPORT_ERROR_EMAIL', null, true));
                    foreach ($emails as $email) {
                        $email = trim($email);
                        if ($practiceError) {
                            $notify = new CcdaFailureOrganization([
                                'slid'      => \Yii::$app->perfectParser->patient->internal_id,
                                'server'    => $serverUrl,
                                'href2file' => $signedUrl,
                            ]);
                        } else {
                            $notify = new CcdaFailure([
                                'errors'    => $uniqueErrors,
                                'slid'      => \Yii::$app->perfectParser->patient->internal_id,
                                'server'    => $serverUrl,
                                'href2file' => $signedUrl,
                                'lastLog'   => \Yii::$app->perfectParser->lastParseLogId,
                            ]);
                        }
                        $notify->send($email, true, Email::getID());
                    }

                    if ($input != AWS::get(AWS::BUCKET_IMAGE, $key)) {
                        \Yii::$app->perfectParser->log("Uploading $key to AWS");
                        AWS::set(AWS::BUCKET_IMAGE, $key, $input);
                    } else {
                        \Yii::$app->perfectParser->log("Skip uploading error log because already on S3");
                    }
                } else {
                    \Yii::$app->perfectParser->log("Skip sending error log because failure report is disabled in test environment");
                }
            }
        } catch (Exception $e) {
            \Yii::error($e->getMessage(), $this->category);
            return false;
        }
    }

    private function filterUniqueErrors($errorsLog, $traceLevel)
    {
        $uniqueErrors = [];
        foreach ($errorsLog as $err) {
            if (array_key_exists($err['error'], $uniqueErrors)) { continue; }

            $fullTrace = preg_split('/#\d+\s/', $err['trace']);
            $trace = '';

            for ($i = 1; $i <= $traceLevel; $i++) {
                $trace .= $fullTrace[$i];
            }

            $uniqueErrors[$err['error']] = $trace;
        }
        return $uniqueErrors;
    }

}
