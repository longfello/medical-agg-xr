<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 11.06.18
 * Time: 13:58
 */

?>

<h4>Method parameters:</h4>
<table class="table table-bordered table-striped">
  <tr>
    <th>Param</th>
    <th>Required</th>
    <th>Description</th>
  </tr>
  <tr>
    <td>hash</td>
    <td>+</td>
    <td>Patient's SLID hash</td>
  </tr>
  <tr>
    <td>forceNextUpdate</td>
    <td>-</td>
    <td>If given and equivalents to "1" then schedules next automatic update to NOW()</td>
  </tr>
</table>

<h4>Return params:</h4>
<table class="table table-bordered table-striped">
  <tr>
    <th>Param</th>
    <th>Description</th>
  </tr>
  <tr>
    <td>result</td>
    <td>true - if all is ok, false - if something is wrong with patients state</td>
  </tr>
  <tr>
    <td>html</td>
    <td>Html representation of result</td>
  </tr>
</table>


