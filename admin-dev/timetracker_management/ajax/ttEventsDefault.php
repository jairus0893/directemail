<?php
	include("./../../../dbconnect.php");

	$result = mysql_query(
			"SELECT 
				break, breakdesc 
			FROM ttevents 
			WHERE
				bc_id = 0
				AND fnttEventActiveGet(id) > 0
			ORDER BY id
			"
	);

	while ($row[] = mysql_fetch_array($result, MYSQL_NUM));
	array_pop($row);

	echo '{ "aaData" : ' . json_encode($row) . ' }';

	mysql_free_result($result);
?>