<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 05.04.17
 * Time: 14:56
 */

namespace common\components;


/**
 * Class MigrationHelper
 * @package common\components
 */
class MigrationHelper
{
    /**
     * @param $table
     * @param $field
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function fieldExists($table, $field)
    {
        $query = "
SELECT count(*)
FROM information_schema.columns 
WHERE table_schema = database()
and COLUMN_NAME = '{$field}'
AND table_name = '{$table}';
";
        return (bool)\Yii::$app->db->createCommand($query)->queryScalar();
    }

    /**
     * @param $table
     * @param $field
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function indexExists($table, $index_name)
    {
        $query = "SHOW KEYS FROM `{$table}` WHERE Key_name='{$index_name}';";
        return (bool)\Yii::$app->db->createCommand($query)->queryScalar();
    }

    /**
     * @param $table
     *
     * @return bool
     */
    public static function tableExists($table)
    {
        return in_array($table, \Yii::$app->db->schema->getTableNames());
    }

    /**
     * @param $table
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function primaryKeyExist($table)
    {
        $query = "
SHOW INDEXES FROM `{$table}` WHERE Key_name = 'PRIMARY';
";
        return (bool)\Yii::$app->db->createCommand($query)->queryScalar();
    }
}