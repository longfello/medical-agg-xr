<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 15.10.18
 * Time: 19:18
 */

namespace common\components\LogRotation;

use common\models\Log\LogRotationLog;
use common\models\Log\LogRotationInitLog;
use common\models\Log\LogRotationErrorLog;
use yii\db\ActiveRecord;
use common\models\Settings;
use Yii;
use yii\db\Query;
use common\components\NotificationManager\channels\Email\Email;
use common\components\NotificationManager\messages\LogRotationFailure;
use yii\db\BatchQueryResult;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class LogRotationEntity
 * @package common\components\LogRotation
 */
class LogRotationEntity
{
    const ROTATION_ITERATION_LIMIT = 50;
    const ROTATION_TRIES = 10;

    /** @var string */
    public $dateField;
    /** @var integer */
    public $rowsLimit;
    /** @var integer */
    public $daysLimit;
    /** @var integer */
    public $leaveRows;
    /** @var string */
    public $tableName;
    /** @var string */
    public $tempTableName;
    /** @var string */
    public $rotationTableName;
    /** @var integer */
    public $currentRowsCount;

    /** @var string */
    protected $_primaryKeyColumnName = null;

    /* flags for logging */
    /** @var bool */
    protected $_rotateByDaysLimit = false;
    /** @var bool */
    protected $_rotateByRowsLimit = false;

