<?php
	$projectnames = projects::projectnames($bcid);
    
	$s3 			= new S3($GLOBAL_S3_ACCESS_KEY[0], $GLOBAL_S3_ACCESS_KEY[1]);
	$s3bucket   	= "bcclientuploads";
	$dispoupdatelist= $bcid . "/dispositionupdateupload";
	$contents   	= $s3->getBucket($s3bucket, $dispoupdatelist);
	foreach ($contents as $content) {
		$splitname 			= explode("/", $content['name']);
		$projectid 			= $splitname[2];
		$dispoupdateid 		= $splitname[3];
		$filename 			= $splitname[4];
		$filenameext 		= $splitname[4];
		$getfilenameonly 	= explode(".", $filenameext);
		$filenamearray[] 	.= $getfilenameonly[0];
		
		$fileurl 			= "http://".$s3bucket.".s3.amazonaws.com/".$dispoupdatelist."/".$projectid."/".$dispoupdateid."/".$filename;
		$prefix     		= $bcid . "/dispositionupdateupload/" . $projectid . "/" . $dispoupdateid . "/" . $filename;

		$getprojectname 	= mysql_query("SELECT * from projects where projectid = $projectid;");
		$rowprojectname 	= mysql_fetch_assoc( $getprojectname );
		$getdispoupdate 	= mysql_query("SELECT * from disposition_update_history where id = $dispoupdateid and projectid = $projectid and bcid = $bcid order by id DESC");
		$rowdispoupdate 	= mysql_fetch_assoc( $getdispoupdate );
		if (!empty($rowdispoupdate)) {
			$rows[$rowdispoupdate['id']][1] = $rowprojectname["projectname"];
		    $rows[$rowdispoupdate['id']][2] = $rowdispoupdate['records_total'];
		    $rows[$rowdispoupdate['id']][3] = $rowdispoupdate['records_updated'];
		    $rows[$rowdispoupdate['id']][4] = date("Y-m-d H:i:s",$rowdispoupdate['date_epoch']);
		    $rows[$rowdispoupdate['id']][5] = '<a href="http://'.$s3bucket.'.s3.amazonaws.com/'.$prefix.'">Export</a> | <a href="#" onclick="removedispoupdate(\''.$dispoupdateid.'\')">Remove</a>';
		}
	}

    $cores = mysql_query("SELECT * from disposition_update_history where bcid = $bcid order by id DESC");
    while ( $row = mysql_fetch_assoc( $cores ) ) {
        $ex[$row['id']] = $row;
        $exlist[$row['id']] = "'".$row['id']."'";
    }

    foreach ($ex as $row) {
    	$uploadfilename = trim($row['filename']);
		$splituploadfilename = explode(".", $uploadfilename);
		if (in_array($splituploadfilename[0], $filenamearray)) {
			//do nothing
		} else {
	        $rows[$row['id']][1] = $projectnames[$row['projectid']];
	        $rows[$row['id']][2] = $row['records_total'];
	        $rows[$row['id']][3] = $row['records_updated'];
	        $rows[$row['id']][4] = date("Y-m-d H:i:s",$row['date_epoch']);
	        $rows[$row['id']][5] = '<a href="admin.php?act=export&type=dispoupdate&id='.$row['id'].'">Export</a> | <a href="#" onclick="removedispoupdate(\''.$row['id'].'\')">Remove</a>';
	    }
	}

    $headers = array('Campaign','Records Searched','Records Affected','Date','Action');
    echo '<div class="entryform" style="width:100%;">
	<a href="#" class="jbut" onclick="dialogwindow(\'listupdatefile\')">Upload New Disposition Update</a>                
	<div id="respmessage"></div><div>';
    echo tablegen($headers,$rows,'100%',NULL,'dataTables_wrapper');
    echo '</div>';
?>
