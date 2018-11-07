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
		//header("Location: ../login");
                echo "Not Authorized";
		exit;
	}
$bcres = mysql_query("SELECT * from bc_clients where bcid = '$bcid'");
$bc = mysql_fetch_array($bcres);
$wares = mysql_query("SELECT * from bc_wallet where bcid = '$bcid'");
$wallet = mysql_fetch_array($wares);

$act = $_REQUEST['act'];
include "accountswitch.php";
?>
<link href="vip/cstyle.css" rel="stylesheet" type="text/css" />

<div id="ManageCampaignSettingsMenu">
  <div class="apptitle">Account VIP Portal<br /><span style="color:#005D96"><?=$bc['company'];?>
      <?php
  if ($bc['ratetype'] == 'prepaid')
  { ?>
  <br> Wallet Credits: <?=$wallet['loadedcredits'];?>
  <?php
  }
  ?>
      </span></div>
    <div class="secnav">
        <ul>
       <li><a href="#" onclick="newwindow('vip/account.php?act=addcredits','Purchase Bluecloud Credits')">Add Credits</a></li>
           <li><a href="#" onClick="tabbed('transactions')">Transactions</a></li>
           <li><a href="#" onClick="linker('usagestats')">Usage Report</a></li>
           <li><a href="#" onClick="linker('admins')">Create/Edit Admin Users</a></li>
<?php
$staticpages = getdatatable("bc_static", "staticid");
foreach ($staticpages as $static)
{
	if ($static['staticid'] == 4)
    	echo '<li>'.$static['name'].'</li>';
    else
    	echo '<li><a href="#" onClick="staticlinker(\''.$static['staticid'].'\')">'.$static['name'].'</a></li>';
}
?></ul>
<span><img src="vip/accepted_cards.png" /></span>
</div>
</div>
<div id="results">
</div>
<div class="clear"></div>
<div id="buypack"></div>
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
function innerlink(page)
	{
		$.ajax({
  		url: "vip/account.php?act="+page,
  		success: function(data){
    	 $('#inner').html(data);
		 dtpick();
		 
  		}
		});
	}
function tabbed(page)
	{
		$.ajax({
  		url: "vip/account.php?act="+page,
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
		window.location = "vip/account.php?act=addcredits&bill_name="+bn+"&amount="+amt;
	}
function newwindow(url, title)
        {
            window.open(url, title,"scrollbars=yes,toolbar=no,directories=no,status=no,menubar=no, location=no, width=1200, height=600");
        }

function staticlinker(staticid)
	{
		$.ajax({
  		url: "vip/account.php?act=static&staticid="+staticid,
  		success: function(data){
    	 $('#results').html(data);
		 dtpick();
		 
  		}
		});
	}
function linker(page)
	{
		$.ajax({
  		url: "vip/account.php?act="+page,
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
  		url: "vip/account.php?act="+actions+"&start="+start+"&end="+end,
  		success: function(data){
    	 $('#resultdetails').html(data);
  	}
	});
}
function showpackage(type)
	{
		$.ajax({
  		url: "vip/account.php?act=showpackage&type="+type,
  		success: function(data){
    	 $('#results').html(data);
  		}
		});
	}
function buypackage(packageid)
	{
		$.ajax({
  		url: "vip/account.php?act=buypackage&id="+packageid,
  		success: function(data){
    	 $('#buypack').html(data);
		 $("#buypack").dialog({width:500, title:'Buy Package'});
  		}
		});
		
	}
function showload()
	{
		$.ajax({
  		url: "vip/account.php?act=loadwallet",
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
  		url: "vip/account.php?act=paypalpay",
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
  		url: "vip/account.php?act=dopurchase&id="+packageid+"&num="+num,
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
					gotovip();
				}
		 
  		}
		});
	}
function actfeature(feat)
	{
		$.ajax({
  		url: "vip/account.php?act=activate&feature="+feat,
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
  		url: "vip/account.php?act=changepassword&newpass="+vl+"&id="+id,
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
  		url: "vip/account.php?act=changerole&newrole="+newrole+"&id="+id,
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