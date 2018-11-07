<?php
// Expects:
// userid, leadid, $return_array, $_DEBUG, $MAX_TRIES

$_DEBUG = 1;

	$callhistory_res = mysql_query($_qry);

	if (!$callhistory_res)
	{
		echo json_encode( array( "id" => -1, "msg" => "Error[$callhistory_res]: " . mysql_error() . $_qry) );
		exit;
	}

	if (mysql_num_rows($callhistory_res) == 0)
	{
		echo json_encode( array( "id" => -1, "msg" => $uniqueid . ": Call history not found!") );
		$MAX_TRIES--;
		exit;
	}
	else
	{
		$callhistory_rec = mysql_fetch_assoc($callhistory_res);

		if ($callhistory_rec['leadid'] == $leadid)
		{
			switch ($data['Event']) 
			{
				case 'Bridge':
					$_qry = "UPDATE callhistory SET dialstatus= 'Connected!' WHERE callid = '". $callhistory_rec['callid'] ."'";
					$res = mysql_query($_qry);
					// syslog(LOG_DEBUG, $res . " - QUERY: " . $_qry );
					$_qry = "UPDATE liveusers SET status = 'oncall', statustimestamp = '".time()."' WHERE userid = " . $userid;
					$res = mysql_query($_qry);
					// syslog(LOG_DEBUG, $res . " - QUERY: " . $_qry );

				    //$_qry = "select a.dialstatus, a.leadid, a.phone, a.userid, b.endepoch, a.conference, a.confchan from callhistory a left join finalhistory b  on a.callid=b.callid where a.leadid = $leadid and a.userid = $userid and a.startepoch >= unix_timestamp(subdate(now(), interval 1 hour)) and b.endepoch is null and a.dialstatus ='Connected!' and a.callid <> '". $callhistory_rec['callid'] ."'";
				    $_qry = "select a.dialstatus, a.leadid, a.phone, a.userid, b.endepoch, a.conference, a.confchan from callhistory a left join finalhistory b  on a.callid=b.callid where a.leadid = $leadid and a.userid = $userid and a.startepoch >= unix_timestamp(subdate(now(), interval 1 hour)) and b.endepoch is null and a.callid <> '". $callhistory_rec['callid'] ."'";
				    $res = mysql_query($_qry);
				    if ($_DEBUG) syslog(LOG_DEBUG, $res . " - QUERY: $_qry - " . mysql_num_rows($res));

					if (!$res)
					{
						mysql_query("update liveusers set leadid ='0', status='ended', webstatus='free', waiting= NOW(), statustimestamp = '".time()."' where leadid ='".$leadid."'");
					}
				    else
				    {
				        if (mysql_num_rows($res) > 0)
				        {
				            mysql_query("update liveusers set status='conference', statustimestamp = '".time()."' where leadid = $leadid and userid = $userid");
				        }
				        else
				        {
				            mysql_query("update liveusers set status='oncall', statustimestamp = '".time()."' where leadid = $leadid and userid = $userid");
				        }
				    }
					break;
				
				case 'Hangup':
					$_qry = "UPDATE callhistory SET dialstatus= 'Hangup! " . $GLOBALS['matches'][$callhistory_rec['callid']][7] . "' WHERE callid = '". $callhistory_rec['callid'] ."'";
					$res = mysql_query($_qry);
					break;

				default:
					# code...
					break;
			}
			echo json_encode( $return_array );
			// echo json_encode( array("id" => 0, "msg" => $_qry) );
			exit;
		}
		else
		{
			echo json_encode( array( "id" => -1, "msg" => "userid: " . $leadid . " qry: " . $_qry . " callhistory_rec: " . json_encode($callhistory_rec) ) );
			$MAX_TRIES--;
			exit;
		}
	}
?>