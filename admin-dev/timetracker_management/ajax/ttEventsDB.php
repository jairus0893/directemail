<?php
	include("./../../../dbconnect.php");

	switch ($_REQUEST['act']) {
		case 'newcustomevent':
			$_qry =	"
				INSERT INTO ttevents
				(bc_id,user_id,break,breakdesc) 
				VALUES
				(
					".$_REQUEST['bc_id']."
					,".$_REQUEST['user_id']."
					,'".$_REQUEST['break']."'
					,'".$_REQUEST['breakdesc']."'
				)
			";

			$tteventsres = mysql_query($_qry);

			if (!$tteventsres)
			{
				die("Error[ttevents]:" . mysql_error() . " : " . $_qry);
			}

			header('Content-Type: application/json');
			echo json_encode(array("new_record" => mysql_insert_id()));
			break;

		case 'deletecustomevent':
			$_qry =	"
				INSERT INTO tteventsactive
				(ttevents_id,user_id,isactive) 
				VALUES
				(
					".$_REQUEST['ttevents_id']."
					,".$_REQUEST['user_id']."
					,-1
				)
			";

			$tteventsactiveres = mysql_query($_qry);

			if (!$tteventsactiveres)
			{
				die("Error[tteventsactive]:" . mysql_error() . " : " . $_qry);
			}

			header('Content-Type: application/json');
			echo json_encode(array("new_record" => mysql_insert_id()));
			break;

		break;

		case 'away':
			$_qry =	"
				INSERT INTO tteventslog
				(project_id,user_id,ttevents_id,ts) 
				VALUES
				(
					".$_REQUEST['project_id']."
					,".$_REQUEST['user_id']."
					,999
					,from_unixtime(unix_timestamp(now())-".$_REQUEST['timeoutIdle'].")
				)
			";

			$tteventslogres = mysql_query($_qry);

			if (!$tteventslogres)
			{
				die("Error[tteventslog]:" . mysql_error() . " : " . $_qry);
			}

			$_insert_id = mysql_insert_id();

			$_qry =	"
				INSERT INTO tteventslog_end
				(tteventslog_id) 
				VALUES
				(
					$_insert_id
				)
			";

			$tteventslog_endres = mysql_query($_qry);

			if (!$tteventslog_endres)
			{
				die("Error[tteventslog_end]:" . mysql_error() . " : " . $_qry);
			}

			$_qry =	"
				INSERT INTO tteventslog
				(project_id,user_id,ttevents_id) 
				VALUES
				(
					".$_REQUEST['project_id']."
					,".$_REQUEST['user_id']."
					,999
				)
			";

			$tteventslogres = mysql_query($_qry);

			if (!$tteventslogres)
			{
				die("Error[tteventslog]:" . mysql_error() . " : " . $_qry);
			}

			header('Content-Type: application/json');
			echo json_encode(array("new_record" => mysql_insert_id()));
			break;

		case 'awayBack':
			$_qry =	"
				INSERT INTO tteventslog_end
				(tteventslog_id) 
				VALUES
				(
					".$_REQUEST['tteventslog_id']."
				)
			";

			$tteventslog_endres = mysql_query($_qry);

			if (!$tteventslog_endres)
			{
				die("Error[tteventslog_end]:" . mysql_error() . " : " . $_qry);
			}

			header('Content-Type: application/json');
			echo json_encode(array("new_record" => mysql_insert_id()));
			break;

		case '_requests':
			header('Content-Type: application/json');
			echo json_encode($_REQUEST);

			break;
		
	}

?>