<?php
	include_once("../../dbconnect.php");

	header('Content-Type: application/json');

	$_qry = "CALL spCampaignCheckAgentsOn(" . $_REQUEST['projectid'] . ")";
	$campaign_res = mysql_query($_qry);

	if (!$campaign_res)
	{
		echo json_encode( array("callmanid" => 0, "msg" => "<option>Error[campaign_res]: " . mysql_error() . ". $_qry</option>") );
	}
	else
	{		
		if (mysql_num_rows($campaign_res) > 0)
		{
			$options = '';
			while ($campaign_rec = mysql_fetch_assoc($campaign_res))
			{
				$options .= sprintf("<option value='%s' label='%d'>%s</option>", $campaign_rec['extension'], $campaign_rec['userid'], $campaign_rec['agent_name']);
			}

			if ($options == '')
			{
				echo json_encode( array("callmanid" => -1, "msg" => "none") );
			}
			else
			{
				echo json_encode( array("callmanid" => 0, "msg" => "<option>Any Agent</option>" . $options) );
			}
		}
		else
		{
			echo json_encode( array("callmanid" => -1, "msg" => "<option>Error[mysql_num_rows()]: No agents on found for campaign.</option>") );
		}
	}
?>