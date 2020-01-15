<?php
/** @var string $attribute */
/** @var array $items */
/** @var array $inputOptions */

/** @var \yii\base\Model $model */

use yii\helpers\Html;

echo Html::activeTextInput($model, $attribute, array_merge($inputOptions, [
        'class'     => 'form-control',
    ]));
