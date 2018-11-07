<?php
if ($act == 'static')
{
    $staticid = $_REQUEST['staticid'];
    $r = getdatatable("bc_static where staticid = '$staticid'", "staticid");
    $static = $r[$staticid];
    ?>
<div><h3><?php echo $static['title']; ?></h3></div>
    <?php echo $static['content']; ?>
<?
    exit;
}
if ($act == 'addnewadmin')
	{
		extract($_POST);
		mysql_query("insert into members set userlogin = '$userlogin', userpass='$userpass', email = '$email', roleid = '$role', bcid = '$bcid', usertype = 'admin'");
		$act ='admins';
	}
if ($act == 'processpayment')
	{
	/*/
$tpw = 'f4cppec2';

extract($_REQUEST);
$t = $merchant."|".$tpw."|".$refid."|".$amount."|".$timestamp."|".$summarycode;
$sysig = sha1($t);
if ($sysig == $fingerprint)
	{		//check if exists on database 
		$res = mysql_query("SELECT * from bc_transactions where referencenumber = '$refid' and signature = '$sysig'");
		$r = mysql_num_rows($res);
		if ($r > 0)
			{
				//transaction already recorded.
			}
		else {
				$e = time();
				$amt = $amount / 100;
				mysql_query("Insert into bc_transactions set epoch = '$e', referencenumber = '$refid', amount = '$amt', date = NOW(), transactiontype='payment', paymentmode = 'cc', bcid= '$bcid', comments = 'Through Secure Pay Portal'");
				$c = mysql_query("SELECT * from bc_wallet where bcid = '$bcid'");
				$exists = mysql_num_rows($c);
				if ($exists > 0)
					{
						mysql_query("Update bc_wallet set loadedcredits = loadedcredits + $amt where bcid = '$bcid'");
					}
				else {
					mysql_query("Insert into bc_wallet set bcid = '$bcid', loadedcredits = $amt");
					}
				*/?>
                <body>
<div id="container">
<div id="header">
<img src="../images/bclogo-small.png" />
<div id="reporttitle">Payment Process</div>
</div>
<hr />
<div id="results">
<center><h3>Thank you for purchasing BlueCloud Credits!</h3></center>
</div>
<script>
setTimeout("closewin()",5000);
function closewin()
{
	parent.finish();

}
</script>
                <?
				exit;
		//	}
	//}
//else echo "error! Sig mismatch!";
}
if ($act == 'addcredits')
	{
		date_default_timezone_set('UTC');
		$ts = date("YmdHis");
		/*$tpw = 'niner123';*/
		$spurl = "https://payment.securepay.com.au/live/v2/invoice";
		                
                /*********** For testing *****************/
                $tpw = 'niner123';
                //$spurl = "https://payment.securepay.com.au/test/v2/invoice";
		if ($_GET['bill_name'] == 'transact')
			{
				extract($_GET);
				$rcode = sha1(time());
				$rcode = crc32($rcode);
				$rcode = sha1($rcode);
				$rcode = crc32($rcode);
				$ref = strtoupper($bc['company']."-".$rcode."-".time());
				$st = array();
				$st['merchant_id'] = "XBC0035";
				$st['tpw'] = $tpw;
				$st['txn_type'] = "0";
				$st['primary_ref'] = $ref;
				$st['amount'] = $amount;
				$st['fp_timestamp'] = $ts;
				$her = implode("|",$st);
				$ur = array();
				$sform = array();
				foreach ($st as $key=>$value)
					{
						if ($key != 'tpw')
							{
								$ur[] = $key."=".$value;
                                                                $sform[] = '<input type="hidden" name="'.$key.'" value="'.$value.'" />'; 
							}
					}
				$him = implode("&",$ur);
				$finger = sha1($her);
				$serv = "https://".$_SERVER['SERVER_NAME']."/admin/accounts/processpayment.php?bcid=".$bcid;
				$logo = "https://".$_SERVER['SERVER_NAME']."/admin/images/bclogo-small.png";
				$title = "Purchasing BlueCloud Credits";
				$ctypes = "VISA|MASTERCARD|AMEX|DINERS|JCB";
				//header("Location: ".$spurl."?".$him."&bill_name=".$bill_name."&fingerprint=".$finger."&return_url=".urlencode($serv)."&page_header_image=".urlencode($logo)."&title=".urlencode($title));
				?>

                
                <html>
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title></title>
                </head>
                <body onload="document.sform.submit()">
                    <form name="sform" action="<?php echo $spurl;?>" method="POST" target="spay">
                    <?php
                    foreach ($sform as $f)
                    {
                        echo $f;
                    }
                    
                    ?>
                    <input type="hidden" name="bill_name" value="<?php echo $bill_name;?>" />
                    <input type="hidden" name="fingerprint" value="<?php echo $finger;?>" />
                    <input type="hidden" name="return_url" value="<?php echo $serv;?>" />
                    <input type="hidden" name="return_url_target" value="parent" />
                    <input type="hidden" name="callback_url" value="<?php echo $serv;?>" />
                    <input type="hidden" name="page_header_image" value="<?php echo $logo;?>" />
                    <input type="hidden" name="card_types" value="<?php echo $ctypes;?>" />
                    <input type="hidden" name="title" value="<?php echo $title;?>" />
                    </form>
                    <iframe src="<?php //echo $spurl."?".$him."&bill_name=".$bill_name."&fingerprint=".$finger . "&return_url=".urlencode($serv)."&callback_url=".urlencode($serv)."&page_header_image=".urlencode($logo)."&card_types=".$ctypes."&title=".urlencode($title);?>" name="spay" width="100%" height="100%" frameborder="0"></iframe>
                <script>
                function finish()
				{
					window.opener.reloadparent();
					window.close();
				}
                </script>
                </body></html>		<?
				
				exit;
			}
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
        <form method="post" action="account.php?act=addcredits" >

        <input type="hidden" id="bn" name="bill_name" value="transact" />

        <tr><td width="1200" align="center" colspan="2"><img src="../images/bclogo-small.png"><br><br><br></td><td></td></tr>
        <tr><td width="600" align="right">Number of Credits</td><td width="600" align="left">
                <?php /* <select name="amount" id="amount">
        <option value="1000">10cr (AUD $10.00)</option>
        <option value="10000">100cr (AUD $100.00)</option>
        <option value="50000">500cr (AUD $500.00)</option>
        <option value="100000">1000cr (AUD $1000.00)</option>
        </select>*/ 
                ?>
            <input type="text" name="amount" id="amount">
            </td>
        <tr><td></td><td colspan=""><a href="#" onClick="addcreditwin()">Continue</a></tr>
        </tr>
        </form>
        </table>
</body>
    <script>
    function addcreditwin()
	{
		var bn = $("#bn").val();
		var amt = $("#amount").val() * 100;
                
		window.location = "account.php?act=addcredits&bill_name="+bn+"&amount="+amt;
	}
    </script>
        <?
		exit;
	}
