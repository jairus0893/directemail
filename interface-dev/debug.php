<?php
include "../dbconnect.php";
$email = 'obrifs@gmail.com';
$dsendres = mysql_query("SELECT * from templates where disposend = 'appointment' and projectid = '261'");
	while ($row = mysql_fetch_assoc($dsendres))
		{
			include_once('Mail.php');
			include_once('Mail/mime.php');
			$email_from = $row['emailfrom'];
		$attachments = split(",",$row['attachments']);
		$email_to =$email;
		$email_subject = $row['template_subject'];
		$email_message = $row['template_body'];
		$message = new Mail_mime();
 		$message->setHTMLBody(rawurldecode($email_message));
 		foreach ($attachments as $attachment)
		{
		$message->addAttachment("../attachments/".$attachment);
		}
		$body = $message->get();
 		$htmlemail = "text/html"; 
		$extraheaders = array("From"=>$email_from, "Subject"=>$email_subject,"Reply-To"=>$email_from);
 		$headers = $message->headers($extraheaders);
		$mailtype = $row['mailtype'];
		if ($mailtype == 'smtp')
			{
				$params["host"] = $row['mailserver'];
				$params["port"] = $row['mailport'];
				$params["auth"] =true;
				$params["username"] = $row['mailuser'];
				$params["password"] = $row['mailpass'];
				$mail = Mail::factory("smtp",$params);	
			}
		else {
 		$mail = Mail::factory("mail");
		}
 		$a = $mail->send($email_to, $headers, $body);
		if ($a)
			{
				mysql_query("INSERT into email_log set userid = '$uid', sent = '$email_to', message = '$email_message', timesent = NOW(), projectid = '".$row['projectid']."', leadid = '$leadid', status = '$a'");
			}
		/*
		$headers = 'From: '.$email_from."\r\n".
		'Reply-To: '.$email_from."\r\n" .
		'X-Mailer: PHP/' . phpversion();
		@mail($email_to, $email_subject, $email_message, $headers);  */
		//echo "Email Sent";
		}