<?php
if ($act == 'updatecamp' && $_REQUEST['fld'] == 'ttrackeropt')
{
	$project_id = $_REQUEST['pid'];
	$field = $_REQUEST['fld'];
	$val = $_REQUEST['vl'];
	$user_id = $_SESSION['uid'];

	$_res = mysql_query("INSERT INTO tteventsopt (project_id, user_id, config, value) VALUES ($project_id,$user_id,'isEnabled', $val)");
	$_insert_id = mysql_insert_id();
	if (!_res)
		printf("DB Error: %s", mysql_error());
	else
		echo printf("New record inserted with id: %s", $_insert_id);

	// print_r($_SESSION);
	exit;
}
?>