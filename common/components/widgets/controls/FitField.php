<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 16.11.2018
 * Time: 13:17
 */

namespace common\components\widgets\controls;


use yii\base\Widget;

/**
 * Class FitField
 * @package common\components\widgets\controls
 */
class FitField extends Widget
{
    /**
     * @var
     */
    public $text;

    /**
     * @return string
     */
    public function run()
    {
        return $this->render('field-lookup', [
            'text' => $this->text,
        ]);
    }
}