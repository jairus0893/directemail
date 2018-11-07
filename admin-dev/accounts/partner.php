<?php
session_start();
error_reporting(0);
include "../../dbconnect.php";
include "../phpfunctions.php";
$userid = $_SESSION['uid'];
$act= $_REQUEST['act'];
function nuform($num, $ch)
	{
		$ret = ($num / $ch) * 100;
		$ret2 = number_format($ret,2);
		return $ret2;
	}
$isadmin = $_SESSION['usertype'];
if ($isadmin != 'cpartner')
	{
		header("Location: ../login");
		exit;
	}
if (!$_SESSION['cp'])
	{
		$cpres = mysql_query("SELECT * from bc_partners where userid = '$userid'");
		$cprow = mysql_fetch_assoc($cpres);
		$_SESSION['cp'] = $cprow;
	}
$cps = $_SESSION['cp'];
$cpid = $cps['cpid'];
$acres = mysql_query("SELECT * from bc_clients where cpid ='$cpid'");
while ($acrow = mysql_fetch_assoc($acres))
	{
		$clients[] = $acrow;
		$selected = "";
		$blankoption = "<option></option>";
		if ($acrow['bcid'] == $_REQUEST['bcid']) 
			{
				$selected = "selected";
				$blankoption = "";
			}
		
		$clientlist .= $blankoption.'<option value="'.$acrow['bcid'].'" '.$selected.'>'.$acrow['company'].'</option>';
	}
if ($_REQUEST['bcid'])
	{
		$_SESSION['bcid'] = $_REQUEST['bcid'];
	}
$bcid = $_SESSION['bcid'];
if (!$bcid)
	{
		$bcid = $clients[0]['bcid'];
	}
include "partnerswitch.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>BlueCloud Australia - Channel Partners </title>
<script type="text/javascript" src="../../jquery/js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="../../jquery/js/jquery-ui-1.8.12.custom.min.js"></script>
<link href="../../jquery/css/redmond/jquery-ui-1.8.12.custom.css" rel="stylesheet" type="text/css" />
<link href="cstyle.css" rel="stylesheet" type="text/css" />
<style>
.ui-widget {
	font-family: Tahoma;
	font-size:8pt;
}
#buypack {
	font-family: Tahoma;
	font-size:8pt;
	width:400px;
	height:300px;
}
#menuleft {
	font-family:Tahoma;
	font-size:10pt;
	width:150px;
	position:relative;
	float:left;
	margin-right:10px;
}
#results {
	width:770px;
	position:relative;
	float:left;
}
.clear {
	clear:both;
}
.menuitem {
	text-align:center;
	position:relative;
}
.tableheader {
	background-color:#333;
	font-size:10pt;
	color:#0CC;
}
.tableitem {
	background-color:#FFF;
	font-size:9pt;
	color:#000;
}
.tableitem_ {
	background-color:#F0F0F0;
	font-size:9pt;
	color:#000;
}
.message {
	font-size:12px;
	color:#F00;
	font-weight:900;
}
.red {
	color:#F00;
}
.green {
	color:#0F0;
}
</style>
</head>

<body onload="dtpick()">
<div id="container">
<div id="header">
<img src="../images/bclogo-small.png" />
<div id="reporttitle">BlueCloud Accounts</div>
</div>
<hr />
<div id="menuleft">
	<p>
    Welcome <?=$cps['partner_name'];?>
    Your Partner Code: <?=$cps['partner_code'];?>
    
    </p>
  <p>
  	<span class="menuitem"><a href="<?=$_SERVER['PHP_SELF']?>?act=allaccounts">All Accounts</a></span><br />
    Usage Reports: <br />
    <select name="bcid" style="width:150px" onchange="window.location='<?=$_SERVER['PHP_SELF']?>?act=usagestats&bcid='+this.options[this.selectedIndex].value"><?=$clientlist;?></select><br />
    
    <span class="menuitem"><a href="#" onClick="linker('support')">Support</a></span></p>
    </p>
<hr />
<span class="menuitem"><a href="../../index.php?act=logout">Logout</a></span>
</div>

<div id="results">
<?=$disp;?>
</div>
<div class="clear"></div>
</div>
<div id="buypack"></div>
</body>
</html>
<script>
function innerlink(page)
	{
		$.ajax({
  		url: "partner.php?act="+page,
  		success: function(data){
    	 $('#inner').html(data);
		 dtpick();
		 
  		}
		});
	}
function tabbed(page)
	{
		$.ajax({
  		url: "partner.php?act="+page,
  		success: function(data){
    	 $('#results').html(data);
		 $('.tabbed').tabs();
		 dtpick();
		 
  		}
		});
	}
function linker(page)
	{
		$.ajax({
  		url: "partner.php?act="+page,
  		success: function(data){
    	 $('#results').html(data);
		 dtpick();
		 
  		}
		});
	}
function dtpick() {
		$( ".dates" ).datepicker({ dateFormat: 'yy-mm-dd' });
}		
document.title = window.name;
function viewrep(actions)
{

	
	var start = document.getElementById('start').value;
	var end = document.getElementById('end').value;
	$.ajax({
  		url: "partner.php?act="+actions+"&start="+start+"&end="+end,
  		success: function(data){
    	 $('#resultdetails').html(data);
  	}
	});
}

</script>