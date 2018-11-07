<?php
	$ttrackeroptres = mysql_query("SELECT value FROM tteventsopt WHERE project_id=".$projrow['projectid']." and config='isEnabled' ORDER BY id desc LIMIT 1");
	if (mysql_num_rows($ttrackeroptres))
	{
		$ttrackeroptrow = mysql_fetch_row($ttrackeroptres);
		if ($ttrackeroptrow[0])
			$ttrackeroptsel = '<option value=1 selected>Enabled</option><option value=0>Disabled</option>';
		else
			$ttrackeroptsel = '<option value=1>Enabled</option><option value=0 selected>Disabled</option>';

	}
	else
	{
		$ttrackeroptsel = '<option value=0 selected>Disabled</option><option value=1>Enabled</option>';
	}
	$rows[101][1] = 'Time Tracker';
	$rows[101][2] = '<select id="ttrackeropt'.$projrow['projectid'].'"  onchange="mc_update(\'ttrackeropt\',\''.$projrow['projectid'].'\')">'.$ttrackeroptsel.'</select>';
?>
