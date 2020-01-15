<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 26.10.18
 * Time: 21:29
 */
?>
<style>
    tr.without-border td{
      border-top:2px dashed #ddd !important;
    }
</style>

<table class="table rotation-setting-table">
    <thead>
        <tr>
            <th>Rows limit</th>
            <th>Days limit</th>
            <th>Leave Rows</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($data)): ?>
        <?php foreach($data as $key => $row): ?>
        <tr>
            <td colspan="3"><?= $row['table'] ?></td>
        </tr>
        <tr class="without-border">
            <td><?= $row['rows_limit'] ?></td>
            <td><?= $row['days_limit'] ?></td>
            <td><?= $row['leave_rows'] ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

