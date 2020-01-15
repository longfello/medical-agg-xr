<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 14.11.17
 * Time: 13:59
 */

namespace common\components\editors;

/**
 * Class YesNo
 * @package common\components\editors
 */
class YesNo extends prototype
{
    /**
     * @return string
     * @throws \Exception
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
        return $this->render('yes-no', [
            'model' => $this,
            'options' => $options
        ]);

    }
}