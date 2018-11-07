<?php
include_once "../../dbconnect.php";
$securekey = 'Do not change this whatever happens!';
$act = $_REQUEST['act'];
$bcid = $_REQUEST['bcid'];
$projectid = $_REQUEST['projectid'];
$leadid = $_REQUEST['leadid'];
$myfile = basename( $_FILES['cfile']['name']);
$filehash = md5($myfile.$securekey);	
$target_path = "../../upload/attachment/". $bcid ."/". $projectid ."/"; 
$desc = $leadid . "_" .$myfile;

if (!empty($leadid)) {
	// Create directory if it does not exist
	if(!is_dir($target_path)) {
	    mkdir($target_path, 0777, true);
	}
	
	if( move_uploaded_file($_FILES['cfile']['tmp_name'], $target_path."/".$desc) ) {
		mysql_query("INSERT into uploads set filename = '$myfile', projectid = '$projectid', description = '$desc', uploaddate = NOW()");
		echo "<script>alert('Upload Complete.');</script>";
		exit;
	} else {
	    echo "<script>alert('Upload Failed.');</script>";
		exit;
	}
} else {
	echo "<script>alert('No Lead. Remove the file and try again.');</script>";
	exit;
}
?>