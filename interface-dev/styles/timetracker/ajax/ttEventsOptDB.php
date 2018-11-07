<?php
	include("../../../dbconnect.php");

	if(!isset($_REQUEST['config']))
		die("Config required.\n");

	if(!isset($_REQUEST['project_id']))
		die("Campaign required.\n");


	switch ($_REQUEST['act']) {
		case 'update':
			$_qry =	"
				INSERT INTO tteventsopt
				(project_id,user_id,config,value) 
				VALUES
				(
					".$_REQUEST['project_id']."
					,".$_REQUEST['user_id']."
					,'".$_REQUEST['config']."'
					,'".$_REQUEST['value']."'
				)
			";

			$ttrackeroptres = mysql_query($_qry);

			if (!$ttrackeroptres)
			{
				die(mysql_error() . " : " . $_qry);
			}

			header('Content-Type: application/json');
			echo json_encode(array("new_record" => mysql_insert_id()));
			break;
		
		case 'get':
			$_qry =	"
				SELECT value 
				FROM tteventsopt 
				WHERE project_id=".$_REQUEST['project_id']." 
					AND config='".$_REQUEST['config']."' 
				ORDER BY id DESC 
				LIMIT 1
			";

			$ttrackeroptres = mysql_query($_qry);

			if (!$ttrackeroptres)
			{
				die(mysql_error() . " : " . $_qry);
			}

			header('Content-Type: application/json');

			if (mysql_num_rows($ttrackeroptres))
			{
				$row = mysql_fetch_array($ttrackeroptres, MYSQL_NUM);

		    	echo json_encode( array($_REQUEST['config'] => $row[0]) );
			}
			else
			{
				switch ($_REQUEST['config']) {
					case 'isEnabled':
				    	echo json_encode( array('isEnabled' => 0) );
						break;
					
					case 'timeoutPause':
				    	echo json_encode( array('timeoutPause' => 60) );
						break;

					case 'timeoutIdle':
				    	echo json_encode( array('timeoutIdle' => 60) );
						break;

					case 'timeoutIdleMax':
				    	echo json_encode( array('timeoutIdleMax' => 600) );
						break;
				}
			}
			break;
	}

?>
