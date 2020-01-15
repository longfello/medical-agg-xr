<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 08.06.18
 * Time: 16:27
 */

/**
 * @var $this \common\components\View
 * @var $method \common\components\PerfectParser\Common\Actions\Methods\Help
 * @var $dataSource \common\components\PerfectParser\Common\Prototype\DataSource
 */
?>

<?php
/**
 * @var $this \common\components\View
 * @var $content string
 */
?>

<?php $this->beginContent('@common/themes/default/layouts/main.php') ?>
  <div class="content">
    <div class="col-xs-12">
      <h1>REST API of "<?= $dataSource->name ?>" data source</h1>
      <h2>Critical error response format:</h2>
      <table class="table table-bordered table-striped">
        <tr>
          <th>Param</th>
          <th>Type</th>
          <th>Value</th>
          <th>Description</th>
        </tr>
        <tr>
          <td>result</td>
          <td>string</td>
          <td>error</td>
          <td>Critical error marker</td>
        </tr>
        <tr>
          <td>data</td>
          <td>string</td>
          <td></td>
          <td>Error description</td>
        </tr>
        <tr>
          <td>code</td>
          <td>integer</td>
          <td></td>
          <td>Error code</td>
        </tr>
        <tr>
          <td>method</td>
          <td>string</td>
          <td></td>
          <td>Error method</td>
        </tr>
      </table>
      <h2>Global parameters (independed of method):</h2>
      <table class="table table-bordered table-striped">
        <tr>
          <th>Param</th>
          <th>Possible values</th>
          <th>Default value</th>
          <th>Example</th>
          <th>Description</th>
        </tr>
        <tr>
          <td>format</td>
          <td><?= implode(' | ',$method->availableFormats) ?></td>
          <td><?= $method->defaultFormat ?></td>
          <td>/rest/<?= \yii\helpers\BaseInflector::camel2id($dataSource::ID) ?>/{method}?format=xml</td>
          <td>Response format</td>
        </tr>
        <tr>
          <td>test</td>
          <td>true | false</td>
          <td>false</td>
          <td>/rest/<?= \yii\helpers\BaseInflector::camel2id($dataSource::ID) ?>/{method}?test=true</td>
          <td>Run method in test environment</td>
        </tr>
        <tr>
          <td>notifications</td>
          <td>true | false</td>
          <td>true - for normal environment,<br>false - for test environment </td>
          <td>/rest/<?= \yii\helpers\BaseInflector::camel2id($dataSource::ID) ?>/{method}?notifications=true</td>
          <td>Enable notification</td>
        </tr>
      </table>

      <?php
        $mode = \Yii::$app->perfectParser->isTest() ? "test" : "normal";
        $list = \Yii::$app->perfectParser->isTest() ? $dataSource->restTestMethodsAvailable : $dataSource->restMethodsAvailable;
      ?>

      <h2>Available methods (<?= $mode ?> mode):</h2>
      <p class="alert alert-warning">Methods list depends on mode (test or normal). To switch mode use global parameter "test".</p>
        <?php foreach($list as $oneMethodName){ ?>
        <div class="well well-sm">
           <?php
            $methodClass = 'common\components\PerfectParser\Common\Actions\Methods\\'. \yii\helpers\BaseInflector::camelize($oneMethodName);
            /** @var \common\components\PerfectParser\Common\Prototype\RestActionMethod $methodClass */
            $helpContent = class_exists($methodClass)?$methodClass::help():"Method not ready yet";
           $methodTitle = class_exists($methodClass)?$methodClass::$name:"no description";
           ?>
          <h3>Method "<b><?= $oneMethodName ?></b>" - <?= $methodTitle ?></h3>
          <?= $helpContent ?>
        </div>
      <?php } ?>
    </div>
  </div>
<?php $this->endContent() ?>
