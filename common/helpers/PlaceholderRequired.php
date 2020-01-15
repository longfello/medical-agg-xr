<?php

namespace common\helpers;

/**
 * Class PlaceholderRequired
 * @package common\helpers
 */
class PlaceholderRequired
{

    /**
     * @param \yii\base\Model $model
     * @param string $field
     * @param string $placeholder
     * @return string
     */
    public static function getPlaceholder($model, $field, $placeholder)
    {
        if($model->isAttributeRequired($field)) {
            $placeholder .= ' (required)';
        }

        return $placeholder;
    }
}