if ($act == 'security')
	{
		?>
        <div class="entry-content">
		<p class="tableheader">Security Policy</p>
        <p>When purchasing Credits from BlueCloud Australia Pty Payment Portal, your financial details are passed 
through a secure server using the latest 128-bit SSL (secure sockets layer) encryption technology.128-bit SSL encryption is approximated to take at least one trillion years to break, and is the industry standard. </p><p>If you have any questions regarding our security policy, please contact our customer support centre at <a href="mailto:support@bluecloudaustralia.com.au">Support@BlueCloudAustralia.com.au</a></p>
</div>
        <?
		exit;
	}
if ($act == 'privacy')
	{
		?>
        <style>
		
		</style>
        <div class="entry-content">
<p class="tableheader">Privacy Statement</p>
<p>This document outlines BlueCloud Australia Pty Ltd's policy in relations to the handling of personal and sensitive information according to the Privacy Amendment (Private Sector) Act 2000 and the NPP (National Privacy Principles) which came into effect on the 21st December 2001.</p>
<p>1.&nbsp;&nbsp; &nbsp;Our Commitment</p>
<p>BlueCloud Australia Pty Ltd provides a range of professional telemarketing services for businesses Australia wide and is in constant interaction with both consumers and business contacts. BlueCloud Australia Pty Ltd is a highly ethical organisation in its field and believes in the privacy of an individual. To protect this right, BlueCloud Australia Pty Ltd Services is committed to ensure that all of its Managers and Employees fully comply with the NPP regulations. Our staff is provided with ongoing training to increase their skills in dealing with the public, proper collection of information and general work ethics.</p>
<p>2.&nbsp;&nbsp; &nbsp;Data collection</p>
<div>
  <p dir="ltr">We do not keep any record of personally identifiable information including IP addresses from which users access our site except where you have specifically provided us with information about yourself, in which case we also record your IP address for security purposes. An example of this would be when proceeding to a checkout to finalise an order you may wish to make. After completing the form provided, your IP address will be stored along with a transaction number.</p>
</div>

<p>3.&nbsp;&nbsp; &nbsp;Your rights</p>
<p>The information is often updated with the full consent of consumer or the business contact and will not be disclosed to any unrelated third party, under any circumstances, without the third party's permission. BlueCloud Australia Pty Ltd will ensure deletion of any individual's details from its database if requested by the latter. The Privacy Amendment (Private Sector) Act 2000 reserves you the right to access your records held in our database. Please contact us if you wish to do so. We have appointed a Privacy Officer to specially take care of complaints or any other matters related to your privacy. Please find contact details below.</p>
<p>Privacy Officer - BlueCloud Australia Pty Ltd</p>
<p><a href="mailto:privacy@bluecloudaustralia.com.au">privacy@bluecloudaustralia.com.au</a><br>
Ph. + 61 1800 234 350<br>
Fx. + 61 2 9211 6655<br>
Address: 68,&nbsp; 89 Jones St<br>
Ultimo NSW 2007 Australia</p>
</div>
        <?
		exit;
	}
