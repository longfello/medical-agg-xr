<?php
namespace common\components\PerfectParser\Common;

use yii\base\Component;

/**
 * @property $regDir
 * @property array $failsCount
 * @property $logDir
 */
class RegTester extends Component
{
    /**
     * @const string
     * Path to MedFusions tests
     */
    const DIR_REGTEST = '@console/tests/medFusion';

    /**
     * @const string
     * Path to log files
     */
    const DIR_LOG = '@console/runtime/logs/medFusion';

    /**
     * @const string[]
     * List of test, which are not displaying in category list.
     * Note: if all tests are selected to processing, then none tests will be skiped
     */
    const SKIP_TESTS = ['sandbox'];

    /**
     * @const integer
     * Max value for the dropdown option FAILS_ALLOWED
     */
    const MAX_FAIL_COUNT = 20;


    /**
     * @return string
     */
    public function getRegDir()
    {
        return \Yii::getAlias(self::DIR_REGTEST);
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        $logDir = \Yii::getAlias(self::DIR_LOG);
        if (!is_dir($logDir)) {
            mkdir($logDir);
        }
        return $logDir;
    }

    /**
     * @param boolean $includeRoot
     * @return array
     */
    public function getTestDirs($includeRoot = false)
    {
        $dirs = glob($this->regDir.'/*', GLOB_ONLYDIR);
        $result = [];
        foreach ($dirs as $path) {
            $dirName = preg_replace('/^.*\//', '', $path);
            if (in_array($dirName, self::SKIP_TESTS)) { continue; }
            $result[$path] = $dirName;
        }

        if ($includeRoot) {
            $result = array_merge([$this->regDir => '(All tests)'], $result);
        }
        return $result;
    }

    /**
     * @param string|null $testDir
     * @return array
     */
    public function getTestFiles($testDir = null)
    {
        if (is_null($testDir)) {
            return ['0' => '(All tests)'];
        }

        $files = glob($this->regDir.'/'.$testDir.'/test_*');

        $result = [];
        foreach ($files as $file) {
            $fileName = preg_replace('/^.*\/test_/', '', $file);
            $result[$fileName] = $fileName;
        }

        $result = array_merge(['0' => '(All tests)'], $result);
        return $result;
    }

    /**
     * Generate items for dropdown list to select FAILS_ALLOWED
     * @return array
     */
    public function getFailsCount()
    {
        $result = [];
        for ($i = 0; $i <= self::MAX_FAIL_COUNT; $i++) {
            $result[$i] = $i;
        }
        return $result;
    }

    /**
     * @param array $rawContent
     *
     * @return string
     */
    private function formatLog(array $rawContent)
    {
        $log = [];
        foreach ($rawContent as $row) {
            if (strpos($row, '*** TestFile:') === 0) {
                $log[] = "<span style='color: navy; font-weight: bold;'>$row</span>";
            } elseif (strpos($row, 'pass:') === 0) {
                $log[] = "<span style='color: darkgreen;'>pass: </span>". str_replace('pass:', '', $row);
            } elseif (strpos($row, 'FAIL:') === 0) {
                $log[] = "<span style='color: darkred; font-weight: bold;'>$row</span>";
            } elseif (strpos($row, 'Expected:') > 0) {
                $log[] = "<span style='color: darkred;'>$row</span>";
            } elseif (strpos($row, 'But:') > 0) {
                $log[] = "<span style='color: darkred;'>$row</span>";
            } elseif (strpos($row, 'Explanation:') === 0) {
                $log[] = "<span style='color: red;'>$row</span>";
            } elseif (strpos($row, 'Num tests succeeded') === 0 && strpos($row, 'failed: 0,') > 0) {
                $log[] = "<span style='color: green; font-weight: bold; font-size: 120%;'>$row</span>";
            } elseif (strpos($row, 'Num tests succeeded') === 0 && strpos($row, 'failed:') > 0) {
                $log[] = "<span style='color: red; font-weight: bold; font-size: 120%;'>$row</span>";
            } else {
                $log[] = $row;
            }
        }

        return implode("<br>", $log);
    }

    /**
     *
     * @param string $category
     * @param string $test
     * @param array $options
     *
     * @return array
     */
    public function runTests($category, $test, $options = [])
    {
        if (!empty($options)) {
            foreach ($options as $env => $val) {
                putenv($env.'='.$val);
            }
        }

        putenv('PHP_MF_LOG_DIR='.$this->logDir);
        $execFile = tempnam('/tmp', 'rr_');
        $regEnv = file_get_contents($this->regDir . '/' . getenv('REG_TEST_ENV_FILE'));
        $cmd = 'python3.5 ' . $this->regDir . '/../regTester.py ';
        $fsHome = 'export FS_HOME='.$this->logDir;

        $testItem = $category;
        if ($test != '0') {
            $testItem .= '/test_' . $test;
        }

        file_put_contents($execFile, implode("\n", ['#!/usr/bin/env bash', $regEnv, $fsHome, $cmd.$testItem]));
        chmod($execFile, 0700);
        $result = [];
        exec($execFile.' 2> '.$this->logDir.'/errors.log', $result);
        unlink($execFile);

        $errors = trim(file_get_contents($this->logDir.'/errors.log'));

        return [
            'result' => $this->formatLog($result),
            'errors' => (empty($errors) ? '(No errors)' : $errors),
        ];
    }

}
