<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 14.11.17
 * Time: 13:59
 */

namespace common\components\editors;

/**
 * Class EditorString
 * @package common\components\editors
 */
class EditorString extends prototype
{
    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['value'], 'string'];
        return $rules;
    }

    /**
     * @return string
     */
    public function renderValue()
    {
        return $this->renderEditor();
    }

    /**
     * @param array $options
     * @return string
     */
    public function renderEditor($options = [])
    {
        return $this->render('string', [
            'model' => $this,
            'options' => $options
        ]);

    }

}