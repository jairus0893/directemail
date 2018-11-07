<?php
	function ui_getOpt($_projectid, $_option)
	{

		$uioptres = mysql_query("SELECT value FROM uiopt WHERE project_id=".$_projectid." and config='$_option' ORDER BY id desc LIMIT 1");
		if (mysql_num_rows($uioptres))
		{
			$uioptrow = mysql_fetch_row($uioptres);

			return $uioptrow[0];

		}
		else
		{
			switch ($_option) {
				case 'isChatEnabled':
				case 'isConferenceTransferEnabled':
				case 'isManualCallNotificationEnabled':
					return 0;

				default:
					return 1;
			}
		}
	}
?>