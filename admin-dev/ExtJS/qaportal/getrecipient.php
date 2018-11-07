<?php


include_once "../../../dbconnect.php";

$clientid		 	= $_REQUEST['clientid'];
$bcid		 	    = $_REQUEST['bcid'];
$temp = array();

$res = mysql_query("SELECT client_contacts.*, members.userlogin, members.userpass, members.usertype as usermode from client_contacts 
left join members on client_contacts.userid = members.userid where clientid = $clientid and client_contacts.bcid = '$bcid' and client_contacts.active = 1");

while ($row = mysql_fetch_assoc($res)){

    $tempname = $row['email'];
    $fname = $row['firstname'];
    $lname = $row['lastname'];
    $temp[] = array('email'=> $tempname , 'name'=>$fname.''.$lname);
}
        
echo json_encode($temp);




