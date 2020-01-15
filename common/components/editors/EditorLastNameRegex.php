<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 03.11.18
 * Time: 13:13
 */

namespace common\components\editors;


/**
 * Class EditorLastNameRegex
 * @package common\components\editors
 */
class EditorLastNameRegex extends prototype
{
    /**
     * Message for validation last name pattern
     */
    const MESSAGE_LAST_USER_VALIDATE_PATTERN = 'Incorrect last name filter pattern : <strong>%s</strong>';
    /*
     * Regular expression for last_name pattern
     */
    /**
     *
     */
    const LAST_NAME_REGEX = '/^[^0-9-.][a-z]+(?!.*--)+|(0-9)+$/iD';
    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [['value' , 'trim'], ['value' , 'checkValidatorLastName']]
        );
    }
    /**
     * @param $attribute
     * @return bool
     */
    public function checkValidatorLastName($attribute)
    {
        $this->$attribute = str_replace(' ', '', $this->$attribute);
        if (strpos($this->$attribute, '^') === false) {
            $this->addError($attribute, 'Needs symbol <strong>^</strong> in  begin string');
        }
        if (preg_match_all('/[\^]/', $this->$attribute , $matches)) {
           if(count($matches[0]) > 1) {
               $this->addError($attribute, 'Needs only one symbol <strong>^</strong> in  begin string');
           }
        }
        if (substr($this->$attribute, -1) == '|') {
            $this->addError($attribute, 'Last name not be empty.');
        }

        if (preg_match_all(EditorRegex::INCORRECT_SYMBOLS_REGEX, $this->$attribute, $matches)) {
            $this->addError($attribute, sprintf(EditorRegex::MESSAGE_VALIDATE, implode(" , ", array_unique($matches[0]))));
        }

        $patterns = explode('|', $this->$attribute);

        foreach ($patterns as $pattern) {
            if (!preg_match(self::LAST_NAME_REGEX, $pattern)) {
                $this->addError($attribute, sprintf(self::MESSAGE_LAST_USER_VALIDATE_PATTERN, $pattern));
            }
            if (is_numeric($pattern)) {
                $this->addError($attribute, 'Last Name not be integer.');
            }
        }
        return true;
    }

    /**
     * @param array $options
     * @return string
     */
    public function renderEditor($options = [])
    {
        return $this->render('lastnameregex', [
            'model' => $this,
            'options' => $options
        ]);
    }

}