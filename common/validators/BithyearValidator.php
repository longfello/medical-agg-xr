<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 12.06.17
 * Time: 17:18
 */

namespace common\validators;

use yii\validators\Validator;


/**
 * Class BithyearValidator
 * @package common\validators
 */
class BithyearValidator extends Validator
{
    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->message = 'Incorrect birth year entered.';
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @param \yii\web\View $view
     *
     * @return null|string
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $message = json_encode($this->message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return <<<JS
if ($.inArray(value.length, [2,4]) === -1) {
    messages.push($message);
}
JS;
    }
}