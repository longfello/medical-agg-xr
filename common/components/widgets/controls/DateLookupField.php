<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 16.11.17
 * Time: 03:09
 */

namespace common\components\widgets\controls;

use common\helpers\PatientInfo;


/**
 * Class DateLookupField
 * @package common\components\widgets\controls
 */
class DateLookupField extends BaseLookupField
{

    /**
     * @var array
     */
    public $inputOptions = [];

    /**
     * @return string
     */
    public function run()
    {

        $value = $this->model->{$this->attribute};

        if (PatientInfo::validateDate($value, true)) {
            $this->inputOptions = array_merge(['class' => 'form-control datetime-on'], $this->inputOptions);
            $this->view->registerJs('initDateWidget();');
        } else {
            $this->inputOptions = array_merge(['class' => 'form-control'], $this->inputOptions);
        }

        $this->registerSelect();

        return $this->render('date-lookup-field', [
            'model' => $this->model,
            'attribute'=>$this->attribute,
            'inputOptions' => $this->inputOptions,
        ]);
    }
}