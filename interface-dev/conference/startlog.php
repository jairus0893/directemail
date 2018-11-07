<?php

	function startlog($action)
	{
		// global $auid, $pid;
		$auid = $_REQUEST['userid'];
		$pid = $_REQUEST['projectid'];

		mysql_query("UPDATE actionlog SET epochend = IF(epochend IS NULL,".time().",epochend) WHERE  userid = $auid ORDER BY logid DESC LIMIT 1");

		mysql_query("INSERT INTO actionlog SET daydate = substr(NOW(),1,10), userid = $auid, projectid = $pid, action = '$action', epochstart = '".time()."'");
		$newactionid = mysql_insert_id();
		
		mysql_query("UPDATE liveusers SET actionid = '$newactionid' WHERE userid = '$auid'");

		return $newactionid;
	}
?>