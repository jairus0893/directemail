<?php

require "../../../classes/S3.php";
include_once "../../../dbconnect.php";

$bcid = $_SESSION['bcid'];
$act = $_REQUEST['act'];
$s3			= new S3($GLOBAL_S3_ACCESS_KEY[0], $GLOBAL_S3_ACCESS_KEY[1]);
$s3bucket   = "bcclientuploads";


if ($act == 'removeattachments3') {
	$tid 		= $_REQUEST['templateid'];
	$attachment = $_REQUEST['attachment'];
	$prefix		= $_REQUEST['prefix'];
	$filepath	= $prefix."/".$attachment;
	if ($s3->deleteObject($s3bucket, $filepath)) {
		$tempres 	= mysql_query("SELECT * from templates where templateid = '$tid'");
		$template 	= mysql_fetch_assoc($tempres);
		$atts 		= explode(",",$template['attachments']);
		foreach ($atts as $att) {
			if ($att != $attachment)
				{
					$newatt[] = $att;
				}
		}
		$newatts = implode(",",$newatt);
		mysql_query("UPDATE templates set attachments = '".mysql_real_escape_string($newatts)."' where templateid = '$tid'");
		$message = "Attachment deleted successfully.";
	} else {
		$message = "Delete attachment failed.";
	}
	echo $message;
}