<?php

include "../../../dbconnect.php";
$act       =  $_REQUEST['act'];
$sigid     =  $_REQUEST['sigid'];
$sigbody   =  $_POST['sigbody'];
$signame   =  $_POST['signame'];

if ($act == 'getsignaturebody') {  
    
    $query               = "SELECT * from signatures where sigid = '$sigid'";
    $query2              = mysql_query($query);
    $row                 = mysql_fetch_array($query2);
    $signature_body      = $row['signature_body'];
    $signature_name      = $row['signature_name'];

    echo $signature_body;

}else if ($act == 'getsignaturename') {

    $query              = "SELECT * from signatures where sigid = '$sigid'";
    $query              = mysql_query($query);
    $row                = mysql_fetch_array($query);
    $signature_name     = $row['signature_name'];

    echo $signature_name;


}else if ($act == 'updatesignature') {

    $update = "update signatures set signature_body = '".mysql_real_escape_string($sigbody)."', signature_name = '$signame' where sigid = '$sigid' ";
    mysql_query($update) or die(mysql_error());
    exit;

    echo 'Successfully Updated';
}


