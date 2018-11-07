<?php
session_start();
include "../dbconnect.php";

$uid = $_SESSION['uid'];
if (strlen($uid) == 0)
	{
		exit();
	}
function send_frame($buf, $host="127.0.0.1", $port=9007) {
   
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	socket_connect($socket,"127.0.0.1", $port);
    socket_send($socket, $buf, strlen($buf),0);
    socket_close($socket);

}
$buf = serialize($_REQUEST);
send_frame($buf);
?>