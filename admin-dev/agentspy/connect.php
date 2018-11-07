<?php
	include_once("../../dbconnect.php");
	require_once("../../PHPAGI/phpagi-asmanager.php");
	include("agentspy_config.php");

	header('Content-Type: application/json');

	$_qry = "SELECT * FROM bc_phones WHERE name = '". $_REQUEST['admin_exten'] ."'";
	$bc_phones_res = mysql_query($_qry);

	if (!bc_phones_res)
	{
		echo json_encode( array("id" => 0, "msg" => "Error[bc_phones_res]: " . mysql_error() . ". $_qry ") );
	}
	else
	{
		if (mysql_num_rows($bc_phones_res) > 0)
		{

			$bc_phones_rec = mysql_fetch_assoc($bc_phones_res);

			$conference = $bc_phones_rec['confserver'];
			$channel = sprintf("%s%s/000%s", __IAX2, $conference, $_REQUEST['admin_exten']);
			$destination = sprintf("000%s", $_REQUEST['agent_exten']);

			$_qry = "
				SELECT 
					a.projectname, a.projectid, b.host, c.name 
				FROM projects a 
					CROSS JOIN bc_servers b 
						ON a.region = b.region and b.type = 'dialer' and b.status = 1 and b.online = 1 
					CROSS JOIN bc_providers c 
						ON a.providerid = c.id 
					CROSS JOIN liveusers d
						ON a.projectid = d.projectid
				WHERE d.extension = '". $_REQUEST['agent_exten'] ."'
			";

			$campaign_res = mysql_query($_qry);

			if (!$campaign_res)
			{
				echo json_encode( array( "id" => -1, "msg" => "Error[campaign_res]: " . mysql_error() . $_qry) );
				exit;
			}

			if (mysql_num_rows($campaign_res) == 0)
			{
				echo json_encode( array( "id" => -1, "msg" => "Campaign not found!") );
				exit;
			}

			$campaign_rec = mysql_fetch_assoc($campaign_res);
			$dialer = $campaign_rec['host'];

			phplog_on();
			phplog("CAMPAIGN_REC: " . json_encode($campaign_rec));

			$asm = new AGI_AsteriskManager();
			if($asm->connect($dialer,'bcpami','cd84b1ade73162c123bca44bf398e6e9'))
			{
				$_command = "channel originate $channel extension $destination@default ";

				$peer = $asm->command($_command);

				$asm->disconnect();
			}
			echo json_encode( array("id" => 0, "msg" => $_command) );
		}
		else
		{
			echo json_encode( array("id" => 0, "msg" => "Error[mysql_num_rows()]: Extension not found.") );
		}
	}			
?>