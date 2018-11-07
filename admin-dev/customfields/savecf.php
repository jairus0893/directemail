<?php

if ($act == 'savecf')
{
    $fn = $_REQUEST['fieldname'];
    // print_r($fn);
   $res = mysql_query("SELECT * from projects where projectid = '".$_REQUEST['projectid']."'");
   $row = mysql_fetch_assoc($res);
    $cf = json_decode($row['customfields'], true);
    foreach ($fn as $key => $value) {
        $cfnew[$value['name']] = $value['value'];
    }
    foreach ($cf as $key => $value)
    {
        $cf[$key] = $cfnew[$key];
    }
    mysql_query("update projects set customfields = '".  mysql_real_escape_string(json_encode($cf))."' where projectid = '".$_REQUEST['projectid']."'");
    exit;
}