<?php
error_reporting(0);
//ini_set('display_errors','yes');
session_start();
include "../../dbconnect.php";
include "../phpfunctions.php";

$bcid = $_SESSION['bcid'];
$roles = getroles($bcid);
function nuform($num, $ch)
	{
		$ret = ($num / $ch) * 100;
		$ret2 = number_format($ret,2);
		return $ret2;
	}
$isadmin = $_SESSION['usertype'];
if ($isadmin != 'user' && checkrights('vip_portal'))
	{
		header("Location: ../login");
		exit;
	}
$bcres = mysql_query("SELECT * from bc_clients where bcid = '$bcid'");
$bc = mysql_fetch_array($bcres);
$wares = mysql_query("SELECT * from bc_wallet where bcid = '$bcid'");
$wallet = mysql_fetch_array($wares);

$act = $_REQUEST['act'];
include "accountswitch.php";
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
<style>
h4 {
	color:#107485;;
}
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
.menuitem{
	text-align:center;
	position:relative;
    color:#16A6BD;
}
.menuitem a{
	text-align:center;
	position:relative;
    color:#107485;
	text-decoration:none;
}
.menuitem a:hover{
	text-align:center;
	position:relative;
    color:#16A6BD;
	text-decoration:none;
}
.tableheader {
	background-color:#333;
	font-size:10pt;
	color:#0CC;
	text-align:left;
	font-weight:200;
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

<body>
<div id="container">
<div id="header">
<img src="../images/bclogo-small.png" />
<div id="reporttitle">BlueCloud Accounts</div>
</div>
<hr />
<div id="menuleft">
  <p>
  <h4>Account Details</h4>
  Account Name:<br /><?=$bc['company'];?><br />
  <br />
      <?php
  if ($bc['ratetype'] == 'prepaid')
  { ?>
  Wallet Credits: <?=$wallet['loadedcredits'];?><br /><br />
  <?php
  }
  ?>
  <span class="menuitem"><a href="#" onclick="newwindow('account.php?act=addcredits','Purchase Bluecloud Credits')">Add Credits</a></span><br />
  <span class="menuitem"><a href="#" onClick="tabbed('transactions')">Transactions</a></span><br />
    <span class="menuitem"><a href="#" onClick="linker('usagestats')">Usage Report</a></span><br />
    <span><img src="accepted_cards.png" /></span>
  <hr />
  <p>
  <h4>Administration</h4>
    <span class="menuitem" style="display:none"><a href="#" onClick="tabbed('features')">Manage System Features</a></span>
     <span class="menuitem"><a href="#" onClick="linker('admins')">Create/Edit Admin Users</a></span><br />
     <span class="menuitem" style="display:none"><a href="#" onClick="linker('admins')">Create/Edit Admin Roles</a></span></p><br />
     
  <hr />
<p>
<?php
$staticpages = getdatatable("bc_static", "staticid");
foreach ($staticpages as $static)
{
    echo '<span class="menuitem"><a href="#" onClick="staticlinker(\''.$static['staticid'].'\')">'.$static['name'].'</a></span><br />';
}
?>
  <span class="menuitem"><a href="#" onClick="dosupport()">Support</a></span></p>
<br />
<p>
<span class="menuitem"><a href="../../index.php?act=logout">Logout</a></span><br />
</p>
</div>

<div id="results">
</div>
<div class="clear"></div>
</div>
<div id="buypack"></div>
</body>
</html>
<script>
function addnewadmin()
	{
		var user = document.getElementById('newuserlogin').value;
		var pass = document.getElementById('newuserpass').value;
		var email = document.getElementById('newemail').value;
		var role = document.getElementById('newrole').value;
		$.ajax({
		type: 'POST',
		data: "userlogin="+user+"&userpass="+pass+"&email="+email+"&role="+role,
  		url: "account.php?act=addnewadmin",
  		success: function(data){
    	 $('#results').html(data);
		 dtpick();
		 
  		}
		});
	}
function reloadparent()
	{
		location.reload();
	}
function innerlink(page)
	{
		$.ajax({
  		url: "account.php?act="+page,
  		success: function(data){
    	 $('#inner').html(data);
		 dtpick();
		 
  		}
		});
	}
function tabbed(page)
	{
		$.ajax({
  		url: "account.php?act="+page,
  		success: function(data){
    	 $('#results').html(data);
		 $('.tabbed').tabs();
		 dtpick();
		 
  		}
		});
	}
function addcreditwin()
	{
		var bn = $("#bn").val();
		var amt = $("#amount").val();
		window.location = "account.php?act=addcredits&bill_name="+bn+"&amount="+amt;
	}
function newwindow(url, title)
        {
            window.open(url, title,"scrollbars=yes,toolbar=no,directories=no,status=no,menubar=no, location=no, width=1200, height=600");
        }
function dosupport()
	{
		window.open("../../support/login.php?lemail=<?=$_SESSION['email'];?>&lticket=<?=$_SESSION['support'];?>", "QA PORTAL","scrollbars=yes,toolbar=no,directories=no,status=no,menubar=no, location=no, width=1200, height=600");
	}
function staticlinker(staticid)
	{
		$.ajax({
  		url: "account.php?act=static&staticid="+staticid,
  		success: function(data){
    	 $('#results').html(data);
		 dtpick();
		 
  		}
		});
	}
function linker(page)
	{
		$.ajax({
  		url: "account.php?act="+page,
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
  		url: "account.php?act="+actions+"&start="+start+"&end="+end,
  		success: function(data){
    	 $('#resultdetails').html(data);
  	}
	});
}
function showpackage(type)
	{
		$.ajax({
  		url: "account.php?act=showpackage&type="+type,
  		success: function(data){
    	 $('#results').html(data);
  		}
		});
	}
function buypackage(packageid)
	{
		$.ajax({
  		url: "account.php?act=buypackage&id="+packageid,
  		success: function(data){
    	 $('#buypack').html(data);
		 $("#buypack").dialog({width:500, title:'Buy Package'});
  		}
		});
		
	}
function showload()
	{
		$.ajax({
  		url: "account.php?act=loadwallet",
  		success: function(data){
    	 $('#buypack').html(data);
		 $("#buypack").dialog({width:800, title:'Load Wallet'});
  		}
		});
		
	}
function paypal()
	{
		$("#buypack").dialog('close');
		$.ajax({
  		url: "account.php?act=paypalpay",
  		success: function(data){
    	 $('#buypack').html(data);
		 $("#buypack").dialog({width:800, title:'Load Wallet'});
  		}
		});
	}
function dopurchase(packageid)
	{
		$("#buypack").dialog('close');
		var num = $("#packnum").val();
		$.ajax({
  		url: "account.php?act=dopurchase&id="+packageid+"&num="+num,
  		success: function(data){
			if (data == 'insufficient')
				{
					var m = $('#buypack').html();
					var mess = '<font color="red">INSUFFICIENT FUNDS</font><br>';
					$('#buypack').html(m + mess);
					$("#buypack").dialog({width:500, title:'Buy Package'});
				}
			if (data == 'done')
    	 		{
					window.location="account.php";
				}
		 
  		}
		});
	}
function actfeature(feat)
	{
		$.ajax({
  		url: "account.php?act=activate&feature="+feat,
  		success: function(data){
    	 $('#results').html(data);
		 }
		});
	}
function computetcc(cost)
	{
		var tcc = cost * $("#packnum").val();
		$("#totalcreditcost").html(tcc);
	}
function changepass(di, fild, id)
	{
		var target = document.getElementById(di);
		var vl = fild;
		target.innerHTML = '<input type=text value="'+vl+'" onblur="changepassword(this.value,\''+di+'\',\''+id+'\')">';
		
	}
var dd;
function changepassword(vl,di,id)
	{
	dd = di;
	$.ajax({
  		url: "account.php?act=changepassword&newpass="+vl+"&id="+id,
  		success: function(data){
    	$("#"+di).html('<a onclick="changepass(\''+di+'\',\''+vl+'\',\''+id+'\')">'+vl+'</a>');
		 }
		});
	
	}

function changerole(id,vl)
	{
		var inner = $("#role"+id).html();
		$("#role"+id).html("<select name=role id=role onchange=\"dochangerole(this,'"+id+"')\" onblur=cancelrolechange('"+id+"','"+vl+"')><option value=0></option><?=addslashes($roles['options']);?></select>");
	}
function dochangerole(el,id)
	{
		var newrole = el.options[el.selectedIndex].value;
		$.ajax({
  		url: "account.php?act=changerole&newrole="+newrole+"&id="+id,
  		success: function(data){
    	linker('admins');
		 }
		});
	}
function cancelrolechange(id,inner)
	{
		$("#role"+id).html('<a onclick="changerole(\''+id+'\')">'+inner+'</a>');
	}
</script>