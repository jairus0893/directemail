<b><?php

	if (isset($_REQUEST['curUSERNAME']));
		$requsername = $_REQUEST['curUSERNAME'];

	if (isset($_REQUEST['dbBreak']));
		$reqdbbreak = $_REQUEST['dbBreak'];

	if (isset($_REQUEST['dbBreakDesc']));
		$reqdbbreakdesc = $_REQUEST['dbBreakDesc'];

	if (!isset($requsername) || !isset($reqdbbreak) || !isset($reqdbbreakdesc))
		die("User / Custom Event Can't Be Blank. ['$reqdbbreak', '$reqdbbreakdesc']");

	$result = mysql_query(
				"
					INSERT INTO ttevents
					(bc_id, user_id, break, breakdesc)
					VALUES
					($bcid, $requsername, '". $reqdbbreak ."', '". $reqdbbreakdesc ."')
				"
			);

	if (!$result) {
	    die('DB Error: ' . mysql_error());
	}

	echo "New Custom Event Added"

?></b>