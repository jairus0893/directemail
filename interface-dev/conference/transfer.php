<?php
	include_once("../../dbconnect.php");
	require_once("../../PHPAGI/phpagi-asmanager.php");
	include_once("owner_channel_id.php");

	header('Content-Type: application/json');

phplog_on();

	$_qry = "SELECT * FROM callhistory WHERE leadid = " . $_REQUEST['leadid'] . " AND phone = '". $_REQUEST['phone'] ."' AND projectid = " . $_REQUEST['projectid'] . " AND userid = " . $_REQUEST['userid'] . " ORDER BY id DESC LIMIT 1";
	$callhistory_res = mysql_query($_qry);

// phplog("QUERY: $_qry");

	if (!callhistory_res)
	{
		echo json_encode( array("callmanid" => 0, "msg" => "Error[callhistory_res]: " . mysql_error() . ". $_qry ") );
	}
	else
	{
		if (mysql_num_rows($callhistory_res) > 0)
		{

			$callhistory_rec = mysql_fetch_assoc($callhistory_res);

			$host = $callhistory_rec['host'];

			$callid = $callhistory_rec['callid'];

// phplog("CALLHISTORY: " . json_encode($callhistory_rec));

			if ( !is_null($callhistory_rec['cli']) )
			{
				$search_peer = $callhistory_rec['conference'];
				$search_number = $_REQUEST['phone'];

				if ( $callhistory_rec['prefix'] <> 'X' && $callhistory_rec['prefix'] <> 'x' && !is_null($callhistory_rec['prefix']) )  
				{
					$search_number = $callhistory_rec['prefix'] . $search_number;					
				}

				// include("owner_channel_id.php");
				$channel = ast_owner_channel_id($host, $search_number);
			}
			else
			{
				$channel = $callhistory_rec['channel'];
			}

			if ($_REQUEST['xferto_agent'] == 'Any Agent')
			{
				$extension = '#' . $_REQUEST['leadid'] . '#' . $_REQUEST['phone'];
			}
			else
			{
				$extension = $_REQUEST['xferto_agent'] . '#' . $_REQUEST['leadid'] . '#' . $_REQUEST['phone'];
			}
			$asm = new AGI_AsteriskManager();
			if($asm->connect($host,'bcpami','cd84b1ade73162c123bca44bf398e6e9'))
			{
				$_command = "channel redirect $channel default," . $_REQUEST['xferto'] . $extension . ",1 ";
				// $_command = "channel redirect $channel default," . $_REQUEST['xferto'] . ",1 ";

				$peer = $asm->command($_command);

				$asm->disconnect();
			}
			
			// This runs only when transferring inbound calls. Transferring inbound calls do not trigger a hangup on the agent UI
			if ( is_array($callhistory_rec) && $callhistory_rec['provider'] == null)
			{

				phplog("NOTIFY AGENT OF HUNG UP!");

				$agent_phone_res = mysql_query("SELECT * FROM bc_phones WHERE name= '". $callhistory_rec['exten'] ."'");
				$notify_extension = "000" . $callhistory_rec['exten'] . "997";

				if (mysql_num_rows($agent_phone_res) > 0)
				{
					$agent_phone_rec = mysql_fetch_assoc($agent_phone_res);
					$confchan = $agent_phone_rec['confchan'];
					$confserver = $agent_phone_rec['confserver'];

					phplog("(((CALLER HUNG UP))) CONFCHAN: " . $confchan . " / CONFSERVER: " . $confserver . " / EXTENSION: " . $notify_extension);

					mysql_query("UPDATE liveusers SET status = 'ended', callchannel = NULL WHERE userid = " . $callhistory_rec['userid']);

					include('remote_agent_notification.php');
				}
			}			
			echo json_encode( array("callmanid" => $channel, "msg" => "$callid Transferred to: " . $_REQUEST['xferto'] . " ext " . $extension) );
		}
		else
		{
			echo json_encode( array("callmanid" => 0, "msg" => "Error[mysql_num_rows()]: Call history not found. $_qry") );
		}
	}			
?>