<?php

namespace common\components\editors;

use yii\base\DynamicModel;
use yii\helpers\Html;


/**
 * Class EditorEmailList
 * @package common\components\editors
 *
 * @property $emailList array;
 */
class EditorEmailList extends prototype {

    /**
     * @var array
     */
    public $emailList = [];

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['emailList'], 'validateEmails'],
        ];
    }

    /**
     * Render emails editor form view
     * @param array $options
     * @return string
     */
    public function renderEditor($options = []) {
        // init $emailList attribute from setting value
        if (!empty($this->value) && 0 === count($this->emailList)) {
            $this->emailList = array_map('trim', explode(',', $this->value));
        }
        return $this->render('emaillist', [
            'model' => $this,
            'options' => $options,
        ]);
    }

    /**
     * @return string
     */
    public function renderValue(){
        if (!empty($this->value)) {
            $value = array_map('trim', explode(',', $this->value));
            $value = implode('<br/>', $value);
        } else {
            $value = '<span class="text-danger">( empty value )</span>';
        }
        return $value . Html::a(
                Html::tag('span', '', ['class' => "glyphicon glyphicon-pencil"]),
                [
                    '/admin/update-settings',
                    'key' => $this->key,
                ],
                [
                    'class' => 'js-popup pull-right',
                    'title' => "Update {$this->name}",
                    'data-pjax' => 0,
                ]
            ). Html::tag('div', '', ['class' => 'clearfix']);
    }

    /**
     * Validate each email value in $emailList attribute
     * Validator add error to model and use array index as attribute key
     *
     * @param $attribute
     * @param $params
     */
    public function validateEmails($attribute, $params) {
        // skip empty values in email list
        //$this->{$attribute} = array_filter($this->{$attribute});

        if (count($this->{$attribute})) {
            // cast numerical array keys to string because Yii validator needs attributes as assoc array
            $keys = explode(',', implode("-item,", array_keys($this->{$attribute})) . '-item');
            $this->{$attribute} = array_combine($keys, $this->{$attribute});

            // run validation via DynamicModel
            $validatorModel = new DynamicModel($this->{$attribute});
            $validatorModel->addRule(array_keys($this->{$attribute}), 'email', [
                    'skipOnError' => false,
                    'message' => 'This is not a valid email address.',
                ]
            );
            $validatorModel->addRule(array_keys($this->{$attribute}), 'required', [
                    'skipOnError' => false,
                    'message' => 'Field can\'t be empty.',
                ]
            );
            if (!$validatorModel->validate()) {
                $this->addErrors($validatorModel->getErrors());
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->value = implode(', ', $this->emailList);
            return true;
        }
        return false;
    }
}
