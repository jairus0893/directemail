<?php
$res = mysql_query("SELECT * from teams where bcid = '$bcid'");
$td ='';
while ($row = mysql_fetch_assoc($res))
{
    $td .= '<option value="'.$row['teamid'].'">'.$row['teamname'].'</option>';
}
?>
<title></title>
<div style="width:200px; height:100px">
Select Team:
<select name="teamid" id="teamid">
    <?=$td;?>
</select>
<input type="button" value="Done" class="jbut" onclick="bulkaction()">
</div>
