<?php
include_once("../../dbconnect.php");

// Expects $_REQUEST[]:
// phone, userid, leadid, projectid

header('Content-Type: application/json');

date_default_timezone_set("Asia/Manila");

function dump_event($ecode,$data,$server,$port) 
{
// Expects:
//  $regionhost - region's dialer
//  $phone - phone numeber
//  $provider

	global $phone, $userid, $leadid, $projectid, $regiondialer, $provider, $MAX_TRIES, $_DEBUG, $phoneprefix;

	// FAILSAFE
	if (time() - $_SERVER['REQUEST_TIME'] > 45)
	{
		$_DEBUG = 1;

		if ($_DEBUG) syslog(LOG_DEBUG, "TIMED OUT: matches[] " . json_encode($GLOBALS['matches']));
		if ($_DEBUG) syslog(LOG_DEBUG, "DIAL: " . $phone . "@" . $provider);
		exit;
	}

    openlog(basename($_SERVER["PHP_SELF"]), LOG_PID, LOG_LOCAL0);
    // syslog(LOG_DEBUG, "START : function dump_event()");

	if ($data['Event'] == 'Dial')
	{
		if ($data['SubEvent'] == 'Begin')
		{
//			if (preg_match("/^($phone)@($provider)/", $data['Dialstring'], $GLOBALS['matches'][$data['UniqueID']]))
			if (preg_match("/.*($phone)@($provider)/", $data['Dialstring'], $GLOBALS['matches'][$data['UniqueID']]))
			{
				if ($_DEBUG) syslog(LOG_DEBUG, json_encode($data));
				
				$GLOBALS['matches'][$data['UniqueID']][3] = $data['UniqueID'];
				$GLOBALS['matches'][$data['UniqueID']][4] = $data['DestUniqueID'];
			}
		}
	}

	if ($data['Event'] == 'Newchannel')
	{
		if (is_array($GLOBALS['matches'][$data['Uniqueid']]))
		{
			if ($data['Uniqueid'] == $GLOBALS['matches'][$data['Uniqueid']][3])
			{
				if ($_DEBUG) syslog(LOG_DEBUG, json_encode($data));
				$GLOBALS['matches'][$data['Uniqueid']][5] = $data['Channel'];
			}
			else if ($data['Uniqueid'] == $GLOBALS['matches'][$data['Uniqueid']][4])
			{
				if ($_DEBUG) syslog(LOG_DEBUG, json_encode($data));
				$GLOBALS['matches'][$data['Uniqueid']][6] = $data['Channel'];
			}
		}
	}

	if ($data['Event'] == 'Bridge')
	{
		if (is_array($GLOBALS['matches'][$data['Uniqueid1']]))
		{
			$exit_bridge = false;
			if ($data['Uniqueid1'] == $GLOBALS['matches'][$data['Uniqueid1']][3])
			{
				if ($_DEBUG) syslog(LOG_DEBUG, json_encode($data));
				if (count($GLOBALS['matches'][$data['Uniqueid1']]) < 7)
				{
					$GLOBALS['matches'][$data['Uniqueid1']][5] = $data['Channel1'];
				}
				$exit_bridge = true;
			}
			else if ($data['Uniqueid2'] == $GLOBALS['matches'][$data['Uniqueid1']][4])
			{
				if ($_DEBUG) syslog(LOG_DEBUG, json_encode($data));
				if (count($GLOBALS['matches'][$data['Uniqueid1']]) < 7)
				{
					$GLOBALS['matches'][$data['Uniqueid1']][6] = $data['Channel2'];
				}
				$exit_bridge = true;
			}

			if ($exit_bridge)
			{
				$uniqueid = $GLOBALS['matches'][$data['Uniqueid1']];
				$return_array = array( "id" => 0, "msg" => "Phone Number: <font color='blue'>$phone</font><br/>Status: <font color='green'>Connected!</font>");
				// echo json_encode( $return_array );
				// exit;
				if ($MAX_TRIES == 0)
				{
					echo json_encode( array("id" => -1, "msg" => "Phone Number: <font color='blue'>$phone</font><br/>Status: <font color='green'>Undetermined!</font>") );
					exit;
				}
				else
				{
					$_qry = "SELECT * FROM callhistory WHERE callid = '".$GLOBALS['matches'][$data['Uniqueid1']][3]."'";
					include("iscallmine.php");
				}
			}
		
		}
	}

	if ($data['Event'] == 'Hangup')
	{
		if (is_array($GLOBALS['matches'][$data['Uniqueid']]))
		{
			$exit_hangup = false;			
			if ($data['Uniqueid'] == $GLOBALS['matches'][$data['Uniqueid']][3])
			{
				if ($_DEBUG) syslog(LOG_DEBUG, json_encode($data));
				if (count($GLOBALS['matches'][$data['Uniqueid']]) < 7)
				{
					$GLOBALS['matches'][$data['Uniqueid']][5] = $data['Channel'];
					$GLOBALS['matches'][$data['Uniqueid']][7] = sprintf("(%s) %s", $data['Cause'], $data['Cause-txt']);
				}
				$exit_hangup = true;			
			}
			else if ($data['Uniqueid'] == $GLOBALS['matches'][$data['Uniqueid']][4])
			{
				if ($_DEBUG) syslog(LOG_DEBUG, json_encode($data));
				if (count($GLOBALS['matches'][$data['Uniqueid']]) < 7)
				{
					$GLOBALS['matches'][$data['Uniqueid']][5] = 'unknown';
					$GLOBALS['matches'][$data['Uniqueid']][6] = $data['Channel'];
					$GLOBALS['matches'][$data['Uniqueid']][7] = sprintf("(%s) %s", $data['Cause'], $data['Cause-txt']);
				}
				$exit_hangup = true;			
			}

			if ($exit_hangup)
			{
				$uniqueid = $GLOBALS['matches'][$data['Uniqueid']];
				$hangupreason = $GLOBALS['matches'][$data['Uniqueid']][7];
				// $return_array = array( "id" => -1, "msg" => "Phone Number: <font color='blue'>$phone</font><br/>Status: <font color='red'>Hangup!</font><br/>Reason: <font color='orange'>" . $GLOBALS['matches'][$data['Uniqueid']][7] . "</font>");

				if ( preg_match("/^\(1\)/",$hangupreason) )
				{
					$return_array = array( "id" => -101, "msg" => "Phone Number: <font color='blue'>$phone</font><br/>Status: <font color='red'>Hangup!</font><br/>Reason: <font color='orange'>" . "Disconnected Phone Number" . "</font>");
				}
				else
				{
					$return_array = array( "id" => -1, "msg" => "Phone Number: <font color='blue'>$phone</font><br/>Status: <font color='red'>Hangup!</font><br/>Reason: <font color='orange'>" . "Call Failed" . "</font>");
				}

				// echo json_encode( $return_array );
				// exit;
				if ($MAX_TRIES == 0)
				{
					echo json_encode( array("id" => -1, "msg" => "Phone Number: <font color='blue'>$phone</font><br/>Status: <font color='green'>Undetermined!</font>") );
					exit;
				}
				else
				{
					$_qry = "SELECT * FROM callhistory WHERE callid = '".$GLOBALS['matches'][$data['Uniqueid']][3]."'";
					include("iscallmine.php");
				}
			}
		}
	}
}


	require_once("../../PHPAGI/phpagi-asmanager.php");

	$_qry = "
		SELECT 
			projectname, projectid, b.host, c.name, a.prefix 
		FROM projects a 
			CROSS JOIN bc_servers b 
				ON a.region = b.region and b.type = 'dialer' and b.status = 1 and b.online = 1 
			CROSS JOIN bc_providers c 
				ON a.providerid = c.id 
		WHERE a.projectid = ". $_REQUEST['projectid'] ."
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

	$phone = $_REQUEST['phone'];
	$userid = $_REQUEST['userid'];
	$leadid = $_REQUEST['leadid'];
	$projectid = $_REQUEST['projectid'];

	$phoneprefix = $campaign_rec['prefix'];
	$provider = $campaign_rec['name'];
	$regionhost = $campaign_rec['host'];

	$MAX_TRIES = 1;
	$_DEBUG = false;

	$asm = new AGI_AsteriskManager();
	if($asm->connect($regionhost,'bcpami','cd84b1ade73162c123bca44bf398e6e9'))
	{
		$asm->Events('call');
		$asm->add_event_handler("Newchannel", "dump_event");
		$asm->add_event_handler("Newcallerid", "dump_event");
		$asm->add_event_handler("Dial", "dump_event");
		$asm->add_event_handler("Newstate", "dump_event");
		$asm->add_event_handler("Bridge", "dump_event");
		$asm->add_event_handler("Hangup", "dump_event");
		
		print_r($asm->wait_response());

		$asm->disconnect();
	}
	else
	{
		mysql_query("Update liveusers set statustimestamp = '".time()."' where userid ='$userid'");
		echo json_encode( array( "id" => -1, "msg" => "Can't connect to region host $regionhost!") );
		exit;
	}
?>