if ($act == 'company')
	{
		echo "
		<p class=\"tableheader\">Company Information</p>
		<p style=\"\">BlueCloud Australia Pty. Ltd.<br>
		ABN: 58 152 768 904
		<br>
		68, 89 Jones St, Ultimo, NSW 2006, Australia
		<br><br>
		Tel: +61 1 800 234 350
		<br>
		Fax: +61 2 9211 6655
		<br><br>
		<a href=\"http://www.bluecloudaustralia.com.au/\">www.BlueCloudAustralia.com.au</a><br>
		<a href=\"mailto:enquiries@BlueCloudAustralia.com.au\">Enquiries@BlueCloudAustralia.com.au</a></p>
		";
		exit;
	}
if ($act == 'refund')
	{
		echo "<p class=\"tableheader\">Refund Policy</p>
		<p style=\"\">You may request for refund of unused credits anytime.</p>
		 <p style=\"\">The amount refunded will be equivalent monetary value to your remaining unused credits minus all the transaction costs incurred.</p>
		 <p style=\"\">Transaction costs may include but no limited to: bank fees, paypal fees, wire transfer fees. <br></p>
		 <p style=\"\">You may submit a request for refund to <a href=\"mailto:accounts@bluecloudaustralia.com.au\"> Accounts@BlueCloudAustralia.com.au</a>, we will attend to your request within seven (7) working days.</p>
		 ";
		exit;
	}
if ($act == 'support')
	{
		echo "
		<p class=\"tableheader\">Refund Policy</p>
		We are currently developing a dedicated support system that will ensure timely resolution of issues and questions.</p> <p> For now email us at <a href=\"mailto:support@bluecloudaustralia.com.au\">Support@BlueCloudAustralia.com.au</a> for any technical concerns and <a href=\"mailto:accounts@bluecloudaustralia.com.au\">Accounts@BlueCloudAustralia.com.au</a> for billing concerns.";
		exit;
	}
if ($act == 'changerole')
	{
		$newrole = $_REQUEST['newrole'];
		$id = $_REQUEST['id'];
		mysql_query("update members set roleid = '$newrole' where userid = '$id'");
		exit;
	}
if ($act == 'changepassword')
	{
		$newpass = $_REQUEST['newpass'];
		$id = $_REQUEST['id'];
		mysql_query("update members set userpass = '$newpass' where userid = '$id'");
		exit;
	}
if ($act == 'newadmin')
	{

		?>
        <table width="700">
        <tr><td colspan="2" class="center-title heading"><b><i>Create New Admin</i></b></td></tr>
        <tr><td class="datas"><b>Userlogin:</b></td><td><input type="text" id="newuserlogin" name="userlogin" /></td>
         <tr><td class="datas"><b>Password:</b></td><td><input type="text" id="newuserpass" name="userpass" /></td>
          <tr><td class="datas"><b>Email:</b></td><td><input type="text" id="newemail" name="email" /></td>
          <tr><td class="datas"><b>Role:</b></td><td><select id="newrole" name="role">
          <option value="1">Administrator</option>
          </select></td>
         <tr><td></td><td><a href="#" onClick="addnewadmin()">Add</a></td></tr>
        </table> 
        <?
		exit;
	}
