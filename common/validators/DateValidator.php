<?php

namespace common\validators;

use yii\validators\Validator;

/**
 * @author Vladimir Press
 */
class DateValidator extends Validator
{

    /**
     * @var bool
     */
    public $skipOnEmpty = true;
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = '{attribute} must be correct DATE.';
        }
    }

    /**
     * @param mixed $value
     * @param null $error
     *
     * @return bool
     */
    public function validate($value, &$error = null)
    {
      if (!$this->parseDate($value)) {
          return false;
      }
        return true;
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     *
     * @return bool
     */
    public function validateAttribute($model, $attribute)
    {
        if (!$this->parseDate($model->$attribute)) {
          $this->addError($model, $attribute, $this->message);
          return false;
        }
        return true;
    }

    /**
     * @param $date
     *
     * @return bool
     */
    private function parseDate($date)
    {
        list($m, $d, $y) = explode('/', $date.'//');
        return  ($this->checkDayMonth($m, $d) && $this->checkDateParts($y, $m, $d) && $this->totalCheck($m, $d, $y) && $this->checkYear($y));
        
    }

    /**
     * @param $year
     * @param $month
     * @param $day
     *
     * @return bool
     */
    private function checkDateParts($year, $month, $day)
    {
      foreach ([$year, $month, $day] as $chain) {
          if (!is_numeric($chain) || is_float($chain) || !$this->chunkValueValid($chain)) return false;
      }
      return true;
    }

    /**
     * @param $year
     *
     * @return bool
     */
    private function checkYear($year)
    {
        $isOk = true;
        
        if (!in_array(strlen($year), [2,4])) $isOk = false;
        if (strlen($year) == 4 && (($year > (date("Y")+100)) || ($year <= 1699))) $isOk = false;
        
        return $isOk;
    }

    /**
     * @param $month
     * @param $day
     *
     * @return bool
     */
    private function checkDayMonth($month, $day)
    {
        $date = \DateTime::createFromFormat('m-d', $month.'-'.$day);
        if ($date) {
            $errors = \DateTime::getLastErrors();
            if (empty($errors['warning_count'])) {
                if ($month<=12 && $month>=1 && $day<=31 && $day>=1)
                    return true;
            };
        }
        return false;
    }

    /**
     * @param $chunk
     *
     * @return bool
     */
    private function chunkValueValid($chunk)
    {
        return !(is_null($chunk) || $chunk == '0' || $chunk == '00');
    }

    /**
     * @param $month
     * @param $day
     * @param $year
     *
     * @return bool
     */
    private function totalCheck($month, $day, $year)
    {
        if (is_null($day) || is_null($month) || is_null($year)) return false;
        switch (strlen($year)) {
            case 2:
                if ($year>date("y")) {
                    $year = '19'.$year;
                } else {
                    $year = '20'.$year;
                }
                break;
            case 4:
                break;
           default:
               return false;
        }
        return checkdate($month, $day, $year);
    }
    
}
