<?php
	include_once("../../dbconnect.php");
	require_once("../../PHPAGI/phpagi-asmanager.php");
	include_once("startlog.php");

	header('Content-Type: application/json');

	$_qry = "SELECT * FROM liveusers WHERE userid = " . $_REQUEST['userid'];
	$liveusers_res = mysql_query($_qry);

	if (!$liveusers_res)
	{
		echo json_encode( array("callmanid" => 0, "msg" => "Error[liveusers_res]: " . mysql_error() . ". $_qry ") );
	}
	else
	{		
		if (mysql_num_rows($liveusers_res) > 0)
		{
			$liveusers_rec = mysql_fetch_assoc($liveusers_res);

			$confserver = $liveusers_rec['confserver'];

			if ($confserver == '10.134.114.17')
			{
				$confserver = '116.93.124.19';
			}

			$_qry = "SELECT * FROM callhistory WHERE leadid = " . $_REQUEST['leadid'] . " AND phone = '". $_REQUEST['phone'] ."' ORDER BY id DESC LIMIT 1";
			$callhistory_res = mysql_query($_qry);

			if (!callhistory_res)
			{
				echo json_encode( array("callmanid" => 0, "msg" => "Error[callhistory_res]: " . mysql_error() . ". $_qry ") );
			}
			else
			{
				if (mysql_num_rows($callhistory_res) > 0)
				{

					$callhistory_rec = mysql_fetch_assoc($callhistory_res);

					$confchannel = $callhistory_rec['confchan'];

					$asm = new AGI_AsteriskManager();
					if($asm->connect($confserver,'bcpami','cd84b1ade73162c123bca44bf398e6e9'))
					{
						$peer = $asm->command("hangup request $confchannel");

						// print_r($peer);

						$asm->disconnect();
					}

					switch ($liveusers_rec['status']) {
						case 'oncall':
						case 'incall':
							startlog('wrap');
							break;
						
						default:
							# code...
							break;
					}
					mysql_query("Update liveusers set statustimestamp = '".time()."' where userid ='".$_REQUEST['userid']."'");
					echo json_encode( array("callmanid" => $confchannel, "msg" => "Hangup: " . $_REQUEST['phone'] . " Agent Status: " . $liveusers_rec['status']) );

				}
				else
				{
					echo json_encode( array("callmanid" => 0, "msg" => "Error[mysql_num_rows()]: Call history not found.") );
				}
			}			
		}
		else
		{
			echo json_encode( array("callmanid" => 0, "msg" => "Error[mysql_num_rows()]: Agent not found.") );
		}
	}
?>