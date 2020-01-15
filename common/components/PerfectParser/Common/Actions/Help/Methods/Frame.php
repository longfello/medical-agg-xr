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
    <td>page</td>
    <td>+</td>
    <td>Identifies link to which page should be returned. Available values: <ul><li>medication-reminders</li></ul></td>
  </tr>
  <tr>
    <td>session_id</td>
    <td>+</td>
    <td>Patient's UMR token</td>
  </tr>
  <tr>
    <td>patient_id</td>
    <td>+</td>
    <td>Patient's SLID#</td>
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
        <td>true - token and url have been created successfully;<br>false - error occurred on attempt to create token or url.</td>
    </tr>
    <tr>
        <td>url</td>
        <td>if returned result = true - url param is a link to requested, otherwise - one of the error params (see "Critical error response format" section at the top of the current page) instead of url param.</td>
    </tr>
</table>

