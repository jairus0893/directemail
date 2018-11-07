<?php

function lastRun ($buffer)
{
	file_put_contents($_SERVER['PHP_SELF'] . "-lastRun.out", $buffer);
}

date_default_timezone_set("Asia/Manila");
//ob_end_clean();

#$dbhost = '10.24.27.1';
$dbhost = '10.0.1.184';
$dbuser = 'bcweb';
$dbpass = '17c7e8d1c00cb4d6bf6dacd5c97ba617';
$db = 'proactiv';

// $confconntype = 'sip';

$dblink = false;

while (!$dblink)
{
	echo "\n" . date("Y-m-d h:i:s") . " Connecting to DB...\n";
	$dblink = mysql_connect($dbhost, $dbuser, $dbpass);

	if (!$dblink) 
		echo "\n" . date("Y-m-d h:i:s") . " " . mysql_error() . "\n";
}
echo date("Y-m-d h:i:s") . " ((( Connected! ))) \n";

$thread_id = mysql_thread_id($dblink);

mysql_select_db($db) or die (mysql_error());


echo "*****************************************************************\n";
?>
