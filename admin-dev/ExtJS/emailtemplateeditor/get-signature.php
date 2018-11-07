<?php


include_once "../../../dbconnect.php";

$bcid		 	= $_REQUEST['bcid'];
$sig = array();

$res = mysql_query("select * from signatures  where bcid = '$bcid'");
while ($row = mysql_fetch_assoc($res)){

    $signature = $row['signature_name'];
    $sigid = $row['sigid'];
    $sig[] = array('name'=> $signature , 'sigid'=>$sigid);
}
        
echo json_encode($sig);








