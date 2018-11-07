<?php
	include("../../dbconnect.php");

	switch ($_REQUEST['act']) {
		case 'queuecheck':
			$_qry =	"CALL spAgentCheckInboundQueue(". $_REQUEST['userid'] .")";

			$queue_res = mysql_query($_qry);

			if (!$queue_res)
			{
				die("Error[callqueue]:" . mysql_error() . " : " . $_qry);
			}

			header('Content-Type: application/json');
			echo json_encode(array("queuecalls" => mysql_num_rows($queue_res)));
			break;

		case 'campaignqueues':
			$_qry =	"CALL spAgentCheckInboundQueue(". $_REQUEST['userid'] .")";

			$queue_res = mysql_query($_qry);

			if (!$queue_res)
			{
				die("Error[callqueue]:" . mysql_error() . " : " . $_qry);
			}

			header('Content-Type: application/json');

			$campaignqueues = array();

			while ($call = mysql_fetch_assoc($queue_res))
			{
				$campaignqueues[ $call['projectid'] ]++;
			}

			echo json_encode($campaignqueues);
			break;

		case '_requests':
			header('Content-Type: application/json');
			echo json_encode($_REQUEST);

			break;
		
	}

?>