    /**
     * LogRotationEntity constructor.
     * @param $tableName
     */
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }


    /**
     * Generate new table name
     *
     * format: <serverRole>_<tableName>_Ymd-His
     * @return string
     */
    private function generateRotationTableName()
    {
        $currentTime = date('Ymd-His');
        $serverRole = getenv('SERVER_ROLE');
        $tableName = $this->tableName;

        return "{$serverRole}_{$tableName}_{$currentTime}";
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function createRotationTable()
    {
        $this->rotationTableName = $this->generateRotationTableName();

        $showCreateTable = Yii::$app->db->createCommand("SHOW CREATE TABLE `{$this->tableName}`")->queryOne();
        $createTableSql = '';
        if (isset($showCreateTable['Create Table'])) {
            $showCreateTable = $showCreateTable['Create Table'];

            $createTableSql = str_replace(
                "CREATE TABLE `{$this->tableName}`",
                "CREATE TABLE IF NOT EXISTS `{$this->rotationTableName}`",
                $showCreateTable
            );
        }
        // create table
        if (!empty($createTableSql)) {
            \Yii::$app->log_rotation_db->createCommand($createTableSql)->execute();
            \Yii::$app->log_rotation_db->createCommand("TRUNCATE TABLE `{$this->rotationTableName}`")->execute();
            // reset pk
            \Yii::$app->log_rotation_db
                ->createCommand("ALTER TABLE `{$this->rotationTableName}` AUTO_INCREMENT = " . ((int)$this->leaveRows + 1))
                ->execute();

            return true;
        }

        return false;
    }

    /**
     * Is need rotation bt rows limit
     * @return bool
     */
    public function isNeedRotationByRows()
    {
        $currentRowsCount = (new Query())->from($this->tableName)->count();
        $result = false;

        if ($this->rowsLimit) {
            if ($currentRowsCount >= $this->rowsLimit && $currentRowsCount > $this->leaveRows) {
                $this->_rotateByRowsLimit = true;
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Is need rotation by days limit
     * Important! This method will ignore the old rows that left, and will only check the rows inserted afterwards
     *
     * @throws \yii\db\Exception
     * @return bool
     */
    public function isNeedRotationByDays()
    {
        $result = false;
        $tableName = $this->tableName;
        $dateField = $this->dateField;
        $primaryKeyColumnName = $this->getPrimaryKeyColumnName();
        $currentRowsCount = (new Query())->from($this->tableName)->count();

        if ($this->daysLimit) {
            // checking if table rows count more then leaving rows count
            if ($currentRowsCount > $this->leaveRows) {
                // skipping previous remaining rows for the oldest record query
                $subQuery = (new Query())->select($dateField)->from($tableName)->orderBy($primaryKeyColumnName)
                    ->where(['>', $primaryKeyColumnName, (int)$this->leaveRows]);
                // select the oldest record
                $oldestRow = (new Query())->select($dateField)->from(['sub' => $subQuery])->orderBy($dateField.' ASC')->one();

                // checking if already passed desired days count
                if (isset($oldestRow[$dateField])) {
                    $oldestDate = intval($oldestRow[$dateField]);
                    if (empty($oldestDate) || date('Y', $oldestDate) <= 1970) { // its not valid timestamp
                        $oldestDate = strtotime($oldestRow[$dateField]);
                    }

                    $daysPassedFromOldest = round((time() - $oldestDate) / 86400);

                    if ($daysPassedFromOldest >= $this->daysLimit) {
                        $this->_rotateByDaysLimit = true;
                        $result = true;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param $tableName
     * @param null $tableSchema
     * @return array
     * @throws Exception
     */
    public static function getAIColumnNames($tableName, $tableSchema = null) {
        if (is_null($tableSchema)) {
            $tableSchema = getenv('DB_NAME');
        }

        return Yii::$app->db->createCommand("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS "
            . "WHERE TABLE_NAME = '{$tableName}' AND (COLUMN_KEY='PRI' OR EXTRA like '%auto_increment%')"
            . " AND TABLE_SCHEMA = '{$tableSchema}'")->queryAll();
    }

    /**
     * @param string $tableSchema
     * @return array
     * @throws \yii\db\Exception
     */
    public function getColumnsWithoutAI($tableSchema = null)
    {
        if(is_null($tableSchema)) {
            $tableSchema = getenv('DB_NAME');
        }
        $result = [];
        $autoIncrementColumns = Yii::$app->db->createCommand("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS "
            . "WHERE TABLE_NAME = '{$this->tableName}' AND EXTRA NOT like '%auto_increment%' AND COLUMN_KEY <> 'PRI' "
            . " AND TABLE_SCHEMA = '{$tableSchema}'")->queryAll();

        if (!empty($autoIncrementColumns) && is_array($autoIncrementColumns)) {
            foreach ($autoIncrementColumns as $item) {
                if (isset($item['COLUMN_NAME'])) {
                    $result[] = $item['COLUMN_NAME'];
                }
            }
        }

        return $result;
    }

    /**
     * $return string|null
     * @return string
     * @throws \yii\db\Exception
     */
    public function getPrimaryKeyColumnName() {
        if (is_null($this->_primaryKeyColumnName)) {
            $primaryKeyColumnsResult = Yii::$app->db->createCommand("SHOW KEYS FROM `{$this->tableName}` WHERE Key_name = 'PRIMARY'")->queryAll();
            if (is_array($primaryKeyColumnsResult) && count($primaryKeyColumnsResult)) {
                $this->_primaryKeyColumnName = $primaryKeyColumnsResult[0]['Column_name'];
            }
        }
        return $this->_primaryKeyColumnName;
    }

    /**
     * Create temporary table
     * @throws \yii\db\Exception
     */
    public function createTempTable()
    {
        $this->tempTableName = $this->tableName.'_temporary_'.date('Ymd_His');

        // rename base table to temp
        Yii::$app->db->createCommand("RENAME TABLE {$this->tableName} TO {$this->tempTableName}")->execute();

        // create empty base table
        Yii::$app->db->createCommand("CREATE TABLE {$this->tableName} LIKE {$this->tempTableName}")->execute();
    }

    /**
     * @return bool|int
     */
    public function getTotalRowsCount()
    {
        $result = false;

        if (!empty($this->tempTableName)) {
            $result = (new Query())->from($this->tempTableName)->count();
        }

        return $result;
    }


    /**
     * Rotate data
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function rotate()
    {
        echo "\n - - Rotating table: {$this->tableName} - - \n";

        try {
            /**
             * Create temp table
             */
            echo "creating temp table ...\n";
            $this->createTempTable();

            /**
             * Return leaving data
             */
            echo "returning leaving data in main table ...\n";
            if ($this->leaveRows) {
                $leavingRows = (new Query())->select($this->getColumnsWithoutAI())->from($this->tempTableName)
                    ->limit($this->leaveRows)
                    ->orderBy($this->dateField.' DESC')
                    ->all();

                if ($leavingRows) {
                    $leavingRows = array_reverse($leavingRows);

                    // reindex primary key values
                    $primaryKeyValue = 1;
                    foreach($leavingRows as &$row) {
                        $row[$this->getPrimaryKeyColumnName()] = $primaryKeyValue++;
                    }

                    \Yii::$app->db->createCommand()->batchInsert($this->tableName, array_keys($leavingRows[0]), $leavingRows)->execute();
                    unset($leavingRows);
                }
            }
        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), 'logRotation');

            // send mail
            $emails = explode(',', env(Settings::LOG_ROTATION_FAILURE_NOTIFICATION_EMAIL, null, true));
            foreach ($emails as $email) {
                $mailModel = new LogRotationFailure([
                    'table' => $this->tableName,
                    'errorText' => $e->getMessage()
                ]);
                $mailModel->send($email, true, Email::getID());
            }
        }
    }

    /**
     * Create table for rotate old temporary table
     *
     * @param string $sourceTableName
     * @return bool|mixed|string
     * @throws \yii\db\Exception
     */
    public function createCustomRotationTable($sourceTableName) {
        $serverRole = getenv('SERVER_ROLE');
        $customRotationTableName = $serverRole . '_' . str_replace('temporary_', '', $sourceTableName);
        $customRotationTableName = substr_replace($customRotationTableName, '-', -7, -6);

        $tableSchema = getenv('LOG_ROTATION_DB_NAME');
        $tables = \Yii::$app->log_rotation_db->createCommand("SELECT `TABLE_NAME` FROM `INFORMATION_SCHEMA`.`TABLES` "
            . "WHERE `TABLE_SCHEMA` = '{$tableSchema}' AND `TABLE_NAME` = '{$customRotationTableName}'" )->queryAll();
        if (count($tables) == 0) {
            $showCreateTable = Yii::$app->db->createCommand("SHOW CREATE TABLE `{$sourceTableName}`")->queryOne();
            $createTableSql = '';
            if (isset($showCreateTable['Create Table'])) {
                $showCreateTable = $showCreateTable['Create Table'];

                $createTableSql = str_replace(
                    "CREATE TABLE `{$sourceTableName}`",
                    "CREATE TABLE `{$customRotationTableName}`",
                    $showCreateTable
                );
            }
            // create table
            if (!empty($createTableSql)) {
                \Yii::$app->log_rotation_db->createCommand($createTableSql)->execute();
                return $customRotationTableName;
            }
            return false;
        } else {
            return $customRotationTableName;
        }
    }

    /**
     * Rotate data from old temporary source table to rotation db
     *
     * @param $sourceTableName
     * @throws \Exception
     */
    public function rotateTemporaryTable($sourceTableName) {
        echo "creating rotating table ...\n";
        $customRotationTableName = $this->createCustomRotationTable($sourceTableName);
        if ($customRotationTableName) {
            $selectRotationDataQuery = (new Query())->select('*')
                ->from($sourceTableName)
                ->orderBy($this->dateField . ' ASC');

            $recordsCount = $selectRotationDataQuery->count();
            $tries = 0;
            $offset = self::ROTATION_ITERATION_LIMIT;
            $startTime = time();
            $items = [];

            (new LogRotationInitLog([
                'tableName' => $sourceTableName,
                'recordsCount' => $recordsCount,
            ]))->save();

            $aiColumns = self::getAIColumnNames($sourceTableName);
            $aiColumnName = $aiColumns[0]['COLUMN_NAME'];

            do {
                $selectRotationDataQuery->limit(self::ROTATION_ITERATION_LIMIT);
                try {
                    $chunkBegin = $offset - self::ROTATION_ITERATION_LIMIT;

                    $currentTime = time();
                    $seconds = $currentTime - $startTime;
                    $k = ($chunkBegin == 0) ? 0 : $recordsCount / $chunkBegin;
                    $evaluatedTime = $seconds * $k;
                    $currentTimeString = date('H:i:s', $seconds);
                    $evaluatedTimeString = date('H:i:s', $evaluatedTime);
                    $displayOffset = ($offset > $recordsCount) ? $recordsCount : $offset;
                    echo "\033[1A";
                    echo "rotating data [{$chunkBegin}-{$displayOffset} of {$recordsCount}] time {$currentTimeString} of {$evaluatedTimeString} ... selecting\n";

                    /** @var ActiveRecord[] $items */
                    $items = $selectRotationDataQuery->all();
                    if (count($items)) {
                        $idArray = ArrayHelper::getColumn($items, $aiColumnName);
                        $idFullString  = $idString = "'" . implode($idArray,"','") . "'";
                        $exists = \Yii::$app->log_rotation_db->createCommand("SELECT `{$aiColumnName}` FROM `{$customRotationTableName}` WHERE `$aiColumnName` IN ({$idString})")->queryAll();
                        if(count($exists)) {
                            $idArray = array_diff($idArray, ArrayHelper::getColumn($exists, $aiColumnName));
                            $idString = "'" . implode($idArray,"','") . "'";
                        }
                        echo "\033[1A";
                        echo "rotating data [{$chunkBegin}-{$displayOffset} of {$recordsCount}] time {$currentTimeString} of {$evaluatedTimeString} ... saving      \n";
                        foreach($items as $index=>$item){
                            if (!in_array($item[$aiColumnName], $idArray)) {
                                unset($items[$index]);
                            }
                        }
                        if (count($items)) {
                            \Yii::$app->log_rotation_db->createCommand()->batchInsert($customRotationTableName, array_keys(reset($items)), $items)->execute();
                        } else if (!empty($idFullString)) {
                            $items = [1];
                        }
                        \Yii::$app->db->createCommand()->delete($sourceTableName, "`{$aiColumnName}` IN ({$idFullString})")->execute();
                    }
                } catch (\Exception $e) {
                    $offset = $offset - self::ROTATION_ITERATION_LIMIT;
                    $tries++;
                    usleep(50000);
                    \Yii::$app->db->close();
                    \Yii::$app->db->open();
                    \Yii::$app->log_rotation_db->close();
                    \Yii::$app->log_rotation_db->open();
                    echo "\nError! try - {$tries}\n";
                    $errorMessage = \yii\helpers\StringHelper::truncate($e->getMessage(), 2048);
                    (new LogRotationErrorLog([
                        'tableName'     => $customRotationTableName,
                        'errorMessage'  => $errorMessage,
                        'errorLine'     => $e->getLine(),
                    ]))->save();
                    if ($tries >= self::ROTATION_TRIES) {
                        throw $e;
                    } else {
                        // disable loop break;
                        $items = [1];
                    }
                }
                $offset = $offset + self::ROTATION_ITERATION_LIMIT;
            } while(count($items) && $chunkBegin + self::ROTATION_ITERATION_LIMIT < $recordsCount);

            (new LogRotationLog([
                'tableName' => $customRotationTableName,
            ]))->save();
            /**
             * Drop temp table
             */
            echo "drop temp table...\n";
            Yii::$app->db->createCommand("DROP TABLE {$sourceTableName}")->execute();
        }
    }

    /**
     * Rotate all old temporary tables
     *
     * @throws \yii\db\Exception
     */
    public function rotateTemporaryTables() {
        $tableSchema = getenv('DB_NAME');
        $temporaryTables = \Yii::$app->db->createCommand("SELECT `TABLE_NAME` FROM `INFORMATION_SCHEMA`.`TABLES` "
            . "WHERE `TABLE_SCHEMA` = '{$tableSchema}' AND `TABLE_NAME` like '{$this->tableName}_temporary_%' ")->queryColumn();
        foreach($temporaryTables as $tableName) {
            echo "\n - - Rotating temporary table: {$tableName} - - \n";
            $this->rotateTemporaryTable($tableName);
        }
    }

    /**
     * @param $tableName
     * @return bool
     * @throws Exception
     */
    public function isTableExists($tableName) {
        $tableSchema = getenv('DB_NAME');
        $count = \Yii::$app->db->createCommand("SELECT count(*) FROM `INFORMATION_SCHEMA`.`TABLES` "
            . " WHERE `TABLE_SCHEMA` = '{$tableSchema}' AND `TABLE_NAME` = '{$tableName}'")->queryScalar();
        return ($count > 0);
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function checkRotate()
    {
        if ($this->isTableExists($this->tableName) && ($this->isNeedRotationByRows() || $this->isNeedRotationByDays())) {
            $this->rotate();
        }
    }

    /**
     * @param $flag
     */
    public function setCacheAndLogging($flag)
    {
        $flag = (boolean) $flag;

        Yii::$app->db->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $flag);
        Yii::$app->log_rotation_db->enableLogging = $flag;
        Yii::$app->log_rotation_db->enableProfiling = $flag;
    }
}
