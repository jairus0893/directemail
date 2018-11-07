<?php
if ( $act == 'updatecamp' && preg_match("/^uiopt-(.*)$/", $_REQUEST['fld'], $_uioption) )
{
	$project_id = $_REQUEST['pid'];
	$field = $_REQUEST['fld'];
	$val = $_REQUEST['vl'];
	$user_id = $_SESSION['uid'];

	mysql_query("INSERT INTO uiopt (project_id, user_id, config, value) VALUES ($project_id,$user_id,'".$_uioption[1]."', $val)");
	// header("Content-Type: text/html");
	// echo "INSERT INTO uiopt (project_id, user_id, config, value) VALUES ($project_id,$user_id,'".$_uioption[1]."', $val)";

	exit;
}
?>