<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 26.10.18
 * Time: 21:24
 */

namespace common\components\editors;


use common\components\MigrationHelper;
use common\models\Settings;
use yii\bootstrap\Html;

/**
 * Class EditorRotation
 * @package common\components\editors
 */
class EditorRotation extends prototype
{
    /** @var  */
    public $table = [];
    /** @var  */
    public $rows_limit = [];
    /** @var  */
    public $days_limit = [];
    /** @var  */
    public $date_field = [];
    /** @var  */
    public $leave_rows = [];
    /** @var  */
    public $errorMessage;
    /** @var  */
    public $errorRow;
    /** @var  */
    public $data;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['table', 'rows_limit', 'days_limit', 'date_field', 'leave_rows', 'value'], 'safe']
        ];
    }

    /**
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return bool
     * @throws \yii\db\Exception
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        if (parent::validate()) {
            $data = $this->formatFormRawData();
            $this->value = !empty($data) ? serialize($data) : '';

            $usedTables = [];
            foreach ($data as $key => $row) {
                $table = $row['table'];
                $rowsLimit = $row['rows_limit'];
                $daysLimit = $row['days_limit'];
                $dateField = $row['date_field'];

                if (empty($table) || !MigrationHelper::tableExists($table) || in_array($table, $usedTables)) {
                    $this->errorMessage = 'Required unique and valid Table name';
                    $this->errorRow = $key;
                } else if (empty($rowsLimit) && empty($daysLimit)) {
                    $this->errorMessage = 'Required Rows limit field or days limit field';
                    $this->errorRow = $key;
                } else if (empty($dateField) || !MigrationHelper::fieldExists($table, $dateField)) {
                    $this->errorMessage = 'Required valid Date field';
                    $this->errorRow = $key;
                }

                if (!empty($this->errorMessage)) {
                    return false;
                }

                $usedTables[] = $table;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    private function formatFormRawData() {
        $rowsCount = count($this->table);

        $result = [];
        for($i = 0; $i < $rowsCount; $i++) {
            if ($i == 0) continue;

            $table = isset($this->table[$i]) ? $this->table[$i] : '';
            $rowsLimit = isset($this->rows_limit[$i]) ? $this->rows_limit[$i] : 0;
            $daysLimit = isset($this->days_limit[$i]) ? $this->days_limit[$i] : 0;
            $dateField = isset($this->date_field[$i]) ? $this->date_field[$i] : '';
            $leaveRows = isset($this->leave_rows[$i]) ? $this->leave_rows[$i] : 0;

            $result[] = [
                'table' => trim($table),
                'rows_limit' => trim(intval($rowsLimit)),
                'days_limit' => trim(intval($daysLimit)),
                'date_field' => trim($dateField),
                'leave_rows' => trim(intval($leaveRows)),
            ];
        }
        return $result;
    }

    /**
     * @return string
     */
    public function renderValue()
    {
        $value = $this->render('rotation/value', [
            'data' => Settings::get(Settings::LOG_ROTATION_SETTING)
        ]);

        $editUrl = Html::a(
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
        );

        return $value.$editUrl;
    }

    /**
     * @param array $options
     * @return string
     */
    public function renderEditor($options = [])
    {
        $formData = unserialize($this->value);
        $formData = !empty($formData) ? $formData : unserialize($this->default_value);
        $countFormData = count($formData);

        return $this->render('rotation/editor', [
            'model' => $this,
            'options' => $options,
            'formData' => $formData,
            'countFormData' => $countFormData
        ]);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function prepareGetValue($value)
    {
        return unserialize($value);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function prepareSetValue($value)
    {
        return serialize($value);
    }
}