<?php
/** @var string $attribute */
use yii\helpers\Html;

/** @var array $items */
/** @var \yii\base\Model $model */
/** @var \common\components\View $this */


echo Html::activeTextInput($model, $attribute, [
        'class'     => 'form-control',
    ]);
