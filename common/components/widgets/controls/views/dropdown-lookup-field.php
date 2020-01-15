<?php
/** @var string $attribute */
/** @var array $items */
/** @var \yii\base\Model $model */

use yii\helpers\Html;

echo Html::activeTextInput($model, $attribute, [
        'class'     => 'form-control',
    ]);