if ($act == 'admins')
	{
	$roles = getroles($bcid);
	$adres = mysql_query("SELECT * from members where usertype = 'admin' and super = '0' and bcid = '$bcid'");
	$admin = '';
	$headers[] = 'Userlogin';
	$headers[] = 'Role';
	$headers[] = 'Password';
	$headers[] = 'Status';
	while ($ad = mysql_fetch_array($adres))
		{
			if ($ad['active'] == '0') 
				{
					$color = "red";
					$activ = '<a href="#" onclick=activate(\''.$ad['userid'].'\')>activate<a>';
				}
			else 
				{
					$color = "yellowgreen";
					$activ = '<a href="#" onclick=deactivate(\''.$ad['userid'].'\')>deactivate<a>';
				}
			$rows[$ad['userid']][1]= '<font color="'.$color.'">'.$ad['userlogin'].'</font>';
			$rows[$ad['userid']][2]= '<div id="role'.$ad['userid'].'"><a onclick="changerole(\''.$ad['userid'].'\',\''.$roles['array'][$ad['roleid']]['rolename'].'\')">'.$roles['array'][$ad['roleid']]['rolename'].'</a></div>';
			$rows[$ad['userid']][3]= '<div id="pass'.$ad['userid'].'"><a onclick="changepass(\'pass'.$ad['userid'].'\',\''.$ad['userpass'].'\',\''.$ad['userid'].'\')">'.$ad['userpass'].'</a></div>';
			$rows[$ad['userid']][4]=$activ;
		}
	echo '<a href="#" onclick="linker(\'newadmin\')">New Admin User</a>';
	 echo tablegen($headers,$rows);
	exit;		
	}
if ($act == 'transactions')
	{
		$res = mysql_query("SELECT * from bc_transactions where bcid = '$bcid' order by epoch DESC");
		$headers[] = 'ID';
		$headers[] = 'Date';
		$headers[] = 'Type';
		$headers[] = 'Mode';
		$headers[] = 'Reference';
		$headers[] = 'Amount (AUD)';
		$headers[] = 'Comments';
		while ($row = mysql_fetch_assoc($res))
			{
				$rows[$row['transactionid']][1] = $row['transactionid'];
				$rows[$row['transactionid']][2] = date("r",$row['epoch']);
				$rows[$row['transactionid']][3] = ucfirst($row['transactiontype']);
				$rows[$row['transactionid']][4] = ucfirst($row['paymentmode']);
				$rows[$row['transactionid']][5] = $row['referencenumber'];
				$rows[$row['transactionid']][6] = "AUD $".number_format($row['amount'],2);
				$rows[$row['transactionid']][7] = $row['comments'];
			}
		echo '<div class="tabbed">
		<ul>
		<li><a href="#transactions">Payment History</a></li>
		<li><a href="#purchases">Purchase History</a></li>
		
		</ul>
		<div id="transactions">
		';
		echo tablegen($headers, $rows,"736");
		unset($headers,$rows);
		echo '</div>
		<div id="purchases">
		';
		$res = mysql_query("SELECT * from bc_features_details");
		while ($row = mysql_fetch_assoc($res))
			{
				$feature[$row['feature']] = $row;
			}
		$res = mysql_query("SELECT * from bc_purchases where bcid = '$bcid'");
		$headers[] = 'ID';
		$headers[] = 'Date';
		$headers[] = 'Feature';
		$headers[] = 'Cost (AUD)';
		while ($row = mysql_fetch_assoc($res))
			{
				$rows[$row['purchaseid']]['Id'] = $row['purchaseid'];
				$rows[$row['purchaseid']]['date'] = date("r",$row['epoch']);
				$rows[$row['purchaseid']]['feature'] = $feature[$row['feature']]['longname'];
				$rows[$row['purchaseid']]['cost'] = "AUD $".number_format($row['cost'],2);
			}
		echo tablegen($headers, $rows,"736");
		echo "</div></div>";
		exit;
	}
if ($act == 'accounts')
	{
		?>
		<div style="text-align:left; font-size:9pt"><a href="#" onClick="innerlink('transactions')">Transactions</a> | <a href="#"  onclick="innerlink('purchases')">Credits Usage</a></div>
        <div id="inner"></div>
		<?
		exit;
	}
