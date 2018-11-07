<?php
	include("./../../dbconnect.php");

	$act = $_REQUEST['act'];

	if ($act == "ttstartbreak")
	{
		$qry = sprintf("
			INSERT INTO tteventslog (project_id, user_id, ttevents_id) 
			VALUES
			(%s,%s,%s)"
			, $_REQUEST['project_id'], $_REQUEST['user_id'],$_REQUEST['ttevents_id']
		);
		// echo $qry;

	    
	    $tteventslogres = mysql_query($qry);
	    if ($tteventslogres)
	    	echo mysql_insert_id();
	   	else
	   		echo mysql_error();
	   	
	    exit;
	}

	if ($act == "ttendbreak")
	{
		$qry = sprintf("
			INSERT INTO tteventslog_end (tteventslog_id)
			VALUES
			(%s)"
			, $_REQUEST['tteventslog_id']
		);
		// echo $qry;
		
	    $tteventslogres = mysql_query("INSERT INTO tteventslog_end (tteventslog_id) VALUES (".$_REQUEST['tteventslog_id'].")");
	    if ($tteventslogres)
	    	echo mysql_insert_id();
	   	else
	   		echo mysql_error();
	   	
	    exit;
	}

	if ($act == 'tteventsloggetid')
	{
	    $tteventslogres = mysql_query(
	    	"
			SELECT 
				concat(alast, ', ', afirst) as agent_name, 
				c.projectname, 
				d.break,
				unix_timestamp(now())-unix_timestamp(a.ts) as ts_offset
			FROM tteventslog a 
				CROSS JOIN memberdetails b ON a.user_id = b.userid
				CROSS JOIN projects c ON a.project_id = c.projectid 
				CROSS JOIN ttevents d ON a.ttevents_id = d.id
			WHERE a.id=".$_REQUEST['tteventslog_id']
		);

	    if ($tteventslogres)
	    {
	    	$row = mysql_fetch_assoc($tteventslogres);
	    	header('Content-Type: application/json');
	    	echo json_encode($row);
	    }
	   	else
	   		echo mysql_error();

	}
	// echo "project_id: ". $_REQUEST['project_id']." user_id: ".$_REQUEST['user_id']." ttevents_id: ".$_REQUEST['ttevents_id'];
?>