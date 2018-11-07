<?php
//error_reporting(0);
session_start();
$bcid = $_SESSION['bcid'];

include "../dbconnect.php";
include "phpfunctions.php";
require_once "../classes/domparser.php";
if (featurecheck($bcid,'email') == false)
	{
		echo "Feature not Supported.  Contact your system Administrator";
		exit;
	}
include_once('Mail.php');
include_once('Mail/mime.php');
require '../phpmailer/PHPMailerAutoload.php';
$act = $_REQUEST['act'];
$projectid 		= $_REQUEST['projectid'];

if ($act == 'sendemail')
	{
		$tid = $_REQUEST['tid'];
		$uid = $_REQUEST['uid'];
                $leadid = $_REQUEST['leadid'];
		$res = mysql_query("SELECT * from templates where templateid = '$tid'");
		$row = mysql_fetch_array($res);
                $sigs = getdatatable("signatures where sigid= ".$row['sigid'],"sigid");
                $sig = $sigs[$row['sigid']];
		$email_from = $_REQUEST['from'];
		$attachments = split(",",$row['attachments']);
		$email_to =$_REQUEST['to'];
                $recipients = $email_to;
		$email_subject = $_REQUEST['subject'];
		$email_message1 = $_REQUEST['message'];
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
		//$message = new Mail_mime();
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
 		//$message->setHTMLBody(rawurldecode($email_message));
                $message->isHTML(true);  
//                $message->SMTPDebug = $row['debug'];
                $message->SMTPDebug = 3;
                $message->Timeout=30;
                $message->Body = $email_message.'<p>Powered by BlueCloud.</p>';
 		foreach ($attachments as $attachment)
		{
		$message->addAttachment("../attachments/".$attachment);
		}
                foreach ($iatt as $cid=>$ipath)
                {
                    $parts = explode(".",$ipath);
                    foreach ($parts as $p)
                    {
                        $ext = $p;
                    }
                    
                    $temp = tempnam("tmp", "tmp");
                    $tn = md5($temp);
                    $tempf = "../attachments/".substr($tn,1,8).".".$ext;
                    file_put_contents($tempf, fopen("$ipath", 'r'));
                    $message->addEmbeddedImage($tempf, $cid);
                   
                }
		//$body = $message->get();
 		//$htmlemail = "text/html"; 
		if (strlen($row['replyto']) > 1)
			{
				$rp = $row['replyto'];
			}
		else $rp = $email_from;
		//$extraheaders = array("From"=>$email_from, "Subject"=>$email_subject,"Reply-To"=>$rp, "To"=>$email_to);
                $message->addAddress($email_to);
                $message->addReplyTo($rp);
                $message->Subject = $email_subject;
                $message->From = $email_from;
                $fromname = $row['emailfromname'];
                if (strlen($fromname) < 1)
                {
                    $parts = explode("@",$email_from);
                    $fromname = $parts[0];
                    
                }
                $message->FromName = $fromname;
		if (strlen($row['emailcc']) > 1)
			{
				//$extraheaders["Cc"] = $row['emailcc'];
                                //$recipients .= ",".$row['emailcc'];
                                $message->addCC($row['emailcc']);
                                
			}
                if (strlen($row['emailbcc']) > 1)
			{
				/*$extraheaders["Bcc"] = $row['emailbcc'];
                                $recipients .= ",".$row['emailbcc'];*/
                                $message->addBCC($row['emailbcc']);
			}
 		//$headers = $message->headers($extraheaders);
		//$a = $mail->send($recipients, $headers, $body);
               //var_dump($message->ErrorInfo);
		if ($message->send())
			{
				mysql_query("INSERT into email_log set userid = '$uid', mailto= '$email_to', timesent = '".time()."', projectid = '".$row['projectid']."', leadid = '$leadid', status = '$a', templateid = '$tid'");
                                echo "Email Sent";
                                
			}
		else {
			echo $message->ErrorInfo;
		}
		/*
		$headers = 'From: '.$email_from."\r\n".
		'Reply-To: '.$email_from."\r\n" .
		'X-Mailer: PHP/' . phpversion();
		@mail($email_to, $email_subject, $email_message, $headers);  */
                unlink($tempf);
		exit;
	}
