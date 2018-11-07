<h3>Agent Interface Settings</h3>
<?php
	$rows = array();
	$headers = array();
	$headers[] = 'Option';
	$headers[] = 'Value';


	$rows[2][1] = "Chat";
	$rows[2][2] = ui_getSelectOpt($projrow['projectid'], 'isChatEnabled');
	$rows[3][1] = "Notes History";
	$rows[3][2] = ui_getSelectOpt($projrow['projectid'], 'isNotesHistoryEnabled');;
	$rows[4][1] = "Conference/Transfer";
	$rows[4][2] = ui_getSelectOpt($projrow['projectid'], 'isConferenceTransferEnabled');;
	$rows[5][1] = "Manual Call Notification";
	$rows[5][2] = ui_getSelectOpt($projrow['projectid'], 'isManualCallNotificationEnabled');;

	echo tablegen($headers,$rows,'100%');

	function ui_getSelectOpt($_projectid, $_option)
	{

		$uioptres = mysql_query("SELECT value FROM uiopt WHERE project_id=".$_projectid." and config='$_option' ORDER BY id desc LIMIT 1");
		if (mysql_num_rows($uioptres))
		{
			$uioptrow = mysql_fetch_row($uioptres);
			if ($uioptrow[0])
				$uioptsel = '<option value=1 selected>Enabled</option><option value=0>Disabled</option>';
			else
				$uioptsel = '<option value=1>Enabled</option><option value=0 selected>Disabled</option>';

		}
		else
		{
			switch ($_option) {
				case 'isChatEnabled':
				case 'isConferenceTransferEnabled':
				case 'isManualCallNotificationEnabled':
					$uioptsel = '<option value=1>Enabled</option><option value=0 selected>Disabled</option>';
					break;

				default:
					$uioptsel = '<option value=1 selected>Enabled</option><option value=0>Disabled</option>';
					break;
			}

		}
		return '<select id="uiopt-'.$_option . $_projectid.'"  onchange="mc_update(\'uiopt-'.$_option.'\',\''.$_projectid.'\')">'.$uioptsel.'</select>';
	}
?>
