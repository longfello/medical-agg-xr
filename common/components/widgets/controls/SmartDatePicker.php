<?php
namespace common\components\widgets\controls;

use common\helpers\PatientInfo;
use yii\validators\DateValidator;


/**
 * Class SmartDataPicker
 * @package common\components\widgets\controls
 */
class SmartDatePicker extends BaseLookupField
{

    /**
     * @var array
     */
    public $inputOptions = ['placeholder' => 'mm/dd/yyyy'];

    /**
     * @var array
     */
    public $options = [];

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {

        $value = $this->model->{$this->attribute};

        $validator = new DateValidator(['format' => 'php:Y-m-d']);
        if ($validator->validate($value)){
            $value = $this->model->{$this->attribute} = \Yii::$app->formatter->asDate($value, 'php:m/d/Y');
        }

        if (PatientInfo::validateDate($value, true)) {
            $this->inputOptions = array_merge(['class' => 'form-control pl-form-control datetime-on'], $this->inputOptions);
            $this->view->registerJs('initDateWidget();');
        } else {
            $this->inputOptions = array_merge(['class' => 'form-control pl-form-control record-input'], $this->inputOptions);
        }

        // $this->registerSelect();

        return $this->render('smart-date-picker', [
            'model' => $this->model,
            'attribute'=>$this->attribute,
            'inputOptions' => $this->inputOptions,
        ]);
    }
}