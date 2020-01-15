<?php

namespace common\components\editors;

/**
 * The form for editing a set of email filters.
 * Editing value assumes to be a string with email regexps separated by "|"
 */
class EditorRegex extends prototype
{
    /**
     * Error message (regex pattern with html): incorrect symbols
     */
    const MESSAGE_VALIDATE = 'Incorrect symbols found: <strong>%s</strong>';

    /**
     * Error message (regex pattern with html): wrong email pattern
     */
    const MESSAGE_PATTERN_VALIDATE = 'Incorrect email filter expression: <strong>%s</strong>';

    /**
     * Regular expression to find incorrect symbols
     */
    const INCORRECT_SYMBOLS_REGEX = "/[?!#$%&*()\{\}<>_;:=№\"']/";

    /**
     * Regular expression to validate single email pattern
     */
    const EMAIL_PATTERN_REGEX = '/^[\^][a-z]+[\\\]{1}[.]{1}[a-z]+[\\\]{1}[\+]{1}|[\^][a-z]+[\\\]{1}[\+]{1}|[\^][a-z]+$/iD';

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [['value' , 'trim'], ['value' , 'checkValidatorUser']]
        );
    }

    /**
     * Validate email regex filter string
     *
     * @param $attribute
     * @return bool
     */
    public function checkValidatorUser($attribute) {
        $valid = true;

        // check whole string for incorrect symbols
        if (preg_match_all(self::INCORRECT_SYMBOLS_REGEX , $this->$attribute, $matches)) {
            $this->addError(
                $attribute,
                sprintf(self::MESSAGE_VALIDATE, implode(", ", array_unique($matches[0])))
            );
            $valid = false;
        }

        // check each email pattern
        $patterns = explode('|' , $this->$attribute);
        foreach ($patterns as $pattern) {
            if (!preg_match(self::EMAIL_PATTERN_REGEX, $pattern)) {
                $this->addError(
                    $attribute,
                    sprintf(self::MESSAGE_PATTERN_VALIDATE, $pattern)
                );
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * @inheritdoc
     */
    public function renderEditor($options = [])
    {
        return $this->render('regex', [
            'model' => $this,
            'options' => $options
        ]);
    }
}
