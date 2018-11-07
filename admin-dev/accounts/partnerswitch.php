<?php
if ($act == 'allaccounts')
	{
		$ures = mysql_query("SELECT bcid,count(bcid) as counts from members where usertype = 'user' group by bcid");
		while ($urow = mysql_fetch_assoc($ures))
			{
				$usercount[$urow['bcid']] = $urow['counts'];
			}
		$month = date("Y-m-");
		$headers[] = 'Name';
		$headers[] = 'Email';
		$headers[] = 'Account Type';
		$headers[] = 'Users Created';
		$headers[] = 'Hours this Month';
		
		foreach ($clients as $client)
			{
				$use = getbcusage($client['bcid'],$month."01",$month."32");
				$rows[$client['bcid']]['company'] = $client['company'];
				$rows[$client['bcid']]['email'] = $client['email'];
				$rows[$client['bcid']]['ratetype'] = ucfirst($client['ratetype']);
				$rows[$client['bcid']]['bcid'] = $usercount[$client['bcid']];
				$rows[$client['bcid']]['usagehours'] = $use['usagehours'];
			}
		$disp = $month;
		$disp .= tablegen($headers,$rows,"770");
	}
if ($act == 'support')
	{
		echo "Watch Out for our new Online Support System.  This is being developed for us to deliver better support.<br>
		email us at <a href=\"mailto:support@bluecloudaustralia.com.au\">support@bluecloudaustralia.com.au</a> for now";
		exit;
	}

if ($act == 'accounts')
	{
		?>
		<div style="text-align:left; font-size:9pt"><a href="#" onclick="innerlink('transactions')">Transactions</a> | <a href="#"  onclick="innerlink('purchases')">Credits Usage</a></div>
        <div id="inner"></div>
		<?
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
        <p> You may also pay by credit card through paypal by clicking this <a href="#" onclick="paypal()">Link</a>.  
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
        <tr><td>Period Covered:</td><td><b><?=$start;?> to <?=$end;?></b></td></tr>
        <tr><td>Total Hours:</td><td><b><?=$usage['usagehours'];?></b></td></tr>
        </table>
		 <?
		 $headers[] = 'Date';
		 $headers[] = 'Usage';
		 $headers[] = 'User';
		 $headers[] = 'Start';
		 $headers[] = 'End';
		 $headers[] = 'Duration (secs)';
		 $ct = 0;
		foreach ($usage['detailed'] as $ud)
			{
				$rows[$ct][1]= $ud['date'];
				$rows[$ct][2]= $ud['usagetype'];
				$rows[$ct][3]= $agents[$ud['userid']];
				$rows[$ct][4]= date('h:i:s A',$ud['login']);
				$rows[$ct][5]= date('h:i:s A',$ud['logout']);
				$rows[$ct][6]= $ud['logout'] - $ud['login'];
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
        <tr><td>Total Hours:</td><td><b><?=$usage['usagehours'];?><b></td></tr>
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
?>