if ($act == 'gettemplate') {
	
	$tempid 		= $_REQUEST['templateid'];
	$currentlead 	= $_REQUEST['leadid'];
	$cname 			= $_REQUEST['cname'];
	$cfname 		= $_REQUEST['cfname'];
	$emailadd 		= $_REQUEST['email'];
	$mailmerge 		= json_decode($_REQUEST['mailmerge'],true);
	
	$usercreds 		= getdatatable("members where userid = ".$_SESSION['uid'], "userid");
	$usercred 		= $usercreds[$_SESSION['uid']];
	$userdetails 	= getdatatable("memberdetails where userid = ".$_SESSION['uid'], "userid");
	$userdetail 	= $userdetails[$_SESSION['uid']];
	$leadres 		= mysql_query("SELECT * from leads_raw where leadid = '$currentlead'");
	$lead 			= mysql_fetch_assoc($leadres);
	
    foreach ($mailmerge as $mm) {
        $lead[$mm['name']] = $mm['value'];
    }
	
	$fields 		= array_keys($lead);
	$tres 			= mysql_query("SELECT * from templates where templateid = '$tempid'");
	$trow 			= mysql_fetch_array($tres);
    $sigs 			= getdatatable("signatures where sigid = '".$trow['sigid']."'",'sigid');
    $sig 			= $sigs[$trow['sigid']];
	$attachments 	= explode(",",$trow['attachments']);
	$b 				= $trow['template_body'];
    $b 				.= '<br /><div id="signature">'.$sig['signature_body'].'</div>';

	if ( $cname != '' && $cfname == '' ) {
		foreach ($fields as $f) {
			if ($f == 'cname') {
		      $b = str_replace("[name]",$lead[$f],$b);
		    }
			if ($f != 'cname') {
				$b = str_replace("[".$f."]",$lead[$f],$b);
			}
		}
	} else if ( $cname == '' && $cfname != '' ) {
		foreach ($fields as $f) {
			if ($f == 'cfname') {
		      $b = str_replace("[name]",$lead[$f],$b);
		    }
			if ($f != 'cfname') {
				$b = str_replace("[".$f."]",$lead[$f],$b);
			}
		}
	} else if ( $cname != '' && $cfname != '' ) {
		foreach ($fields as $f) {
			if ($f == 'cname') {
		      $b = str_replace("[name]",$lead[$f],$b);
		    }
			if ($f != 'cname') {
				$b = str_replace("[".$f."]",$lead[$f],$b);
			}
		}
	}
	
    foreach ($userdetail as $key=>$val) {
        $b = str_replace("[agent-$key]",$val,$b);
    }
	
    foreach ($usercred as $key=>$val) {
        $b = str_replace("[agent-$key]",$val,$b);
    }
    
	$templistres = mysql_query("SELECT * from templates where projectid = '".$trow['projectid']."'");
	while ( $lrow = mysql_fetch_array($templistres) ) {
		$tlist[$lrow['templateid']] = $lrow;
		$toptions .= '<option value="'.$lrow['templateid'].'">'.$lrow['template_name'].'</option>';
	}
	?>
	<script>
		$( "#upload" ).change(function(e) {
			var fileName = e.target.files[0].name;
            $("span#atts").append("<div class=\"attachments\">"+fileName+" | <a  href=\"#\" onclick=\"removeadditionalattachment(this)\">Remove</a></div>");
			$("#formattachment").trigger('submit');
		});
	</script>
    <table style="width:700px;font-size: 0.8em;" cellpadding="0" cellspacing="5" border="0">       
      <tr>
       <td colspan="1" class="title">
      Select Template:</td><td><select name="emailtemplate" class="box" onchange="changetemplate(this)"><option value="<?=$tempid;?>" selected="selected"><?=$tlist[$tempid]["template_name"];?></option><?=$toptions;?></select></td>
      </tr>
     <tr>
     	<td class="title">Email To:</td>
     	<?
 		if ( $emailadd != "" ) {
 			?>
 			<td align="left"><input type="email" name="emailto" id="emailto" class="box" value="<?=$emailadd;?>" /></td>
 			<?
 		} else {
 			?>
 			<td align="left"><input type="email" name="emailto" id="emailto" class="box" value="<?=$trow['emailto'];?>" /></td>
 			<?
 		}
     	?>
     </tr>
     <?php include("emailtemplate/emailer-include-cc.php") ?>
     <tr>
     	<td class="title">Email From:</td>
     	<td align="left"><input type="hidden" name="emailfrom" id="emailfrom" value="<?=$trow['emailfrom'];?>" />
            <input type="hidden" id="editable" name="editable" value="<?=$trow['editable'];?>" />
            <?=$trow['emailfrom'];?>
            </td>
     </tr>
     <tr>
     	<td class="title">Subject:</td>
     	<td align="left">
            <?php 
            if ($trow['editable'] == 1) {
                ?>
            <input type="text" name="subject" id="subject" class="box" value="<?=$trow['template_subject'];?>" />
            <?
			} else {
            ?>
            <input type="hidden" name="subject" id="subject" value="<?=$trow['template_subject'];?>" />
            <?=$trow['template_subject'];?>
            <?php 
            }
            ?>
        </td>
     </tr>
     <tr>
     	<?php include("emailtemplate/emailer-include.php") ?>
     	<td class="title">Attachments:</td>
     	<td align="left">
 		<div id="filenames">
		</div>
        <span id="atts">
        <?php
		$ct = 0;
		foreach ($attachments as $attachment) {
			if (strlen($attachment) > 0) {
			$ct++;
			?>
	        <div id="div_<?=$ct;?>" class="attachments"><a href="../attachments/<?=$attachment;?>"><?=$attachment;?></a> | <a  href="#" onclick="removeattachment('<?=$tempid;?>','<?=$attachment;?>','<?=$ct;?>')">Remove</a></div>
	        <?
			}
		}
		?>
        </span>
        <form enctype="multipart/form-data" method="POST" action="emailtemplate/emailtabuploader.php" target="uplo2" id="formattachment">
			<input type="hidden" name="templateid" value="<?=$tempid;?>" />
			<input type="hidden" name="act" value="attach" />
			<input type="hidden" name="bcid" value="<?=$bcid;?>" />
			<input type="hidden" name="projectid" value="<?=$projectid;?>" />
			<input type="hidden" name="leadid" value="<?=$currentlead;?>" />
			Attach File: <input id="upload" name="cfile" type="file" style="font-size:10px; height:20px; padding-bottom:8px; position:relative; left:25px"/>
		</form>
        <iframe name="uplo2" width="0" height="0" style="display:none"></iframe>
		</td>
     </tr>
     <tr>
     	<td class="title">Message:</td><td style="font-size: 10pt">(<?php echo $trow['editable'] == 1 ? "Editable":"Not Editable";?>)</td></tr>
     <tr>
     	<td align="left" colspan="2"><textarea name="emailbody" id="emailbody" class="box-1" value=""  style="width:700px; height:200px;"/><?=$b;?></textarea></td>
     </tr>
     <td colspan="2">
      <a href="#" id="sendemailbut" onclick="agent_sendemail('<?=$tempid;?>', '<?=$bcid;?>')">Send</a></td>
      </tr></table>
    <?
	exit;
}
if ($act == 'removeattachment') {
	$tid = $_REQUEST['templateid'];
	$attachment = $_REQUEST['attachment'];
	$tempres = mysql_query("SELECT * from templates where templateid = '$tid'");
	$template = mysql_fetch_assoc($tempres);
	$atts = explode(",",$template['attachments']);
	foreach ($atts as $att) {
		if ($att != $attachment) {
			$newatt[] = $att;
		}
	}
	$newatts = implode(",",$newatt);
	mysql_query("UPDATE templates set attachments = '".mysql_real_escape_string($newatts)."' where templateid = '$tid'");
	exit;
}