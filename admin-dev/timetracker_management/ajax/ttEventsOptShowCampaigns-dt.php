<?php
	include("./../../../dbconnect.php");

	$_qry =	"
		SELECT 
			projectid, 
			projectname, 
			fnttOptGet(projectid,'isEnabled') as isTTenabled 
		FROM projects
		WHERE bcid=".$_REQUEST['_bcid']."
		;	
	";

	$_results = mysql_query($_qry);

	if (!$_results)
		die(mysql_error() . " : " . $_qry);

	while ($row = mysql_fetch_array($_results, MYSQL_NUM))
	{
		if ($row[2])
			$_select_options = '<option value=1 selected>Enabled</option><option value=0>Disabled</option>';
		else
			$_select_options = '<option value=1>Enabled</option><option value=0 selected>Disabled</option>';

		$_select = '<select style="width: 200px" id="ttrackeropt'.$row[0].'"  onchange="mc_update(\'ttrackeropt\',\''.$row[0].'\')">'.$_select_options.'</select>';
		$row[2] = $_select;

		$finalrows[] = $row;
	}
	array_pop($row);

	echo '{ "aaData" : ' . json_encode($finalrows) . ' }';

	mysql_free_result($_results);
?>