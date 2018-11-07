<?php
include_once("../../dbconnect.php");

// Expects $_REQUEST[]:
// phone, userid, leadid, projectid

header('Content-Type: application/json');

date_default_timezone_set("Asia/Manila");

function endcallmonitor($ecode,$data,$server,$port) 
{
	global $phone, $userid, $leadid, $projectid, $regiondialer, $provider, $MAX_TRIES, $_DEBUG;
	global $extension, $confserver, $status;
	global $asm;

	// FAILSAFE
	if (time() - $_SERVER['REQUEST_TIME'] > 60)
	{
		$_DEBUG = 1;

		// if ($_DEBUG) syslog(LOG_DEBUG, "TIMED OUT: matches[] " . json_encode($GLOBALS['matches']));
		// if ($_DEBUG) syslog(LOG_DEBUG, "CHANSPYSTART: " . $extension . "@" . $confserver);

		echo json_encode( array( "id" => 1, "msg" => "TIMED OUT!") );
		$asm->disconnect();
		mysql_query("Update liveusers set statustimestamp = '".time()."' where userid ='$userid'");
		if ($_DEBUG) syslog(LOG_DEBUG, "$userid: TIMED OUT! ($status) $extension @ $confserver ");
		exit;
	}

    openlog(basename($_SERVER["PHP_SELF"]), LOG_PID, LOG_LOCAL0);
    // syslog(LOG_DEBUG, "START : function endcallmonitor()");

	if ($data['Event'] == 'Newchannel')
	{
		if ($data['ChannelStateDesc'] == 'Ring')
			{
			if (preg_match("/^Local\/000($extension)997@default/", $data['Channel']))
			{
				preg_match("/^Local\/000($extension)997@default/", $data['Channel'], $GLOBALS['matches'][$data['Uniqueid']]);
				$GLOBALS['matches'][$data['Uniqueid']][2] = $data['Uniqueid'];

				if ($_DEBUG) syslog(LOG_DEBUG, "$userid: Hanging Up... ($status) $extension @ $confserver ");
			}
		}
	}

	if ($data['Event'] == 'ChanSpyStart')
	{
		if (preg_match("/^Local\/000($extension)997@default/", $data['SpyerChannel']))
		{
			preg_match("/^Local\/000($extension)997@default/", $data['SpyerChannel'], $GLOBALS['matches'][$data['Uniqueid']]);
			// if ($_DEBUG) syslog(LOG_DEBUG, "ChanSpyStart" . json_encode($GLOBALS['matches']));

			echo json_encode( array( "id" => 1, "msg" => "checkdialstate()") );
			$asm->disconnect();
			mysql_query("Update liveusers set statustimestamp = '".time()."' where userid ='$userid'");
			if ($_DEBUG) syslog(LOG_DEBUG, "$userid: Ended. ($status) $extension @ $confserver ");
		    exit;
		}
	}

	if ($data['Event'] == 'SoftHangupRequest')
	{
		if (is_array($GLOBALS['matches'][$data['Uniqueid']]) && preg_match("/^Local\/000($extension)997@default/", $data['Channel']))
		{
			if ($data['Uniqueid'] == $GLOBALS['matches'][$data['Uniqueid']][2])
			{
				// if ($_DEBUG) syslog(LOG_DEBUG, "SoftHangupRequest: " . json_encode($GLOBALS['matches']));
	
				echo json_encode( array( "id" => -1, "msg" => "Please LOGON/RE-LOGON to your extension!") );
				$asm->disconnect();
				mysql_query("Update liveusers set statustimestamp = '".time()."' where userid ='$userid'");
				if ($_DEBUG) syslog(LOG_DEBUG, "$userid: Ended, but extension offline! ($status) $extension @ $confserver ");
				exit;
			}
		}
	}

}

// MAIN //

    openlog(basename($_SERVER["PHP_SELF"]), LOG_PID, LOG_LOCAL0);

	require_once("../../PHPAGI/phpagi-asmanager.php");

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
		echo json_encode( array( "id" => -1, "msg" => "Error[liveusers_res]: " . mysql_error() . $_qry) );
		exit;
	}

	if (mysql_num_rows($liveusers_res) == 0)
	{
		echo json_encode( array( "id" => -1, "msg" => "User not found!") );
		exit;
	}

	$liveusers_rec = mysql_fetch_assoc($liveusers_res);
	mysql_close($liveusers_res);

	$phone = $_REQUEST['phone'];
	$userid = $_REQUEST['userid'];
	$leadid = $liveusers_rec['leadid'];
	$projectid = $liveusers_rec['projectid'];

	$provider = $liveusers_rec['name'];
	$regionhost = $liveusers_rec['host'];

	$extension = $liveusers_rec['extension'];
	$confserver = $liveusers_rec['confserver'];
	$status = $liveusers_rec['status'];
	$amiport = 6038;

	$MAX_TRIES = 1;
	$_DEBUG = true;

	if ($status == 'ended')
	{
		echo json_encode( array( "id" => 1, "msg" => "Waiting not necessary!") );
		mysql_query("Update liveusers set statustimestamp = '".time()."' where userid ='$userid'");
		if ($_DEBUG) syslog(LOG_DEBUG, "$userid: SKIP! ($status) $extension @ $confserver ");
		exit;
	}

	$asm = new AGI_AsteriskManager();
	if($asm->connect($confserver . ':' . $amiport,'callmonitor','cd84b1ade73162c123bca44bf398e6e9'))
	{
		if ($_DEBUG) syslog(LOG_DEBUG, "$userid: InCall. ($status) $extension @ $confserver ");

		$asm->Events('call');

		$asm->add_event_handler("Newchannel", "endcallmonitor");
		$asm->add_event_handler("ChanSpyStart", "endcallmonitor");
		$asm->add_event_handler("SoftHangupRequest", "endcallmonitor");
		
		do
		{
	    	$params = $asm->wait_response(true);

			// FAILSAFE
			if (time() - $_SERVER['REQUEST_TIME'] > 60)
			{
				$_DEBUG = 1;

				echo json_encode( array( "id" => 1, "msg" => "TIMED OUT!") );
				$asm->disconnect();
				mysql_query("Update liveusers set statustimestamp = '".time()."' where userid ='$userid'");
				if ($_DEBUG) syslog(LOG_DEBUG, "$userid: TIMED OUT ON WHILE! ($status) $extension @ $confserver ");
				exit;
			}
		} while (!array_key_exists('GoodBye',$params));

		// $asm->disconnect();
		exit;
	}
	else
	{
		echo json_encode( array( "id" => -1, "msg" => "Can't connect to conference host $confserver!") );
		mysql_query("Update liveusers set statustimestamp = '".time()."' where userid ='$userid'");
		if ($_DEBUG) syslog(LOG_DEBUG, "$userid: Can't Connect. ($status) $extension @ $confserver ");
		exit;
	}
?>
