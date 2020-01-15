<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 11.06.18
 * Time: 13:58
 */

?>

<p>Patient medical data must be body of the POST-request.</p>

<h4>Method parameters:</h4>
<table class="table table-bordered table-striped">
  <tr>
    <th>Param</th>
    <th>Required</th>
    <th>Description</th>
  </tr>
  <tr>
    <td>slid</td>
    <td>+</td>
    <td>Patient's SLID</td>
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
    <td>Operation result. Boolean value, true - data processed successful, false - data processing failed.</td>
  </tr>
  <tr>
    <td>errors</td>
    <td>Array of errors</td>
  </tr>
  <tr>
    <td>log</td>
    <td>If test mode enabled - html representation of data processing process.</td>
  </tr>
</table>


