<?php
	include("./../../../dbconnect.php");

	if(isset($_REQUEST['curBCID']))
		$reqBCID = $_REQUEST['curBCID'];

	$result = mysql_query(
			"SELECT 
				id, break, breakdesc 
			FROM ttevents 
			WHERE
				bc_id = " . $reqBCID . "
				AND fnttEventActiveGet(id) > 0
			ORDER BY id
			"
	);

	$finalrow = array();
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$row['DT_RowId'] = $row[0];
		$row['DT_RowClass'] = "customevent";
		$finalrow[] = $row;
	}
	// array_pop($finalrow);

	echo '{ "aaData" : ' . json_encode($finalrow) . ' }';

	mysql_free_result($result);
?>