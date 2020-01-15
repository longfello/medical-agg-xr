<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 01.06.17
 * Time: 9:53
 */

namespace common\components;


use yii\base\Model;

/**
 * Class TimedForm
 * @package common\components
 */
class TimedForm extends Model
{

    /**
     * @var
     */
    public $id;
    /**
     * @var int
     */
    public $timeLimit = 60;
    /**
     * @var int
     */
    public $timeInterval = 3600;
    /**
     * @var int
     */
    public $triesCount = 3;
    /**
     * @var int
     */
    public $maxTriesCount = 3;
    /**
     * @var string
     */
    public $maxTriesCountAttribute = 'tries-error';
    /**
     * @var string
     */
    public $triesErrorMessage = 'Too many attempts, please try in {time} seconds.';
    /**
     * @var bool
     */
    public $checkTries = true;

    /**
     * @var
     */
    public $sessionKey;
    /**
     * @var
     */
    public $sessionTimeoutKey;

    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->sessionKey = $this->sessionKey ? $this->sessionKey : 'tries-' . $this->id;
        $this->sessionTimeoutKey = $this->sessionKey . '-timeout';
    }

    /**
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function afterValidate()
    {
        // if forgot set, dont validate timeout
        if (!isset($this->forgot) || !$this->forgot) {
            $this->validateTimeout(true);
        }
        parent::afterValidate();
    }


    /**
     * @param bool $clearErrors
     *
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function validateTimeout($clearErrors = false)
    {
        if (!$this->checkTries) return;
        $this->triesCount = (int)\Yii::$app->guestSession->get($this->sessionKey, 0) + 1;

        if ($this->isExpired()) {
            if ($clearErrors) {
                $this->clearErrors();
            }
            $message = str_replace("{time}", $this->getExpire(), $this->triesErrorMessage);
            $this->addError($this->maxTriesCountAttribute, $message);
        } else {
            \Yii::$app->guestSession->set($this->sessionKey, $this->triesCount, time() + $this->timeInterval);
        }
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function isExpired()
    {
        $expired = ($this->triesCount >= $this->maxTriesCount);

        if ($expired) {
            if (\Yii::$app->guestSession->get($this->sessionTimeoutKey, 0) <= time()) {
                \Yii::$app->guestSession->set($this->sessionTimeoutKey, time() + $this->timeLimit, time() + $this->timeLimit);
                $expired = false;
            }
        }

        return $expired;
    }

    /**
     * @return int
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function getExpire()
    {
        return \Yii::$app->guestSession->getExpire($this->sessionTimeoutKey);
    }

}