<?php
ini_set('display_errors','on');
error_reporting(E_ALL);
include "../../dbconnect.php";
include "../phpfunctions.php";
$tpw = 'niner123';
$bcid = $_REQUEST['bcid'];
extract($_REQUEST);
$t = $merchant."|".$tpw."|".$refid."|".$amount."|".$timestamp."|".$summarycode;
$sysig = sha1($t);
if ($sysig == $fingerprint)
	{		//check if exists on database 
                ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script type="text/javascript" src="../../jquery/js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="../../jquery/js/jquery-ui-1.8.12.custom.min.js"></script>
<link href="../../jquery/css/redmond/jquery-ui-1.8.12.custom.css" rel="stylesheet" type="text/css" />
<link href="cstyle.css" rel="stylesheet" type="text/css" />
</head><body>
        <table style="width:1200px; margin-top:200px">
        <tr><td width="1200" align="center" colspan="2"><img src="../images/bclogo-small.png"><br><br><br></td><td></td></tr>
                                <tr><td width="1200" align="center" colspan="2">Thank you for using BlueCloud<br><br><br><a href="#" onclick="window.close()">Close</a></td><td></td></tr>                        
        </table>
                <?php
		$res = mysql_query("SELECT * from bc_transactions where referencenumber = '$refid'");
		$r = mysql_num_rows($res);
		if ($r > 0)
			{
				
			}
		else {
				$e = time();
				$amt = $amount / 100;
				mysql_query("Insert into bc_transactions set epoch = '$e', referencenumber = '$refid', amount = '$amt', date = NOW(), transactiontype='payment', paymentmode = 'cc', bcid= '$bcid', comments = 'Through Secure Pay Portal', signature = '$sysig'");
				$c = mysql_query("SELECT * from bc_wallet where bcid = '$bcid'");
				$exists = mysql_num_rows($c);
				if ($exists > 0)
					{
						mysql_query("Update bc_wallet set loadedcredits = loadedcredits + $amt where bcid = '$bcid'");
					}
				else {
					mysql_query("Insert into bc_wallet set bcid = '$bcid', loadedcredits = $amt");
					}
				echo "payment accepted";
				
			}
	}
	
else 
{
    echo "error! Sig mismatch!";
    var_dump($_REQUEST);
}
?>