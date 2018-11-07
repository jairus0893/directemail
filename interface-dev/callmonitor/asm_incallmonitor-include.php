<?php
include_once("../dbconnect.php");

// Expects $_REQUEST[]:
// phone, userid, leadid, projectid

// header('Content-Type: application/json');

date_default_timezone_set("Asia/Manila");

function dump_event($ecode,$data,$server,$port) 
{
	global $phone, $userid, $leadid, $projectid, $regiondialer, $provider, $MAX_TRIES, $_DEBUG;
	global $extension, $confserver, $status;

	// FAILSAFE
	if (time() - $_SERVER['REQUEST_TIME'] > 28800)
	{
		$_DEBUG = 1;

		if ($_DEBUG) syslog(LOG_DEBUG, "TIMED OUT: matches[] " . json_encode($GLOBALS['matches']));
		if ($_DEBUG) syslog(LOG_DEBUG, "CHANSPYSTART: " . $extension . "@" . $confserver);

		syslog(LOG_DEBUG, json_encode( array( "id" => -1, "msg" => "TIMED OUT!") ));

		exit;
	}

    openlog(basename($_SERVER["PHP_SELF"]), LOG_PID, LOG_LOCAL0);
    // syslog(LOG_DEBUG, "START : function dump_event()");

	if ($data['Event'] == 'Newchannel')
	{
		if ($data['ChannelStateDesc'] == 'Ring')
			{
			if (preg_match("/^Local\/000($extension)998@default/", $data['Channel'], $GLOBALS['matches'][$data['Uniqueid']]))
			{
				$GLOBALS['matches'][$data['Uniqueid']][2] = $data['Uniqueid'];
			}
		}
	}

	if ($data['Event'] == 'ChanSpyStart')
	{
		if (preg_match("/^Local\/000($extension)998@default/", $data['SpyerChannel'], $GLOBALS['matches'][$data['Uniqueid']]))
		{
			if ($_DEBUG) syslog(LOG_DEBUG, "ChanSpyStart" . json_encode($GLOBALS['matches']));

			syslog(LOG_DEBUG, json_encode( array( "id" => 1, "msg" => "checkforcalls()") ));

		    exit;
		}
	}

	if ($data['Event'] == 'SoftHangupRequest')
	{
		if (is_array($GLOBALS['matches'][$data['Uniqueid']]))
		{
			if ($data['Uniqueid'] == $GLOBALS['matches'][$data['Uniqueid']][2])
			{
				if ($_DEBUG) syslog(LOG_DEBUG, "SoftHangupRequest: " . json_encode($GLOBALS['matches']));
	
				syslog(LOG_DEBUG,  json_encode( array( "id" => -1, "msg" => "Please logon/re-logon to your extension!") ));

				exit;
			}
		}
	}

}

// MAIN //


	require_once("../PHPAGI/phpagi-asmanager.php");

	$_qry = "
		SELECT 
			z.*, a.projectname, z.projectid, b.host, c.name 
		FROM liveusers z
			CROSS JOIN projects a
				ON z.projectid = a.projectid 
			CROSS JOIN bc_servers b 
				ON a.region = b.region and b.type = 'dialer' and b.status = 1 and b.online = 1 
			CROSS JOIN bc_providers c 
				ON a.providerid = c.id 
		WHERE z.userid = ". $_REQUEST['userid'] ."
	";

	$liveusers_res = mysql_query($_qry);

	if (!$liveusers_res)
	{
		syslog(LOG_DEBUG,  json_encode( array( "id" => -1, "msg" => "Error[liveusers_res]: " . mysql_error() . $_qry) ));
		exit;
	}

	if (mysql_num_rows($liveusers_res) == 0)
	{
		syslog(LOG_DEBUG,  json_encode( array( "id" => -1, "msg" => "User not found!") ));
		exit;
	}

	$liveusers_rec = mysql_fetch_assoc($liveusers_res);

	$phone = $_REQUEST['phone'];
	$userid = $_REQUEST['uid'];
	$leadid = $liveusers_rec['leadid'];
	$projectid = $liveusers_rec['projectid'];

	$provider = $liveusers_rec['name'];
	$regionhost = $liveusers_rec['host'];

	$extension = $liveusers_rec['extension'];
	$confserver = $liveusers_rec['confserver'];
	$status = $liveusers_rec['status'];

	$MAX_TRIES = 1;
	$_DEBUG = true;

	$asm = new AGI_AsteriskManager();
	if($asm->connect('localhost','bcpami','cd84b1ade73162c123bca44bf398e6e9'))
	{
		$asm->Events('call');

		$asm->add_event_handler("Newchannel", "dump_event");
		$asm->add_event_handler("ChanSpyStart", "dump_event");
		$asm->add_event_handler("SoftHangupRequest", "dump_event");
		
		print_r($asm->wait_response());

		$asm->disconnect();
	}
	else
	{
		syslog(LOG_DEBUG,  json_encode( array( "id" => -1, "msg" => "Can't connect to region host $regionhost!") ));
		exit;
	}
?>
