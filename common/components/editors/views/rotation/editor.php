<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 29.10.18
 * Time: 15:38
 */
$script = <<< JS
 $(document).ready(function(){
        //
        // Base info
        //
        // Phone multi input
        // with hack of last
        $(document).on('click', '#rotation-form .btn-add', function(e) {
            e.preventDefault();
            var controlWrapper = $('#rotation-form .controls .entities');
            
            var emptyEntryHtml = '<div class="entry">'+
                            '<div class="form-group row">'+
                                '<div class="col-xs-3 padding-right-5">'+
                                    '<input class="form-control" name="EditorRotation[table][]" type="text" placeholder="Table" value=""/>'+
                                '</div>'+
                                '<div class="col-xs-2 padding-left-0 padding-right-5">'+
                                    '<input class="form-control" name="EditorRotation[rows_limit][]" type="number" placeholder="Rows limit" value=""/>'+
                                '</div>'+
                                '<div class="col-xs-2 padding-left-0 padding-right-5">'+
                                    '<input class="form-control" name="EditorRotation[days_limit][]" type="number" placeholder="Days limit" value=""/>'+
                                '</div>'+
                                '<div class="col-xs-2 padding-left-0 padding-right-5">'+
                                    '<input class="form-control" name="EditorRotation[date_field][]" type="text" placeholder="Date field" value=""/>'+
                                '</div>'+
                                '<div class="col-xs-2 padding-left-0">'+
                                    '<input class="form-control" name="EditorRotation[leave_rows][]" type="number" placeholder="Leave rows" value=""/>'+
                                '</div>'+
                                '<div class="col-xs-1 padding-left-0">'+
                                    '<button class="btn btn-remove btn-danger" type="button"><span class="glyphicon glyphicon-minus"></span></button>'+
                                '</div>'+
                            '</div>'+
                        '</div>';
            controlWrapper.append(emptyEntryHtml);
        }).on('click', '#rotation-form .btn-remove', function(e){
            $(this).parents('.entry:first').remove();

            e.preventDefault();
            return false;
        });
    });
JS;
$this->registerJs($script);

$action = \yii\helpers\Url::to(['/admin/update-settings', 'key' => $model->key, 'inline' => false]);
?>
<style>
    .padding-left-0 {
        padding-left:0px;
    }
    .padding-right-5 {
        padding-right:5px;
    }
    .padding-top-15 {
        padding-top:15px;
    }
    .padding-bottom-5 {
        padding-bottom:5px;
    }
</style>

<div class="padding-top-15">
    <?php if ($model->errorMessage): ?>
        <div class="alert alert-danger">
            <?= $model->errorMessage ?>
        </div>
    <?php endif; ?>

    <!-- additional numbers -->
    <div id="rotation-form">
        <div class="controls">
            <form action="<?= $action ?>" method="post" autocomplete="off">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />

                <div class="col-xs-3 padding-left-0 padding-bottom-5">
                    <b>Table</b>
                </div>
                <div class="col-xs-2 padding-left-0 padding-bottom-5">
                    <b>Rows limit</b>
                </div>
                <div class="col-xs-2 padding-left-0 padding-bottom-5">
                    <b>Days limit</b>
                </div>
                <div class="col-xs-2 padding-left-0 padding-bottom-5">
                    <b>Date field</b>
                </div>
                <div class="col-xs-2 padding-left-0 padding-bottom-5">
                    <b>Leave rows</b>
                </div>

                <input class="form-control" name="EditorRotation[table][]" type="hidden" placeholder="Table" value=""/>
                <input class="form-control" name="EditorRotation[rows_limit][]" type="hidden" placeholder="Rows limit" value=""/>
                <input class="form-control" name="EditorRotation[days_limit][]" type="hidden" placeholder="Days limit" value=""/>
                <input class="form-control" name="EditorRotation[date_field][]" type="hidden" placeholder="Date field" value=""/>
                <input class="form-control" name="EditorRotation[leave_rows][]" type="hidden" placeholder="Leave rows" value=""/>

            <div class="entities">

                    <?php if (!empty($formData)): ?>
                        <?php foreach($formData as $key => $row): ?>
                            <div class="entry">
                                <div class="form-group row <?= $key === $model->errorRow ? 'has-error' : ''; ?>">
                                    <div class="col-xs-3 padding-right-5">
                                        <input class="form-control" name="EditorRotation[table][]" type="text" placeholder="Table" value="<?= $row['table'] ?>"/>
                                    </div>
                                    <div class="col-xs-2 padding-left-0 padding-right-5">
                                        <input class="form-control" name="EditorRotation[rows_limit][]" type="number" placeholder="Rows limit" value="<?= $row['rows_limit'] ?>"/>
                                    </div>
                                    <div class="col-xs-2 padding-left-0 padding-right-5">
                                        <input class="form-control" name="EditorRotation[days_limit][]" type="number" placeholder="Days limit" value="<?= $row['days_limit'] ?>"/>
                                    </div>
                                    <div class="col-xs-2 padding-left-0 padding-right-5">
                                        <input class="form-control" name="EditorRotation[date_field][]" type="text" placeholder="Date field" value="<?= $row['date_field'] ?>"/>
                                    </div>
                                    <div class="col-xs-2 padding-left-0">
                                        <input class="form-control" name="EditorRotation[leave_rows][]" type="number" placeholder="Leave rows" value="<?= $row['leave_rows'] ?>"/>
                                    </div>
                                    <div class="col-xs-1 padding-left-0">
                                        <button class="btn btn-remove btn-danger" type="button"><span class="glyphicon glyphicon-minus"></span></button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="entry">
                            <div class="form-group row">
                                <div class="col-xs-3 padding-right-5">
                                    <input class="form-control" name="EditorRotation[table][]" type="text" placeholder="Table" value=""/>
                                </div>
                                <div class="col-xs-2 padding-left-0 padding-right-5">
                                    <input class="form-control" name="EditorRotation[rows_limit][]" type="number" placeholder="Rows limit" value=""/>
                                </div>
                                <div class="col-xs-2 padding-left-0 padding-right-5">
                                    <input class="form-control" name="EditorRotation[days_limit][]" type="number" placeholder="Days limit" value=""/>
                                </div>
                                <div class="col-xs-2 padding-left-0 padding-right-5">
                                    <input class="form-control" name="EditorRotation[date_field][]" type="text" placeholder="Date field" value=""/>
                                </div>
                                <div class="col-xs-2 padding-left-0">
                                    <input class="form-control" name="EditorRotation[leave_rows][]" type="number" placeholder="Leave rows" value=""/>
                                </div>
                                <div class="col-xs-1 padding-left-0">
                                    <button class="btn btn-success btn-add" type="button"><span class="glyphicon glyphicon-plus"></span></button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-xs-11 pbottom10">
                    <button type="submit" class="btn btn-success">Save</button>
                </div>

                <div class="col-xs-1 padding-left-0">
                    <button class="btn btn-success btn-add" type="button"><span class="glyphicon glyphicon-plus"></span></button>
                </div>
            </form>
        </div>
    </div>
</div>