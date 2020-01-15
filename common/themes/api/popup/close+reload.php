<?php

/** @var $this \yii\web\View */

$url = $url ?? 0;
$position = $position ?? -1;

$this->registerJs("
var url = '{$url}';
parent.location.reload();
parent.$('.modal').modal('hide');
if (url != 0) {
    var page = $('#pagination-default-value', window.parent.document).html();
    page = ({$position} == -1 ? '' : Math.ceil({$position}/page));
    parent.window.location.replace(url+page);
}
");
