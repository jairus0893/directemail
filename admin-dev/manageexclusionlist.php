<?php
$projectnames = projects::projectnames($bcid);

$cores = mysql_query("SELECT * FROM lists_exclusion WHERE bcid = $bcid ORDER BY date_created DESC");
while ( $row = mysql_fetch_assoc( $cores) ) {
    $ex[$row['id']] = $row;
    $exlist[$row['id']] = "'".$row['id']."'";
}

$excount = mysql_query("SELECT exclusionid, count(*) as excount from lists_exclusion_data where exclusionid in (".implode(",",$exlist).") group by exclusionid");
while ( $exrow = mysql_fetch_assoc( $excount ) ) {
    $ecount[$exrow['exclusionid']] = $exrow['excount'];
}

foreach ( $ex as $row ) {
	$uploadfilename = trim($row['exclusion_name']);
	if (in_array($uploadfilename, $filenamearray)) {
		//do nothing
	} else {
	    $rows[$row['id']][1] = $projectnames[$row['projectid']];
	    $rows[$row['id']][2] = $row['exclusion_name'];
	    $rows[$row['id']][3] = date("Y-m-d",$row['date_created']);
	    $rows[$row['id']][4] = $ecount[$row['id']];
	    $rows[$row['id']][5] = '<a href="admin.php?act=exclutionlistexport&id='.$row['id'].'">Export</a> | <a href="#" onclick="removeexclusion(\''.$row['id'].'\')">Remove</a>';
	}
}

$s3 			= new S3($GLOBAL_S3_ACCESS_KEY[0], $GLOBAL_S3_ACCESS_KEY[1]);
$s3bucket   	= "bcclientuploads";
$exclusionlist	= $bcid . "/exclusionlistupload";
$contents   	= $s3->getBucket($s3bucket, $exclusionlist);
foreach ($contents as $content) {
	$splitname 			= explode("/", $content['name']);
	$projectid 			= $splitname[2];
	$exclusionid 		= $splitname[3];
	$filename 			= $splitname[4];
	$filenameext 		= $splitname[4];
	$getfilenameonly 	= explode(".", $filenameext);
	$filenamearray[] 	.= $getfilenameonly[0];
	
	$fileurl 			= "http://".$s3bucket.".s3.amazonaws.com/".$exclusionlist."/".$projectid."/".$exclusionid."/".$filename;
	$prefix     		= $bcid . "/exclusionlistupload/" . $projectid . "/" . $exclusionid . "/" . $filename;
	
	$getprojectname 	= mysql_query("SELECT * from projects where projectid = $projectid;");
	$rowprojectname 	= mysql_fetch_assoc( $getprojectname );
	$getlistexclusion 	= mysql_query("SELECT * from lists_exclusion where id = $exclusionid and projectid = $projectid and bcid = $bcid;");
	$rowlistexclusion 	= mysql_fetch_assoc( $getlistexclusion );
	$getcount 			= mysql_query("SELECT count(*) as excount from lists_exclusion_data where exclusionid = '".$rowlistexclusion["id"]."'");
	while ( $getrowcount = mysql_fetch_assoc( $getcount ) ) {
	    $count = $getrowcount['excount'];
	}
	
    $rows[$rowlistexclusion['id']][1] = $rowprojectname["projectname"];
    $rows[$rowlistexclusion['id']][2] = $filename;
    $rows[$rowlistexclusion['id']][3] = date("Y-m-d",$rowlistexclusion['date_created']);
    $rows[$rowlistexclusion['id']][4] = $count;
    $rows[$rowlistexclusion['id']][5] = '<a href="'.$fileurl.'">Export</a> | <a href="#" onclick="removeexclusion(\''.$exclusionid.'\')">Remove</a>';
}

$headers = array('Campaign','Exclusion Name','Date Added','Records','Actions');
echo '<div class="entryform" style="width:100%;">
<a href="#" class="jbut" onclick="dialogwindow(\'newexclusionlist\')">Upload New Exclusion List</a>                
<div id="respmessage"></div><div>';
echo tablegen($headers,$rows,'100%',NULL,'dataTables_wrapper');
echo '</div>';
?>