if ($act == 'activate')
	{
		$feat = $_REQUEST['feature'];
		$status = buyfeature($bcid,$feat);
		if ($status == 'done')
			{
				$mess = 'Transaction Completed';
			}
		else {
				$mess = 'Error: '.$status;
			}
		$act = 'features';
	}
if ($act == 'features')	
	{
		$fres = mysql_query("SELECT * from bc_features_details");
		while ($frow = mysql_fetch_array($fres))
			{
				$features[$frow['feature']] = $frow;
			}
		$res = mysql_query("SELECT * from bc_features_exp where bcid = '$bcid'");
		while ($rexp = mysql_fetch_assoc($res))
			{
				$features[$rexp['feature']]['expdate'] = $rexp['expdate'];
			}
		$res = mysql_query("SELECT * from bc_features where bcid = '$bcid'");
		$row = mysql_fetch_assoc($res);
		$f = array_keys($row);
		foreach ($f as $fld)
			{
				if ($fld != 'bcid')
					{
						$features[$fld]['name'] = ucfirst($fld);
						if ($row[$fld] == 1) 
							{
							$features[$fld]['value'] = '<span class="green">Active</span>';
							if ($features[$fld]['type'] == 'option') $features[$fld]['option'] = 'Expires: '.date("F j Y, g:i a",$features[$fld]['expdate']);
							}
						if ($row[$fld] == 0) 
							{
								$features[$fld]['value'] = '<span class="red">Inactive</span>';
								$features[$fld]['option'] = '<a href="#" onclick="actfeature(\''.$fld.'\')">Activate</a>';
							}
					}
			}
		$disp = '<center><span class="message">'.$mess.'</span></center><table width="736"><tr><td class="tableheader">Feature</td><td class="tableheader">Status</td><td class="tableheader">Cost (AUD)</td><td class="tableheader">Options</td></tr>';
		$ct = 1;$ctdm = 1;
		foreach ($features as $feature)
			{
				if ($feature['type'] == "option")
				{
				if ($ct % 2) 
					{
					$c = "tableitem";
					}
				else {
					$c = "tableitem_";
				}
				$disp .= '<tr>'; 
				$disp .= '<td class="'.$c.'">'.$feature['longname'].'</td>';
				$disp .= '<td class="'.$c.'">'.$feature['value'].'</td>';
				$disp .= '<td class="'.$c.'">AUD $'.number_format($feature['cost'],2).' '.ucfirst($feature['interval']).'</td>';
				$disp .= '<td class="'.$c.'">'.$feature['option'].'</td>';
				$disp .='</tr>';
				$ct++;
				}
				if ($feature['type'] == "dm")
				{
					if ($ctdm % 2) 
					{
					$cd = "tableitem";
					}
				else {
					$cd = "tableitem_";
				}
				$dispdm .= '<tr>'; 
				$dispdm .= '<td class="'.$cd.'">'.$feature['longname'].'</td>';
				$dispdm .= '<td class="'.$cd.'">'.$feature['value'].'</td>';
				$dispdm .= '<td class="'.$cd.'">'.$feature['description'].' '.ucfirst($feature['interval']).'</td>';
				$dispdm .= '<td class="'.$cd.'">'.$feature['option'].'</td>';
				$dispdm .='</tr>';
				$ctdm++;
				}
				
			}
		$disp .= '</table>';
		echo '<div class="tabbed">
		<ul>
		<li><a href="#bca">BlueCloud Active &#169;</a></li>
		<li><a href="#bcj">BlueCloud Jazz &#169;</a></li>
		<li><a href="#bcp">BlueCloud Plus &#169;</a></li>
		</ul>
		<div id="bca">';
		echo '<img src="http://www.bluecloudaustralia.com.au/wp-content/themes/BlueCloud/images/bc-active.png" alt="BlueCloud Jazz"><br />';
		echo '<table width="736"><tr><td class="tableheader">Dialing Options</td><td class="tableheader">Status</td><td class="tableheader">Description</td><td class="tableheader">Options</td></tr>';
		echo $dispdm;
		echo '</table>';
		$headersa[] = 'Feature';
		$headersa[] = 'Status';
		$headersa[] = 'Description';
		$headersa[] = 'Cost';
		$rowsa['1'] = array(1=>"Administrator Access",2=>"Active",3=>"Manage Campaigns /Agents /Load Lists",4=>"included") ;
		$rowsa['2'] = array(1=>"Script Editor",2=>"Active",3=>"Build Scripts + Add exportable Fields",4=>"included") ;
		$rowsa['3'] = array(1=>"List Loader",2=>"Active",3=>"Load Lists with De-Dupe/Wash functionalities",4=>"included") ;
		$rowsa['4'] = array(1=>"QA Portal",2=>"Active",3=>"Give access to Quality Control Staff",4=>"included") ;
		$rowsa['5'] = array(1=>"Recording Portal",2=>"Active",3=>"Audio Recording",4=>"included") ;
		$rowsa['6'] = array(1=>"Level 1 Technical Support",2=>"Active",3=>"Online / Email Support",4=>"included") ;
		$rowsa['7'] = array(1=>"Level 1 User Support",2=>"Active",3=>"BC provide Tutorials and User Manuals + email support",4=>"included") ;
		$rowsa['8'] = array(1=>"Instant Messaging [BlueCloud Chat]",2=>"Active",3=>"Talk to Admin + Agents + Support Staff via IM",4=>"included");
		$rowsa['9'] = array(1=>"Mobile Calls",2=>"Active",3=>"Cost per minute � Additional Charges Apply",4=>"AUD $0.22");
		
		echo tablegen($headersa,$rowsa,"736");
		
		echo '</div>
		<div id="bcj">
		';
		echo '<img src="http://www.bluecloudaustralia.com.au/wp-content/themes/BlueCloud/images/bc-jazz.png" alt="BlueCloud Jazz"><br />';
		echo $disp;
		echo '</div>
		<div id="bcp">';
		unset($headersa,$rowsa);
		$headersa[] = 'Feature';
		$headersa[] = 'Description';
		$headersa[] = 'Cost';
		$rowsa['1'] = array(1=>"Consultancy",2=>"Consulting Services � Call Centre Strategies",3=>"TBD");
		$rowsa['2'] = array(1=>"Administrative Support",2=>"List Loading/User + reports set up etc",3=>"AUD $1,210.00/ Month");
		$rowsa['3'] = array(1=>"Agents CC � Train & Motivate ",2=>"BC - CC experts - Train your agents","AUD $385.00 / Hour");
		$rowsa['4'] = array(1=>"Email Video Production",2=>"Script + Produce & Direct Video Media Clip","TBD");
		$rowsa['5'] = array(1=>"Script Design",2=>"BC - CC experts - Design script","AUD $110.00 / Hour");
		echo '<div class="col-3">
								<div class="prod-logo"><a href="http://www.bluecloudaustralia.com.au/bluecloud-plus/"><img src="http://www.bluecloudaustralia.com.au/wp-content/themes/BlueCloud/images/bc-plus.png" alt="BlueCloud Plus"></a></div>
								<b>Do you need Agency Services to help you set up campaigns?</b><br>
								We can be your Administrator/Trainer/script Designer etc.
								BlueCloud Plus offers a range of services to assist you. 
								<b>Call us on 1800 234 350</b><br>
								<a class="learnmore" target="_blank" href="http://www.bluecloudaustralia.com.au/bluecloud-plus/">Learn More</a>
							</div>';
		echo tablegen($headersa,$rowsa,"736");
		echo '</div>';
		exit;
	}
