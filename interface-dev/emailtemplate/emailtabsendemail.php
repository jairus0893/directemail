<?php
/*
  sendemail.php

  EXPECTS:
    act     - switch value 
    tid     - template id
    uid     - user id
    leadid  - lead id
    to      - recipient
*/
include "../../dbconnect.php";

session_start();

phplog("_SESSION: " . print_r($_SESSION, true));

$bcid = $_SESSION['bcid'];

include "../phpfunctions.php";
require_once "../../classes-dev/domparser.php";
require_once '../../classes-dev/classes.php';
if (featurecheck($bcid,'email') == false)
{
	echo "Feature not Supported.  Contact your system Administrator";
	exit;
}
include_once('Mail.php');
include_once('Mail/mime.php');
require '../../phpmailer/PHPMailerAutoload.php';

$act = $_REQUEST['act'];

if ($act == 'sendemail')
{		
    phplog("_REQUEST: " . print_r($_REQUEST, true));
	
	$bcid 		= $_REQUEST['bcid'];
	$projectid 	= $_REQUEST['projectid'];
	$tid 		= $_REQUEST['tid'];
	$uid 		= $_REQUEST['uid'];
    $leadid 	= $_REQUEST['leadid'];
    $email_to 	= $_REQUEST['to'];
	$email_cc 	= $_REQUEST['cc'];
	$subject 	= $_REQUEST['subject'];
	$message 	= $_REQUEST['message'];
	$attachment = $_REQUEST['attachment'];
	
	$res = mysql_query("SELECT * FROM templates WHERE templateid = '$tid'");
	$row = mysql_fetch_array($res);

    phplog("TEMPLATE ROW: " . print_r($row, true));
    
    $sigs = getdatatable("signatures where sigid= ".$row['sigid'],"sigid");
    $sig = $sigs[$row['sigid']];

	$email_from = $row['emailfrom'];
	$attachmentremove = str_replace(" | Remove", "", $attachment);
	$templateattachments = explode(',', str_replace(" | Remove", "", $attachment));
	$additionalattachments = split(",",$attachmentremove);
	$email_subject = $subject;
	$email_message1 = $message;

    $em = str_get_html(rawurldecode($email_message1));
    $ict = 0;

    foreach ($em->find("div#signature img") as $img)
    {
      $ipath = $img->src;
      $cid = "img-".$ict;
      $em->find("div#signature img",$ict)->src = "cid:$cid";
      $iatt[$cid] = $ipath;
      $ict++;
    }
	
    $email_message = (string)$em;
    $message = new PHPMailer();
    $message->isSMTP();
    $message->SMTPAuth = true;
    $message->Host = $row['mailserver'];
	$message->Port = $row['mailport'];

    if ($row['mailencryption'] != 'none')
    {
      $message->SMTPSecure = $row['mailencryption'];
    }
	
    $message->Username = $row['mailuser'];
	$message->Password = $row['mailpass'];
    $message->isHTML(true);  
    $message->SMTPDebug = $row['debug'];
    $message->Timeout=30;
    $message->Body = $email_message.'<p>Powered by BlueCloud.</p>';

	foreach ($templateattachments as $templateattachment)
	{
		if( file_exists("../../attachments/".$templateattachment) ) {
			$message->AddAttachment("../../attachments/".$templateattachment);
		}
	}
	foreach ($additionalattachments as $additionalattachment)
	{
		$filename = $leadid . "_" .$additionalattachment;
		if( file_exists("../../upload/attachment/". $bcid ."/". $projectid ."/".$filename) ) {
			$message->AddAttachment("../../upload/attachment/". $bcid ."/". $projectid ."/".$filename);
		}
	}
    foreach ($iatt as $cid=>$ipath) {
      $parts = explode(".",$ipath);
      foreach ($parts as $p) {
          $ext = $p;
      }
      
      $temp = tempnam("tmp", "tmp");
      $tn = md5($temp);
      $tempf = "../../attachments/".substr($tn,1,8).".".$ext;
      file_put_contents($tempf, fopen("$ipath", 'r'));
      $message->addEmbeddedImage($tempf, $cid);
    }
	
	if (strlen($row['replyto']) > 1) {
		$rp = $row['replyto'];
	} else {
		$rp = $email_from;
    }
      
	$message->addAddress($email_to);
    $message->addReplyTo($rp);
    $message->Subject = $email_subject;
    $message->From = $email_from;
    $fromname = $row['emailfromname'];
    if (strlen($fromname) < 1) {
        $parts = explode("@",$email_from);
        $fromname = $parts[0];
    }

    $message->FromName = $fromname;
	
	$email_cc_addadress = split(",",$email_cc);
	foreach ($email_cc_addadress as $ccaddress) {
		$trimccaddress = trim($ccaddress);
	    $message->addCC($trimccaddress);
	}

    if (strlen($row['emailbcc']) > 1) {
      $message->addBCC($row['emailbcc']);
	}
	
    phplog("Sending email ... ");

	if ($message->send()) {
		// Add Notes
		$u = new members($uid);
	    $record = new records($leadid);
	    $prevnote = $record->notes();
	    $newnote = "Email Sent";
	    $jnote = json_decode($prevnote,true);
	    if (!$jnote) $jnote = array();
	    array_push($jnote,array(
	        "user"=>$u->userlogin,
	        "timestamp"=>time(),
	        "message"=>$newnote
	    ));
	    $n = $record->notes(json_encode($jnote));
		
  		$_status = "Sent";
		
		phplog("SUCCESS!");
  		echo "Email Sent";
	} else  {
		// Add Notes
		$u = new members($uid);
	    $record = new records($leadid);
	    $prevnote = $record->notes();
	    $newnote = "Email Failed: " . $message->ErrorInfo;
	    $jnote = json_decode($prevnote,true);
	    if (!$jnote) $jnote = array();
	    array_push($jnote,array(
	        "user"=>$u->userlogin,
	        "timestamp"=>time(),
	        "message"=>$newnote
	    ));
	    $n = $record->notes(json_encode($jnote));
		
    	$_status = $message->ErrorInfo;

    	phplog("FAILED!");
		echo "Email Failed: " . $message->ErrorInfo;
	}
	//Email Log
	phplog_on();
    $_qry = "INSERT into email_log set userid = '$uid', mailto= '$email_to', message = '$email_message', timesent = unix_timestamp(), projectid = '".$projectid."', leadid = '$leadid', status = '$_status', attachment = '$attachmentremove', templateid = '$tid'";
    phplog("QUERY: " . $_qry);
    mysql_query($_qry);
    phplog("NEW EMAIL_LOG RECORD: " . mysql_insert_id());
	
	unlink($tempf);
}