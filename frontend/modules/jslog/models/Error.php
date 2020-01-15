<?php
namespace frontend\modules\jslog\models;

use Yii;

/**
 * This is the model class for table "system_log" to populate records from JS Logger
 *
 * @property integer $id
 * @property integer $level
 * @property string $category
 * @property float $log_time
 * @property string $prefix
 * @property string $message
 */
class Error extends \yii\db\ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'system_log';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['level'], 'integer'],
            [['category'], 'string', 'max' => 255],
            [['log_time'], 'double'],
            [['prefix', 'message'], 'string']
        ];
    }

    /**
     * @param $insert
     *
     * @return bool
     */
    public function beforeSave($insert)
    {
        $user_ident = (Yii::$app->patient->isGuest) ? 'guest':Yii::$app->patient->patients_id;

        $this->message = "patients_id: ".$user_ident.";\n".$this->message;
        return parent::beforeSave($insert);
    }

}
