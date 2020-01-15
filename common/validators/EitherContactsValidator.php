<?php

namespace common\validators;

use common\models\Allergies;
use yii\db\ActiveRecord;

/**
 * Class EitherContactsValidator
 * @package common\validators
 */
class EitherContactsValidator extends  EitherValidator
{
    /**
     * Overrided method for contacts validation
     * @param Allergies|ActiveRecord $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $values = [];
        $values[$attribute] = $model->{$attribute};
        foreach ($this->eitherAttributes as $eitherAttribute) {
            $values[$eitherAttribute] = $model->{$eitherAttribute};
        }
        $filledValues = array_filter($values, function ($value) {
            return !$this->isEmpty($value);
        });
        if (count($filledValues) == 0  && $model->practice_id>0) {
            $this->addError($model, $attribute, $this->message, $this->getErrorParams($model, $attribute));
        }
    }
}