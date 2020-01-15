<?php
/** @var common\components\View $this */
/** @var string $attribute */
/** @var string $buttonId */
/** @var array $items */
/** @var array $links */

use yii\bootstrap\Html;

$this->registerJs('
$("#'.$buttonId.'").on("change", function(){
    $("#'.$id.'").trigger("change");
});
');
?>

<?php echo Html::textInput(null, null, ['class' => 'form-control', 'id'=>$id]); ?>
<?php echo Html::activeHiddenInput($model, 'child_'.$attribute, [
        'id' => $id.'_hidden',
    ]); ?>
<?php echo Html::activeFileInput($model, $attribute, ['id' => $buttonId]); ?>
