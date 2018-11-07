<?php
	include_once("../../dbconnect.php");

	header('Content-Type: application/json');

	$_qry = "SELECT * FROM projects WHERE projectid = " . $_REQUEST['projectid'];
	$campaign_res = mysql_query($_qry);

	if (!$campaign_res)
	{
		echo json_encode( array("callmanid" => 0, "msg" => "<option>Error[campaign_res]: " . mysql_error() . ". $_qry</option>") );
	}
	else
	{		
		if (mysql_num_rows($campaign_res) > 0)
		{
			$campaign_rec = mysql_fetch_assoc($campaign_res);

			$bcid = $campaign_rec['bcid'];

			$_qry = "SELECT a.projectid, a.projectname, b.fromuser FROM projects a CROSS JOIN bc_providers b ON a.providerid = b.id WHERE a.bcid = $bcid AND a.dialmode = 'inbound' AND a.active = 1 AND LENGTH(b.fromuser) > 0 ORDER BY a.projectname";

			$campaign_provider_res = mysql_query($_qry);

			if (!$campaign_provider_res)
			{
				echo json_encode( array("callmanid" => 0, "msg" => "<option>Error[campaign_provider_res]: " . mysql_error() . ". $_qry</option>") );
			}
			else
			{
				if (mysql_num_rows($campaign_provider_res) > 0)
				{
					$options = '';
					while ($campaign_provider_rec = mysql_fetch_assoc($campaign_provider_res))
					{
						$options .= sprintf("<option value='%s' label='%d'>%s</option>", $campaign_provider_rec['fromuser'], $campaign_provider_rec['projectid'], $campaign_provider_rec['projectname']);
					}

					if ($options == '')
					{
						echo json_encode( array("callmanid" => 0, "msg" => "none") );
					}
					else
					{
						echo json_encode( array("callmanid" => 0, "msg" => $options) );
					}
				}
				else
				{
					echo json_encode( array("callmanid" => -2, "msg" => "<option>Error[mysql_num_rows()]: No inbound campaigns found.</option>") );
				}
			}
		}
		else
		{
			echo json_encode( array("callmanid" => -1, "msg" => "<option>Error[mysql_num_rows()]: Campaign not found.</option>") );
		}
	}
?>