if ($act == 'usagestats')
	{
		$disp = '<table>
        <tr><td>Date Start</td>
    <td><input type="text" name="start" class="dates" id="start" /></td></tr>
    	<tr><td>Date End</td>
    <td><input type="text" name="end" class="dates" id="end"/></td></tr>
    <tr>
  	<td colspan="2" align="left">
    <a href="#" onClick="viewrep(\'viewlogdetails\')">View</a> |
    </td>
  </tr>
    	</table>
		<div id="resultdetails">
		</div>';
		echo $disp;
		exit;
	}
if ($act == 'paypalpay')	
	{
		?>
        <iframe src="https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WTSCUKTSZFXZ4&custom=<?=$bc['company'];?>" width="950" height="500" frameborder="0"></iframe>
        <?
		exit;
	}
if ($act == 'loadwallet')
	{
		?>
        <p> To Load your wallet, you may pay by cash, checque or bank transfer and email info@bluecloudaustralia.com.au
        the details of your payment.  However this make take 3 to 7 days to be reflected on your account.</p>
        <p> You may also pay by credit card through paypal by clicking this <a href="#" onClick="paypal()">Link</a>.  
        Payments made through this will take from a few hours to a day to be reflected.</p>
        <?
		exit;
	}
if ($act == 'dopurchase')
	{
		$packageid = $_REQUEST['id'];
		$num = $_REQUEST['num'];
		$t = dopurchase($packageid, $num, $bcid);
		echo $t;
		exit;
	}
