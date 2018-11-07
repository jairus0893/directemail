<?php


include_once "../../../dbconnect.php";

$pid		 	= $_REQUEST['pid'];
$temp = array();

$res = mysql_query("SELECT * from templates where projectid = '$pid'");
while ($row = mysql_fetch_assoc($res)){

    $tempname = $row['template_name'];
    $tempid = $row['templateid'];
    $temp[] = array('name'=> $tempname , 'tempid'=>$tempid);
}
        
echo json_encode($temp);




