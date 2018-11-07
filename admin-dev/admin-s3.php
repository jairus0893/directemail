<?php
/**
 * Admin Folder is where all files and functions on the Admin interface are located
 * This handles all ajax requests and makes all database calls.
 * @copyright   Copyright (C) 2010-2011 - BlueCloud Australia 
 * @author      Aubrey Servito <obrifs@gmail.com>
 * @license     Proprietary
 * 
 */
session_start();
$securekey = 'Do not change this whatever happens!';
ini_set("display_errors","off");
error_reporting(E_ALL);
date_default_timezone_set($_SESSION['timezone']);
$isadmin = $_SESSION['usertype'];
if ($isadmin != 'user' && !checkrights('admin_portal'))
	{
		header("Location: ../login");
		exit;
	}
include "../dbconnect.php";
require_once '../classes/classes.php';
require "../classes/S3.php";
include "phpfunctions.php";
$bcid = $_SESSION['bcid'];
$act = $_REQUEST['act'];
$s3			= new S3($GLOBAL_S3_ACCESS_KEY[0], $GLOBAL_S3_ACCESS_KEY[1]);
$s3bucket   = "bcclientuploads";
require_once 'adminsubsystem.php';
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
if ($act == 'deletefiles3') {
	$projectid = $_REQUEST['projectid'];
	$attachment = $_REQUEST['filename'];
	$prefix		= $_REQUEST['prefix'];
	$filepath	= $prefix."/".$attachment;
	if ($s3->deleteObject($s3bucket, $filepath)) {
		mysql_query("delete from uploads where filename = '".$attachment."' and projectid = '".$projectid."' and (type like 'file' or type is null)");
		$message = "File deleted successfully.";
	} else {
		$message = "Deleting file failed.";
	}
	echo $message;
}
if ($act == 'removesigimages3') {
	$sigid 		= $_REQUEST['sigid'];
	$attachment = $_REQUEST['image'];
	$prefix		= $_REQUEST['prefix'];
	$filepath	= $prefix."/".$attachment;
	if ($s3->deleteObject($s3bucket, $filepath)) {
		$tempres = mysql_query("SELECT * from signatures where sigid = '$sigid'");
	    $sig = mysql_fetch_assoc($tempres);
	    $atts = explode(",",$sig['signature_images']);
	    foreach ($atts as $att) {
		    if ($att != $attachment) {
		    	$newatt[] = $att;
		    }
	    }
	    $newatts = implode(",",$newatt);
	    mysql_query("UPDATE signatures set signature_images = '".mysql_real_escape_string($newatts)."' where sigid = '$sigid'");
	    $message = "Attachment deleted successfully.";
	} else {
		$message = "Delete attachment failed.";
	}
	echo $message;
}
if ($act == 'removeexclusions3') {
    $eid 		= $_REQUEST['id'];
    $prefix		= $_REQUEST['prefix'];
	if ($s3->deleteObject($s3bucket, $prefix)) {
		mysql_query("delete from lists_exclusion where id = $eid");
	    mysql_query("delete from lists_exclusion_data where exclusionid = $eid");
	    $message = "List deleted successfully.";
	} else {
		$message = "Deleting list failed.";
	}
	echo $message;
}
if ($act == 'removedispoupdates3') {
    $id 		= $_REQUEST['id'];
    $prefix		= $_REQUEST['prefix'];
	if ($s3->deleteObject($s3bucket, $prefix)) {
		mysql_query("delete from disposition_update_history where id = '$id'");
	    $message = "List deleted successfully.";
	} else {
		$message = "Deleting list failed.";
	}
	echo $message;
}
if ($act == 'removedncs3') {
    $id 		= $_REQUEST['id'];
    $prefix		= $_REQUEST['prefix'];
	if ($s3->deleteObject($s3bucket, $prefix)) {
		mysql_query("delete from donotcall_list where id = '$id'");
		mysql_query("delete from donotcall where dncid = '$id'");
	    $message = "Do Not Call list deleted successfully.";
	} else {
		$message = "Deleting list failed.";
	}
	echo $message;
}
if ($act == 'setListDeleteds3') {
    $lid 		= $_REQUEST['lid'];
    $prefix		= $_REQUEST['prefix'];
	if ($s3->deleteObject($s3bucket, $prefix)) {
		mysql_query("Update lists SET is_deleted = 1, active = 0 WHERE lid = ".$lid);
	    $message = "List deleted successfully.";
	} else {
		$message = "Deleting list failed.";
	}
	echo $message;
}