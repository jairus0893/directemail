<?php
	include_once("../../dbconnect.php");
	include_once("startlog.php");

	header('Content-Type: application/json');

	$_qry = "SELECT * FROM projects WHERE projectid = " . $_REQUEST['projectid'];
	$campaign_res = mysql_query($_qry);

	if (!$campaign_res)
	{
		echo json_encode( array("callmanid" => 0, "msg" => "Error[campaign_res]: " . mysql_error() . ". $_qry ") );
	}
	else
	{		
		if (mysql_num_rows($campaign_res) > 0)
		{
			$campaign_rec = mysql_fetch_assoc($campaign_res);

			mysql_query("DELETE FROM callman WHERE leadid = " . $_REQUEST['leadid']);
			
			$_qry = sprintf("INSERT INTO callman SET userid = %d, leadid = %d, phone = '%s', status = 'originate', projectid = %d, prefix = '%s', bcid = %d, region = '%s', start = UNIX_TIMESTAMP(), mode = 1 "
						, $_REQUEST['userid']
						, $_REQUEST['leadid']
						, $_REQUEST['phone']
						, $_REQUEST['projectid']
						, $campaign_rec['prefix']
						, $campaign_rec['bcid']
						, $campaign_rec['region']
				);

			$callman_res = mysql_query($_qry);
			startlog("dial");

			if (!callman_res)
			{
				echo json_encode( array("callmanid" => 0, "msg" => "Error[callman_res]: " . mysql_error() . ". $_qry ") );
			}
			else
			{
			    mysql_query("UPDATE liveusers SET LEADID = ".$_REQUEST['leadid'].", status = 'dialing', actionid = '0', statustimestamp = '".time()."' where userid = " . $_REQUEST['userid']);

				echo json_encode( array("callmanid" => 0, "msg" => "Query[callman]: $_qry") );
			}

		}
		else
		{
			echo json_encode( array("callmanid" => 0, "msg" => "Error[mysql_num_rows()]: Campaign not found.") );
		}
	}
?>