if ($act == 'buypackage')
	{
		$packageid = $_REQUEST['id'];
		$t = buypackage($packageid);
		echo $t;
		exit;
	}
if ($act == 'showpackage')
	{
		$type = $_REQUEST['type'];
		$t = getpackages($type);
		echo $t;
		exit;
	}
if ($act == 'viewlogdetails')
	{
		extract($_REQUEST);
		$agents = getagentnames();
		$usage = getbcusage($bcid,$start,$end);
		$cost = getusagecost($bcid,$usage['usagesecs']);
		$rate = getrates($bcid);
		?>
        <br />
        <table width="780">
        <tr><td colspan="4"><b>Usage Details</b></td></tr>
        <tr><td>Period Covered:</td><td><?=$start;?> to <?=$end;?></td></tr>
        <tr><td>Total Hours:</td><td><?=$usage['usagehours'];?></td></tr>
        </table>
		 <?
		 $headers[] = 'Date';
		 $headers[] = 'Usage';
		 $headers[] = 'User';
		 $headers[] = 'Start';
		 $headers[] = 'End';
		 $headers[] = 'Duration';
		 $headers[] = 'Credits Used';
		 $ct = 0;
		foreach ($usage['detailed'] as $ud)
			{
				$rows[$ct]['1']= $ud['date'];
				$rows[$ct]['2']= $ud['usagetype'];
				$rows[$ct]['3']= $agents[$ud['userid']];
				$rows[$ct]['4']= date('h:i:s A',$ud['login']);
				$rows[$ct]['5']= date('h:i:s A',$ud['logout']);
				$rows[$ct]['6']= $ud['logout'] - $ud['login'];
				$rows[$ct]['7']= number_format(($ud['logout'] - $ud['login']) / 3600 * $rate[$ud['usagetype']],2);
				$ct++;
				
			}
		echo tablegen($headers,$rows);
		
		exit;
	}
if ($act == 'viewdetails')
	{
		extract($_REQUEST);
		$agents = getagentnames();
		$usage = getbcusage($bcid,$start,$end);
		$cost = getusagecost($bcid,$usage['usagesecs']);
		$rate = getrates($bcid);
		?>
        <br />
        <table width="929">
        <tr><td colspan="4"><b>Usage Details</b></td></tr>
        <tr><td>Period Covered:</td><td><?=$start;?> to <?=$end;?></td></tr>
        <tr><td>Total Hours:</td><td><?=$usage['usagehours'];?></td></tr>
        <tr><td>Date</td><td>Usage</td><td>Estimated Costs (excluding mobile costs)</td></tr>
        <?
		foreach ($usage['usagedays'] as $ud)
			{
				$dh = number_format($ud['duration'] /3600,2);
				$tcost = ($ud['duration'] /3600) * $rate[0];
				$ncost = number_format($tcost,2);
				?>
                 <tr><td><?=$ud['date'];?></td><td><?=$dh;?></td><td><?=$ncost;?> Credits</td></tr>
                <?
			}
		?>
        </table>
        <?
		exit;
	}

if ($act == 'view')
	{
		extract($_REQUEST);
		$agents = getagentnames();
		$usage = getbcusage($bcid,$start,$end);
		
		$cost = getusagecost($bcid,$usage['usagesecs']);
		$rate = getrates($bcid);
		$mobile = getmobileusage($bcid,$start,$end);
		$mobcost = getmobilecost($bcid,$mobile['total']);
		?>
        <br />
        <table width="929">
        <tr><td colspan="4"><b>Usage Summary</b></td></tr>
        <tr><td>Period Covered:</td><td><?=$start;?> to <?=$end;?></td></tr>
        <tr><td>Total Hours:</td><td><?=$usage['usagehours'];?></td></tr>
        <tr><td>Usage Costs:</td><td><?=$cost;?> <?=$rate[1];?></td></tr>
        <tr><td>Mobile Usage:</td><td><?=$mobile['total'];?></td></tr>
        <tr><td>Mobile Costs:</td><td><?=$mobcost;?> <?=$rate[1];?></td></tr>
        </table>
        <?
		exit;
	}
if ($act == 'terms')
	{
	include "terms.html";
	exit;
	}
?>