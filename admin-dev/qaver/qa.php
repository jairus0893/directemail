<?php
session_start();

$path = '/var/www/html/BlueCloud-Dev';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
$bcid = $_SESSION['bcid'];
$act = $_REQUEST['act'];
$uid = $_SESSION['uid'];
$debugm = '';
date_default_timezone_set($_SESSION['timezone']);
include "../../dbconnect.php";
require "../../classes/classes.php";
require "../../classes/records.php";
require "../../classes/lists.php";
require "../../classes/projects.php";
require "../../classes/labels.php";
require "../../classes/S3.php";
include_once '../../phpmailer/PHPMailerAutoload.php';
require "../../classes/mailer.php";
include "../phpfunctions.php";
require "mp3_get_tags.php";
include "qaver.php";
$agentids = getagentids($bcid);

include "qaverswitch.php";
$projects = projectlist($bcid);
$projectsactiveandinactive = projectlist($bcid);
$projectsactive = activeprojectlist($bcid);

$dispolist = dispolist($_POST['projectid']);

if ($act == "applycustomizeview") {
	$userid = $_REQUEST['userid'];
	$viewfields	= $_REQUEST['viewfields'];
	$result = array();
	foreach ($viewfields as $value) { 
		if ( $value == "epoch_timeofcall") 
		{
			$result[$value] = array( 'Date', 1 );		
		}		
		if ( $value == "projectid") 
		{
			$result[$value] = array( 'Campaign Name', 1 );		
		}
		if ( $value == "status" ) 
		{	
			$result[$value] = array( 'QA status', 1 );
		}
		if ( $value == "approvedby" ) 
		{	
			$result[$value] = array( 'Approved By', 1 );
		}
		if ( $value == "approvedto" ) 
		{	
			$result[$value] = array( 'Approved To', 1 );
		}
		if ( $value == "assignedby" ) 
		{	
			$result[$value] = array( 'Assigned By', 1 );
		}
		if ( $value == "assignedto" ) 
		{	
			$result[$value] = array( 'Assigned To', 1 );
		}
		if ( $value == "clientdispo" ) 
		{	
			$result[$value] = array( 'Client Disposition', 1 );
		}
		if ( $value == "dispo" ) 
		{	
			$result[$value] = array( 'Disposition', 1 );
		}		
		if ( $value == "assigned" ) 
		{	
			$result[$value] = array( 'Agent', 1 );	
		}		
		if ( $value == "phone" ) 
		{	
			$result[$value] = array( 'Phone', 1 );
		}		
		if ( $value == "altphone" ) 
		{	
			$result[$value] = array( 'Alt Phone', 1 );		
		}		
		if ( $value == "mobile" ) 
		{			
			$result[$value] = array( 'Mobile', 1 );		
		}
		if ( $value == "title" ) 
		{			
			$result[$value] = array( 'Title', 1 );		
		}			
		if ( $value == "cname" ) 
		{			
			$result[$value] = array( 'Name', 1 );		
		}		
		if ( $value == "cfname" ) 
		{			
			$result[$value] = array( 'Firstname', 1 );		
		}		
		if ( $value == "clname" ) 
		{
			$result[$value] = array( 'Lastname', 1 );
		}		
		if ( $value == "company" ) 
		{	
			$result[$value] = array( 'Company', 1 );
		}
		if ( $value == "industry" ) 
		{	
			$result[$value] = array( 'Industry', 1 );
		}			
		if ( $value == "email" ) 
		{	
			$result[$value] = array( 'Email', 1 );		
		}		
		if ( $value == "address1" ) 
		{			
			$result[$value] = array( 'Address1', 1 );		
		}		
		if ( $value == "address2" ) 
		{	
			$result[$value] = array( 'Address2', 1 );
		}		
		if ( $value == "suburb" ) 
		{	
			$result[$value] = array( 'Suburb', 1 );		
		}		
		if ( $value == "city" ) 
		{	
			$result[$value] = array( 'City', 1 );		
		}		
		if ( $value == "state" ) 
		{	
			$result[$value] = array( 'State', 1 );		
		}		
		if ( $value == "country" ) 
		{	
			$result[$value] = array( 'Country', 1 );
		}		
		if ( $value == "zip" ) 
		{	
			$result[$value] = array( 'Postcode', 1 );		
		}		
		if ( $value == "epoch_callable" ) 
		{	
			$result[$value] = array( 'Date Set', 1 );
		}
		if ( $value == "note" ) 
		{	
			$result[$value] = array( 'Notes', 1 );
		}	
		if ( $value == "comments" ) 
		{	
			$result[$value] = array( 'Comments', 1 );
		}
		if ( $value == "positiontitle" ) 
		{	
			$result[$value] = array( 'Position Title', 1 );
		}
		if ( $value == "sic" ) 
		{	
			$result[$value] = array( 'SIC', 1 );
		}
		if ( $value == "leadid" ) 
		{	
			$result[$value] = array( 'Lead ID', 1 );
		}	
	}
	$vfs = json_encode($result);		
	mysql_query("INSERT INTO uiopt SET user_id = '$userid', config = 'QAPortalCustomView', value = '$vfs'");
}
if ($act == "restoredefaultcustomizeview") {
	$userid = $_REQUEST['userid'];
	$viewfields	 = array( // fieldname=>array(label,checked by default)
		'epoch_timeofcall'=> array('Date',1),
		'projectid'=> array('Campaign Name',1),
		'status'=>array('QA status',1),
		'approvedby'=>array('Approved By',0),
		'approvedto'=>array('Approved To',0),
		'assignedby'=>array('Assigned By',0),
		'assignedto'=>array('Assigned To',0),
		'clientdispo'=>array('Client Disposition',0),
		'dispo'=>array('Disposition',1),
		'assigned'=>array('Agent',1),
		'phone'=>array('Phone',1),
		'altphone'=>array('Alt Phone',1),
		'mobile'=>array('Mobile',0),
		'title'=>array('Title',1),
		'cname'=>array('Name',1),
		'cfname'=>array('Firstname',0),
		'clname'=>array('Lastname',0),
		'company'=>array('Company',1),
		'industry'=>array('Industry',0),
		'email'=>array('Email',1),
		'address1'=>array('Address1',0),
		'address2'=>array('Address2',0),
		'suburb'=>array('Suburb',0),
		'city'=>array('City',1),
		'state'=>array('State',0),
		'country'=>array('Country',0),
		'zip'=>array('Postcode',0),
		'epoch_callable'=>array('Date Set',0),
		'note' => array('Notes', 1),
		'comments' => array('Comments', 1),
		'positiontitle' => array('Position Title', 1),
		'sic' => array('SIC', 0),
		'leadid' => array('Lead ID', 1)
	);
	$vfs = json_encode($viewfields);		
	mysql_query("INSERT INTO uiopt SET user_id = '$userid', config = 'QAPortalCustomView', value = '$vfs'");
}
if ($act == "showactivecampaign") {
	$userid = $_REQUEST['userid'];
	mysql_query("INSERT INTO uiopt SET user_id = '$userid', config = 'EnabledActiveAndInactiveCampaign', value = '0'");
}
if ($act == "showinactivecampaign") {
	$userid = $_REQUEST['userid'];
	mysql_query("INSERT INTO uiopt SET user_id = '$userid', config = 'EnabledActiveAndInactiveCampaign', value = '1'");
}
?>
<!DOCTYPE html">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script type="text/javascript" src="../../jquery/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../jquery/js/jquery-ui-1.8.12.custom.min.js"></script>
<script type="text/javascript" src="../../jquery/js/blockui.js"></script>
<script type="text/javascript" src="../../jquery/js/jqprint.js"></script>
<script type="text/javascript" src="../../jquery/js/jqform.js"></script>
<script type="text/javascript" src="../../jquery/datatable/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../../jquery/jplayer/jquery.jplayer.min.js"></script>
<script src="../../jquery/datetimepicker/jquery.datetimepicker.js"></script>
<link rel="stylesheet" type="text/css" href="../../jquery/datetimepicker/jquery.datetimepicker.css" />
<link href="../../jquery/css/redmond/jquery-ui-1.8.12.custom.css" rel="stylesheet" type="text/css" />
<link href="../../jquery/datatable/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
<link href="qaver.css" rel="stylesheet" type="text/css" />
<script src="../../jquery/js/jquery.multiselect.js"></script>
<link href="../../jquery/css/jquery.multiselect.css" rel="stylesheet" type="text/css" />
<style>
.dialogform {
	display:none;
}
</style>
</head>
<?php
if ($_POST['projectid']) {
	if (!empty($_REQUEST["viewfields"])) {
		foreach ($_REQUEST["viewfields"] as $vfval) {
	        $vkeys .= $vfval . ", ";
	    }
	    $vkeys = rtrim($vkeys, ", ");
	}
	?>
	<body onload="getcustomdata(<?php echo $_POST["projectid"]?>, <?php echo "'".$vkeys."'"?>)">
	<?php 
} else {
	?>
<body>
<?php 
}
?>
<div id="container">
<div id="header">
<img src="../images/bclogo-small.png" />
<div id="reporttitle">QA Portal</div>
</div>
<?php  if (!checkrights('admin_portal')){
?>
    <div id="navmenu" style="text-align:right;padding:5px"><a href="../../index.php">Logout</a></div>
    <?php  } ?>
<hr />
<div id="filters">
<table width="100%">
<form name="filterform" action="qa.php?act=search" method="post">
<tr>
<td>*Campaign:</td>
<td>
	<select name="projectid" id="projectid" onchange="projectchange();">
		<?php
		$getActiveAndInactiveCampaign = mysql_query("SELECT * FROM uiopt WHERE user_id = '$uid' AND config = 'EnabledActiveAndInactiveCampaign' ORDER BY ts DESC LIMIT 1");
		if (mysql_num_rows($getActiveAndInactiveCampaign) > 0) {
			$getdata = array();
		    while ($getrow = mysql_fetch_assoc($getActiveAndInactiveCampaign)) {
		        $getdata = $getrow;
		    }
		    if ($getdata["value"] == 1) {
		    	?>
		    	<option value="<?php echo $projectsactiveandinactive[$_POST['projectid']]['projectid'];?>"><?php echo $projectsactiveandinactive[$_POST['projectid']]['projectname'];?></option>
				<?php
			} else {
				?>
		    	<option value="<?php echo $projectsactive[$_POST['projectid']]['projectid'];?>"><?php echo $projectsactive[$_POST['projectid']]['projectname'];?></option>
				<?php
			}
		} else if (mysql_num_rows($getActiveAndInactiveCampaign) == 0) {
			?>
	    	<option value="<?php echo $projectsactive[$_POST['projectid']]['projectid'];?>"><?php echo $projectsactive[$_POST['projectid']]['projectname'];?></option>
			<?php
		}
		if ($_POST['projectid'] == 'all') {
			$alsel = 'selected="selected"';
		}
		else $alsel = '';
		?>
		<option value="all" <?php echo $alsel;?>>All</option>
		<?php
		$getActiveAndInactiveCampaign = mysql_query("SELECT * FROM uiopt WHERE user_id = '$uid' AND config = 'EnabledActiveAndInactiveCampaign' ORDER BY ts DESC LIMIT 1");
		if (mysql_num_rows($getActiveAndInactiveCampaign) > 0) {
			$getdata = array();
		    while ($getrow = mysql_fetch_assoc($getActiveAndInactiveCampaign)) {
		        $getdata = $getrow;
		    }
		    if ($getdata["value"] == 1) {
		    	?>
		    	<?php echo createdropdown($projectsactiveandinactive,"projectname",'id');?>
				<?php
			} else {
				?>
		    	<?php echo createdropdown($projectsactive,"projectname",'id');?>
				<?php
			}
		} else if (mysql_num_rows($getActiveAndInactiveCampaign) == 0) {
			?>
	    	<?php echo createdropdown($projectsactive,"projectname",'id');?>
			<?php
		}
		?>
	</select>
	<?php
	$getActiveAndInactiveCampaign = mysql_query("SELECT * FROM uiopt WHERE user_id = '$uid' AND config = 'EnabledActiveAndInactiveCampaign' ORDER BY ts DESC LIMIT 1");
	if (mysql_num_rows($getActiveAndInactiveCampaign) > 0) {
		$getdata = array();
	    while ($getrow = mysql_fetch_assoc($getActiveAndInactiveCampaign)) {
	        $getdata = $getrow;
	    }
	    if ($getdata["value"] == "1") {
	    	?>
	    	<input type="checkbox" id="inactivecampaign" name="inactivecampaign" checked="checked">&nbsp;Show also inactive campaign</input>
	    	<?php
		} else {
	    	?>
	    	<input type="checkbox" id="inactivecampaign" name="inactivecampaign">&nbsp;Show also inactive campaign</input>
	    	<?php
		}
	} else if (mysql_num_rows($getActiveAndInactiveCampaign) == 0) {
		?>
    	<input type="checkbox" id="inactivecampaign" name="inactivecampaign">&nbsp;Show also inactive campaign</input>
    	<?php  
	}
	?>
</td>
<td>*Start</td>
<td>
	<?php
	if ($_POST['start'] == NULL) {
		$datenow = date('Y-m-d');
	?>
	<input type="text" name="start" id="start" class="dateinput" value="<?php echo $datenow;?>" />
	<?php
	} else if ($_POST['start'] != NULL) {
	?>
	<input type="text" name="start" id="start" class="dateinput" value="<?php echo $_POST['start'];?>" />
	<?php
	}
	?>
</td>
</tr>
<tr>
<td>*Disposition:</td>
<td>
	<select name="dispostion[]" id="dispostion" multiple="multiple">
	<?php
	if (!empty($_POST['dispostion'])) {
		foreach ($dispolist as $dispos) {
			foreach ($_POST['dispostion'] as $postdispo) {
				if ($postdispo == $dispos['statusname']) {
		            ?>
		            <option selected value="<?php echo $dispos['statusname'];?>"><?php echo ucfirst($dispos['statusname']);?></option>
		            <?php
		        }
			}
		}
	}
	?>
	<?php echo createdropdown($dispolist,"statusname","statusname");?>
	</select>
	<br/>
	<i><p style="font-size:xx-small;">*Hold down the Ctrl (windows) / Command (Mac) button to select multiple options.</p></i>
</td>
<td>*End</td>
<td>
	<?php
	if ($_POST['end'] == NULL) {
		$datenow = date('Y-m-d');
	?>
	<input type="text" name="end" id="end" class="dateinput" value="<?php  echo $datenow;?>" />
	<?php
	} else if ($_POST['end'] != NULL) {
	?>
	<input type="text" name="end" id="end" class="dateinput" value="<?php echo $_POST['end'];?>" />
	<?php
	}
	?>
</td>
</tr>
<tr>
<td>Agent</td><td colspan="1">
<select name="agentid">
<option value="all">All</option>
<?php
foreach ($agentids as $agid)
	{
        if ($_POST['agentid'] == $agid['userid'])
        {
            ?>
            <option SELECTED value="<?php echo $agid['userid'];?>"><?php  echo $agid['alast'] . ", " . $agid['afirst'] . " - (" . $agid['userlogin'] . ")";?></option>
            <?php
        }
        else
        {            
    		?>
            <option value="<?php echo $agid['userid'];?>"><?php  echo $agid['alast'] . ", " . $agid['afirst'] . " - (" . $agid['userlogin'] . ")";?></option>
            <?php
        }
	}
?>
</select>
</td>
<?php
$datesetselected = '';
if ($_REQUEST['datetype'] == 'dateset')
{
    $datesetselected = 'selected="selected"';
}
?>
<td>Date Type</td><td><select name="datetype" id="datetype"><option value="calldate">Call Date</option><option value="dateset" <?php echo $datesetselected;?>>Date Set</option></select></td>
</tr>
<tr>
<td><a href="#" onclick="dosearch();">Search</a> | <a href="#" onclick="doexport();">Export</a></td><td colspan="3"></td>
</tr>
</form>
</table>
    <div style="text-align:right"><input type="button" value="Customize View" style="padding:5px" onclick="togglecv()" /></div>
</div>
<div id="cview">
    <h3 style="padding-bottom: 10px;"> Default Fields</h3>
    <?php
    $customizeViewArray = array(
	    'epoch_timeofcall' => array(
	        'Date',
	        1
	    ),
	    'projectid' => array(
	        'Campaign Name',
	        1
		),
		'status' => array(
	        'QA status',
	        1
	    ),
	    'approvedby' => array(
	        'Approved By',
	        1
	    ),
	    'approvedto' => array(
	        'Approved To',
	        1
	    ),
	    'assignedby' => array(
	        'Assigned By',
	        1
	    ),
	    'assignedto' => array(
	        'Assigned To',
	        1
	    ),
	    'clientdispo' => array(
	        'Client Disposition',
	        1
	    ),
	    'dispo' => array(
	        'Disposition',
	        1
	    ),
	    'assigned' => array(
	        'Agent',
	        1
	    ),
	    'phone' => array(
	        'Phone',
	        1
	    ),
	    'altphone' => array(
	        'Alt Phone',
	        0
	    ),
	    'mobile' => array(
	        'Mobile',
	        0
	    ),		
	    'title' => array(        
	        'Title',        
	        1    
	    ),    
	    'cname' => array(
	        'Name',
	        1
	    ),
	    'cfname' => array(
	        'Firstname',
	        0
	    ),
	    'clname' => array(
	        'Lastname',
	        0
	    ),
	    'company' => array(
	        'Company',
	        1
	    ),		
	    'industry' => array(        
	        'Industry',        
	        1    
	    ),    
	    'email' => array(
	        'Email',
	        1
	    ),
	    'address1' => array(
	        'Address1',
	        0
	    ),
	    'address2' => array(
	        'Address2',
	        0
	    ),
	    'suburb' => array(
	        'Suburb',
	        0
	    ),
	    'city' => array(
	        'City',
	        1
	    ),
	    'state' => array(
	        'State',
	        0
	    ),
	    'country' => array(
	        'Country',
	        0
	    ),
	    'zip' => array(
	        'Postcode',
	        0
	    ),
	    'epoch_callable' => array(
	        'Date Set',
	        0
	    ),
	    'note' => array(
	        'Notes',
	        0
	    ),
	    'comments' => array(
	        'Comments',
	        0
	    ),
	    'positiontitle' => array(
	        'Position Title',
			0
	    ),
	    'sic' => array(
	        'SIC',
			0
	    ),
	    'leadid' => array(
	        'Lead ID',
	        0
	    )
	);
    if ($_REQUEST["viewfields"]) {
		foreach ($_REQUEST["viewfields"] as $vfval) {
	        $keys .= $vfval . ", ";
	    }
	    $keys = rtrim($keys, ", ");
		$checked = "checked";
		
		//DEFAULT FIELDS
	    $rct  = 0;
	    foreach ($customizeViewArray as $customizekey => $customizearv) {
	        if ($rct == 7) {
	            echo '</table>';
	            $rct = 0;
	        }
	        if ($rct == 0) {
	            echo '<table class="vftab"><tr><th>Column</th><th>Show</th></tr>';
	        }
            if (strpos($keys, $customizekey) !== false) {
            	echo '<tr><td class="label">'.$customizearv[0].'</td><td class="ck"><input type="checkbox" name="viewfields[]" class="vfs" value="'.$customizekey.'" '.$checked.'></td></tr>';
    		} else {
    		    echo '<tr><td class="label">'.$customizearv[0].'</td><td class="ck"><input type="checkbox" name="viewfields[]" class="vfs" value="'.$customizekey.'"></td></tr>';
    		}
	        $rct++;
	    }
	    echo '</table>';
	} else {
    $getQAPortalCustomizeView = mysql_query("SELECT * FROM uiopt WHERE user_id = '$uid' AND config = 'QAPortalCustomView' ORDER BY ts DESC LIMIT 1");
	if (mysql_num_rows($getQAPortalCustomizeView) > 0) {
		$getdata = array();
	    while ($getrow = mysql_fetch_assoc($getQAPortalCustomizeView)) {
	        $getdata = $getrow;
	    }
	    $jsonintoarray = json_decode($getdata["value"]);
	    foreach ($jsonintoarray as $uioptkey => $uioptval) {
	        $keys .= $uioptkey . ", ";
	    }
	    $keys = rtrim($keys, ", ");
	    $rct  = 0;
	    foreach ($customizeViewArray as $customizekey => $customizearv) {
	        if ($rct == 7) {
	            echo '</table>';
	            $rct = 0;
	        }
	        if ($rct == 0) {
	            echo '<table class="vftab"><tr><th>Column</th><th>Show</th></tr>';
	        }
	        $compare = strpos($keys, $customizekey);
	        if ($compare === false) {
	            echo '<tr><td class="label">'.$customizearv[0].'</td><td class="ck"><input type="checkbox" name="viewfields[]" class="vfs" value="'.$customizekey.'"></td></tr>';
	        } else {
	            foreach ($jsonintoarray as $jsonkey => $jsonarv) {
	                $checked = '';
	                if ($jsonarv[1] == 1)
	                    $checked = "checked";
	                if ($customizearv[0] == $jsonarv[0]) {
	                	echo '<tr><td class="label">'.$jsonarv[0].'</td><td class="ck"><input type="checkbox" name="viewfields[]" class="vfs" value="'.$jsonkey.'" '.$checked.'></td></tr>';
	        		}
	            }
	        }
	        $rct++;
	    }
	    echo '</table>';
	} else if (mysql_num_rows($getQAPortalCustomizeView) == 0) {
		$rct = 0;
	    $ctmctr = 0;
	    foreach ($viewfields as $key=>$arv)
	    {
	        if ($rct == 7) {echo '</table>';$rct = 0;}
	        if ($rct == 0)
	        {
	            echo '<table class="vftab"><tr><th>Column</th><th>Show</th></tr>';
	        }
	        if($arv[2] == "custom"){
	            if($ctmctr == 0){
	                echo '</table><div id="customdatatable"><h3>Custom Fields</h3><table class="vftab"><tr><th>Column</th><th>Show</th></tr>';
	            } elseif ($ctmctr > 0) {
	                # code...
	            }
	            $ctmctr++;
	            $rct = 1;
	        }
	        $checked = '';
	        if ($arv[1] == 1) $checked = "checked";
	        echo '<tr><td class="label">'.$arv[0].'</td><td class="ck"><input type="checkbox" name="viewfields[]" class="vfs" value="'.$key.'" '.$checked.'></td></tr>';
	        $rct++;
	    }
	    echo '</table>';
	    echo ($ctmctr > 0 ? '</div>' : '');
		}
	}
    ?>
    <div class="custom-control">
        <button class="cc-button" data-action="selectall">Select All</button>
        <button class="cc-button" data-action="clearall">Clear All</button>
        <input type="hidden" name="scvuserid" id="scvuserid" value="<?php  echo $uid; ?>"/>
        <button class="cc-button" data-action="savecustomview">Save Custom View</button>
		<button class="cc-button" data-action="restoredefaultcustomizeview">Restore Default</button>
    </div>
</div>
<div class="clear"></div>
<hr />
<br />
<?php echo $dcont;?>
</div>
    <div id="jp" style="display:none"></div>
    <div id="dialogcontainer" style="display:none"></div>
    <div id="recordingscontainer" style="display:none"></div>
</body>
<script>
var viewcv = false
function inbrowser(nurl)
{
    $("#dialogcontainer").dialog("destroy");
    $("#dialogcontainer").html('<iframe src="'+nurl+'" style="border:0px" frameborder="0" width="100%" height="100%"></iframe>');
    $("#dialogcontainer").dialog({
        width: 955,
        height:505,
        modal: true
    });
}
function showupdatepage(statusid)
	{
		$.ajax({
                    url:"qa.php?act=getstatusoption&statusid="+statusid,
                    success: function(resp){
                        if (resp != 'none') inbrowser(resp);
                    }
                });

	}
function togglecv()
{
    $("#cview").toggle({easing:'linear'});
    
}
function createdateinput()
	{
	jQuery('#datetd').show();
	}
function cleardateinput()
	{
	jQuery('#datetd').hide();
        
	}
function epochtoutc(epoch)
{
    var utcSeconds = epoch;
    var d = new Date(0); // The 0 there is the key, which sets the date to the epoch
    d.setUTCSeconds(utcSeconds);
   
    return d;
}
function sendemailtoclient(i)
	{   
                var to = $("select[name=emailtoclient]").val();
                var leadid = $("#tlid").html();
                var emailcont = printable("#msg_print");
                var odata = {htmlbody: emailcont};
                var subject = $("input[name=subject]").val();
                $.ajax({
                    url:'qa.php?act=emailtoclient&to='+to+'&subject='+subject+'&leadid='+leadid,
                    type: 'POST',
                    data: odata,
                    success:function(resp){
                        alert("Email Sent");
                    }
                });
		
	}

function emaillead(leadid)
	{
		$("#emailcontacts"+leadid).dialog();
                $("#setc").button();
	}
        
function qacall(leadid,event)
{
    event.stopPropagation();
    $("#qamailcont").remove();
    $.ajax({
        url: "qa.php?act=dialer&sub=dial_controls&leadid="+leadid,
        success: function(data){
            if (data == 'setext')
                {
                    alert("Set Extension first!");
                    setadminext();
                }
            else {
           $("#dialogcontainer").html(data);
           $("#dialogcontainer").dialog({
               modal: true
           })
           dodial(leadid);
            }
        }
    });
    
}
function selectslot(leadid,slotid,opt)
{
    $.ajax({
            url: 'qa.php?act=selectslot&slotid='+slotid+'&leadid='+leadid+'&opt='+opt,
            success: function(resp){
                $("#dialogcontainer").dialog("destroy");
                savelead()
            }
        });
}
function saveadminext()
{
    var adminexts = $("#setadminext").val()
    $.ajax({
 		 	url: "../admin.php?act=saveadminext&adminext="+adminexts,
 			success: function(resp){
                            $("#dialogcontainer").dialog("destroy");
                            $("#adminext").html(resp);
                            adminext = resp;
  			}
		});
}
function setadminext()
{
    $.ajax({
 		 	url: "../admin.php?act=setadminext",
 			success: function(resp){
                            $("#dialogcontainer").dialog("destroy");
                            $("#dialogcontainer").html(resp);
                            $("#dialogcontainer").dialog({
                                title: "Set Extension",
                                minWidth:400
                            });
                            $(".jbut").button();
  			}
		});
}
function qamail(leadid,event)
{
    event.stopPropagation();
    $("#qamailcont").remove();
    $.ajax({
        url: "qa.php?act=getlead&leadid="+leadid,
        success: function(data){
        //$("#dispostion").html(data);
        
                $("#container").append('<div id="qamailcont" style="display:none">'+data+'</div>');
                emaillead(leadid);
                // alert($("#reassign_agent_select").val());
        }
    });
    
}
function doslots(leadid,clientid)
{
        $.ajax({
            url: 'qa.php?act=cslots&clientid='+clientid+'&leadid='+leadid,
            success: function(resp){
                $("#dialogcontainer").dialog("destroy");
                $("#dialogcontainer").html(resp);
                $("#dialogcontainer").dialog({minWidth:614});
                var dt = jQuery(".datatabslot").dataTable();
                dt.fnSort([[1,'asc']])
            }
        });
	
}
function dosearch() {
    var vf = $("input.vfs").serialize();
    var campaign = $("#projectid").val();
    var start = $("#start").val();
    var end = $("#end").val();
	var disposition = $('#dispostion :selected').map(function(){
  		return this.value
	}).get();
	if (campaign != "" && start != "" && end != "" && disposition != "") {
		document.filterform.action = 'qa.php?act=search&'+vf+'&disposition='+disposition;
		document.filterform.submit();
	} else {
		alert("Please fill in all the required fields (*) and try again.");
	}
}
function doexport() {
    var vf = $("input.vfs").serialize();
    var campaign = $("#projectid").val();
    var start = $("#start").val();
    var end = $("#end").val();
	var disposition = $('#dispostion :selected').map(function(){
  		return this.value
	}).get();
	if (campaign != "" && start != "" && end != "" && disposition != "") {
		document.filterform.action = 'qa.php?act=newexport&'+vf+'&disposition='+disposition;
    	document.filterform.submit();
	} else {
		alert("Please fill in all the required fields (*) and try again.");
	}
}
function jpplay(media)
{
    
    $("#jp").jPlayer({
        ready: function () {
          $(this).jPlayer("setMedia", {
            wav: media

          });
        },
        swfPath: "../../jquery/jplayer",
        supplied: "mp3"
      });
    $("#jp").jPlayer("play");
}
function jppause()
{
    $("#jp").jPlayer("pause");
}
function jpstop()
{
    $("#jp").jPlayer("stop");
}
function jpreset()
{
    $("#jp").jPlayer("play",0);
}
var gettinglead = false;
function getlead(leadid, start, end)
{
    if (!gettinglead)
        {
            gettinglead =true;
            $.ajax({
                url: "qa.php?act=getlead&leadid="+leadid+"&startdate="+start+"&enddate="+end,
                success: function(data){
                //$("#dispostion").html(data);
                        $("#container").append('<div id=msg style="display:none">'+data+'</div>');
                        $(".qacf").each(function(){
                            $(this).blur(function(){
                                var f = $(this).attr("name");
                                var v = $(this).val();
                                var l = $("#leadidval").val();
                                $.ajax({
                                    url:'qa.php?act=savecf',
                                    type: "POST",
                                    data: {
                                        "field":f,
                                        "value":v,
                                        "leadid":l
                                    }
                                });
                            });
                        });
                        $(".qasf").each(function(){
                            $(this).blur(function(){
                                var f = $(this).attr("name");
                                var v = $(this).val();
                                var l = $("#leadidval").val();
                                $.ajax({
                                    url:'qa.php?act=savesf',
                                    type: "POST",
                                    data: {
                                        "field":f,
                                        "value":v,
                                        "leadid":l
                                    }
                                });
                            });
                        });
                        var lt = $(".tolocaltime").html();
                        var d = epochtoutc(lt);
                        $(".tolocaltime").html(d.toLocaleString());

                        $("#msg").dialog({
                                modal:true,
                                minWidth:820,
                                minHeight:400,
                                close: function(){ $("#msg").remove();}
                                });
                        $('.dtpick').datetimepicker({
                            format: 'Y-m-d H:i'
                        });
                        $("#updatelead").submit(function()
                                {
                                        $(this).ajaxSubmit(); 
                                });
                        gettinglead = false;
                        /***************************/
                        /* ADDED BY Vincent Castro */
                        /***************************/
                        if ($("#disposition select").val() == "ScheduledCallback") {
                            disposelect( $("#disposition select").val() );
                        }
                        }
                    });
        }
	}
function projectchange()
	{
		var v = $("#projectid").val();
		$.ajax({
  				url: "qa.php?act=updatedispolist&projectid="+v,
  				success: function(data){
    	 			$("#dispostion").html(data);
					
  					}
				});
        getcustomdata(v);
	}
	function savelead()
	{
		$("#updatelead").ajaxSubmit(function(){
				$.blockUI({ 
            message: "Lead updated successfully!", 
            fadeIn: 700, 
            fadeOut: 700, 
            showOverlay: false, 
            centerY: true, 
			centerX: true,
            css: { 
                width: '350px', 
                border: 'none', 
                padding: '5px',
				
                backgroundColor: '#330066', 
                '-webkit-border-radius': '10px', 
                '-moz-border-radius': '10px', 
                opacity: .6, 
                color: '#fff' 
            }
        	});
			setTimeout($.unblockUI, 3000);
                        $("#msg").dialog("close");
			});
	}
function saveleadforqa() {
	var dispo = $("select[name=dispo]").val();
	var projid = $("input[name=projectid]").val();
	var leadid = $("#leadidval").val();
	$.ajax({
        url: 'qa.php?act=checkbookedslotdispo&dispo='+dispo+'&projid='+projid+'&leadid='+leadid,
        type: 'POST',
        success: function(resp){
	var status = $("#status").val();
	if (status == "approvedto") {
		$("#status").val("approved");
		$("#updatelead").ajaxSubmit(function(){
			$.blockUI({ 
	        message: "Lead updated successfully!", 
	        fadeIn: 700, 
	        fadeOut: 700, 
	        showOverlay: false, 
	        centerY: true, 
			centerX: true,
	        css: { 
	            width: '350px', 
	            border: 'none', 
	            padding: '5px',
				
	            backgroundColor: '#330066', 
	            '-webkit-border-radius': '10px', 
	            '-moz-border-radius': '10px', 
	            opacity: .6, 
	            color: '#fff' 
	        }
	    	});
			setTimeout($.unblockUI, 3000);
	        $("#msg").dialog("close");
		});
	} else {
		$("#updatelead").ajaxSubmit(function(){
			$.blockUI({ 
	        message: "Lead updated successfully!", 
	        fadeIn: 700, 
	        fadeOut: 700, 
	        showOverlay: false, 
	        centerY: true, 
			centerX: true,
	        css: { 
	            width: '350px', 
	            border: 'none', 
	            padding: '5px',
				
	            backgroundColor: '#330066', 
	            '-webkit-border-radius': '10px', 
	            '-moz-border-radius': '10px', 
	            opacity: .6, 
	            color: '#fff' 
	        }
	    	});
			setTimeout($.unblockUI, 3000);
	        $("#msg").dialog("close");
		});
	}
}
});
}
function printable(target)
{
     var orig = $(target).html();
     $(target+" input[type=text]").each(function(){
			
			$(this).parent().html('<span name="'+$(this).attr("name")+'" class="frominput">'+$(this).val()+'</span>');
			});
		$(target+" textarea").each(function(){
			$(this).parent().html('<span name="'+$(this).attr("name")+'" class="fromtextarea">'+$(this).val()+'</span>');
			});
		$(target+" select").each(function(){
			$(this).css("display","none");
			$('<span class="fromselect">'+$(this).val()+'</span>').appendTo($(this).parent());
			});
     var anew = $(target).html();
     $(target).html(orig);
     return anew;
}
function printdiv()
	{
            var orig = $("#msg_print").html();
		$("#msg_print input[type=text]").each(function(){
			
			$(this).parent().html('<span name="'+$(this).attr("name")+'" class="frominput">'+$(this).val()+'</span>');
			});
		$("#msg_print textarea").each(function(){
			$(this).parent().html('<span name="'+$(this).attr("name")+'" class="fromtextarea">'+$(this).val()+'</span>');
			});
		$("#msg_print select").each(function(){
			$(this).css("display","none");
			$('<span class="fromselect">'+$(this).val()+'</span>').appendTo($(this).parent());
			});
		$("#msg_print").print();
		$("#msg_print").html(orig);
	}
function approveto()
{
    var pid = $("#projectid").val();
    if (isNaN(pid))
    {
        alert("Campaign MUST be selected!");
    }
    else {
        $.ajax({
            url: 'qa.php?act=getclientcontacts&pid='+pid,
            type: 'get',
            success: function(resp){
                $("#dialogcontainer").html(resp);
                $("#dialogcontainer").dialog();
            }
        })
    }
}
function approvetolead(bid, pid)
{
    $.ajax({
        url: 'qa.php?act=getclientcontactsforlead&bid='+bid+'&pid='+pid,
        type: 'get',
        success: function(resp){
            $("#dialogcontainer").html(resp);
            $("#dialogcontainer").dialog();
        }
    })
}
function doassignto()
{
	var userid = <?php echo $uid?>;
	if (userid != 0 || userid != "") {
    var contactid = $("#assigntoclientcontact").val();
    var ct = 0;var bid = new Array();
    $("[name=bulkaction]").each(function(){
        if (this.checked)
            {
                bid[ct] = $(this).val();
                ct++;
            }
    });
    var cts = 0;
    $.ajax({
        url: 'qa.php?act=bulkstatusupdate&status=assignto&contactid='+contactid,
        type: 'POST',
        data: {
	            "bcids": bid,
	            "userid": userid
        },
        success: function(){
            var i;
            $("#dialogcontainer").dialog("destroy");
            for (i = 0;i < ct;++i)
                {
                    
                    $("span#status"+bid[i]).html("Approved");
                    $("span#status"+bid[i]).css("text-transform","capitalize");
                }
        }
    });
	} else if (userid == 0 || userid == "") {
		alert("Session expired. Please logout and re-login.");
	}
}
function doassigntolead(leadid)
{
	var userid = <?php echo $uid?>;
	var lid = leadid;
	var bid = [];
	bid.push(lid);
	if (userid != 0 || userid != "") {
		var contactid = $("#assigntoclientcontact").val();
	    $.ajax({
	        url: 'qa.php?act=bulkstatusupdate&status=assignto&contactid='+contactid,
	        type: 'POST',
	        data: {
	            "bcids": bid,
	            "userid": userid
	        },
	        success: function(){
	            $("#dialogcontainer").dialog("destroy");
	        }
	    });
	} else if (userid == 0 || userid == "") {
		alert("Session expired. Please logout and re-login.");
	}
}
function bulkqa(){
	var userid = <?php echo $uid?>;
	if (userid != 0 || userid != "") {
    var action = $("#bulkaction").val();
    if (action == 'approvedto')
    {
        approveto();
    } else if (action == 'callback') { /* ADDED BY Vincent Castro */
        transfercallback();
	    } else if (action == 'changedisposition') {
	        changedisposition();
    }
    else {
    var ct = 0;var bid = new Array();
    $("[name=bulkaction]").each(function(){
        if (this.checked)
            {
                bid[ct] = $(this).val();
                ct++;
            }
    });
    var cts = 0;
    if (action != '')
        {
    $.ajax({
        url: 'qa.php?act=bulkstatusupdate&status='+action,
        type: 'POST',
        data: {
	            "bcids": bid,
	            "userid": userid
        },
        success: function(){
            var i;
            for (i = 0;i < ct;++i)
                {
                    
                    $("span#status"+bid[i]).html(action);
                    $("span#status"+bid[i]).css("text-transform","capitalize");
	                }
	        }
	    });
	        }
	    }
	} else if (userid == 0 || userid == "") {
		alert("Session expired. Please logout and re-login.");
	}
}
function leadqabulk(leadid, pid){
	var userid = <?php echo $uid?>;
	var lid = leadid;
	var bid = [];
	bid.push(lid);
	if (userid != 0 || userid != "") {
		var action = $("#status").val();
	    if (action == 'approvedto') {
	        approvetolead(bid, pid);
	    } else if (action == 'callback') {
	        transfercallbacklead(bid, pid);
	    } else if (action == 'changedisposition') {
	        changedispositionlead(bid, pid);
                }
	    else {
		    if (action != '') {
			    $.ajax({
			        url: 'qa.php?act=bulkstatusupdate&status='+action,
			        type: 'POST',
			        data: {
			            "bcids": bid,
			            "userid": userid
			        },
			        success: function(){
        }
    });
			}
        }
	} else if (userid == 0 || userid == "") {
		alert("Session expired. Please logout and re-login.");
    }
}
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
function getcustomdata(pid, cvfields) {
	if (typeof pid === "undefined" ) {
		
	} else {
		$("#customdatatable").remove();
		$.ajax({
			url: "qa.php?act=getcustomdata&projectid="+pid+"&cvfields="+cvfields,
			success: function(data){
				$(".custom-control").before(data);
			}
		});
	}
}
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
function disposelect(data){
    if (data == "ScheduledCallback") {
        category = 'agent';
    } else {
        var category = $(data).find("option:selected").data('category');
    }
    // alert(category);
    $("#reassign_agent").remove();
    $("#oldagent").hide();
    if(category == 'agent'){
        var pid = $("#projectid").val();
        var userid = $("#assignedid").val();
        $.ajax({
            url: "qa.php?act=showagents&projectid="+pid+"&assigned="+userid,
            success: function(data){
                $("#tableheader").after(data);
            }
        });
    } else {
        $("#reassign_agent").remove();
        $("#oldagent").show();
    }
}
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
function transfercallback(){
    var pid = $("#projectid").val();
    if (isNaN(pid))
    {
        alert("Campaign MUST be selected!");
    } else {
        $.ajax({
            url: 'qa.php?act=showagentsbulk&projectid='+pid,
            type: 'get',
            success: function(resp){
                $("#dialogcontainer").html(resp);
                $("#dialogcontainer").dialog();
            }
        })
    }
}
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
function bulktransfercallback(data){
    var checkbox = $(".dataTable input[name=bulkaction]");
    var leadid = [];
    var ctr = 0;
    checkbox.each(function(){
        if(this.checked){
            leadid.push($(this).val());
            // console.log( this.closest("tr").find(".dispo") );
            if( $(this).closest("tr").find("td.dispo").html() != "ScheduledCallback"){
                ctr++;
            }
        }
    });

    if(ctr){
        alert("Only leads with callback can be transfer!");
         $("#dialogcontainer").dialog("close");
         $("#bulkaction").val(0);
    }

    var agentid = $(data).find("option:selected").val();

    if(leadid.length && agentid && ctr == 0){
        $.ajax({
            url: 'qa.php?act=transferagentsbulk',
            data: {'leadid': leadid, 'agentid': agentid},
            type: 'get',
            success: function(resp){
                location.reload();
            }
        })
    }
}

function togglecheckbox()
{
    if ($("#checkboxall").is(":checked"))
    {
        $("[name=bulkaction]").prop("checked",true);
    }
    else {
        $("[name=bulkaction]").prop("checked",false);
    }
}

function player_window(projectid, leadid, projectlinkurl)
{
    // theplayer = window.open('http://116.93.124.48/audioplayer.php?projectid='+projectid+'&leadid='+leadid, 'theplayer', 'titlebar=no,menubar=no,toolbar=no,resizable=no');
    // var player_url = 'http://116.93.124.48/audioplayer.php?projectid='+projectid+'&leadid='+leadid;
    var player_url = projectlinkurl+'?';

    $( "#recordingscontainer" ).html("<center><iframe width='100%' height='100%' src='"+ player_url + $.param( {'projectid':projectid, 'leadid':leadid} ) + "'></iframe></center>");

    $( "#recordingscontainer" ).dialog({
        title: "PLAY RECORDINGS",
        resizable: false,
        width: 400,
        height: 400,
        position: { my: "center", at: "center", of: window },
        modal: true,
        draggable: false,
        closeOnEscape: false,
        dialogClass: "no-close",
        buttons: {
            "Done": function() {
                $( this ).dialog( "close" );
                $( this ).dialog( "destroy" );
            }
        }
    });
}


$(document).ready(function(e) {
    /***************************/
    /* ADDED BY Vincent Castro */
    /***************************/
    $.urlParam = function(name){
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results==null){
           return null;
        }
        else{
           return results[1] || 0;
        }
    }
    var v = $("#projectid").val();
    if(v != "" && $.urlParam('act') != "search") getcustomdata(v);

    $(".cc-button").click(function(){
        var action = $(this).data('action');
        if(action == "selectall"){
            $(".vfs").prop('checked', true);
        } else if(action == "clearall"){
            $(".vfs").prop('checked', false);
        } else if(action == "savecustomview"){
            var viewfields = $(".vfs").serialize();
            var scvuserid = $("#scvuserid").val();							
		    $.ajax({																  		
		        url: "qa.php?act=applycustomizeview&"+viewfields+"&userid="+scvuserid,							  		
		        success: function(data){
		        	alert("Custom view saved.");
		        }								
		    });		
		} else if(action == "restoredefaultcustomizeview"){
            var viewfields = $(".vfs").serialize();
            var scvuserid = $("#scvuserid").val();							
		    $.ajax({																  		
		        url: "qa.php?act=restoredefaultcustomizeview&&userid="+scvuserid,							  		
		        success: function(data){
		        	alert("Restored default fields.");
					location.reload();
		        }								
		    });		
        }
    })
    /** end **/

    $('.dtpick').datetimepicker({
        format: 'Y-m-d H:i',
        step: 15
    });
    $(".dateinput").datepicker({ dateFormat: 'yy-mm-dd' });
	$("div#searchresults table").dataTable({
            "iDisplayLength": 50,
            "fnDrawCallback": function() {
                $(".bulk").click(function(e){
                    e.stopPropagation();
                });
            }
        });
        $(".dataTables_length").html('<select id="bulkaction" onchange="bulkqa()">'+
                '<option value="">Bulk Action</option>'+
                '<option value="approved">Approved</option>'+
                '<option value="approvedto">Approve To</option>'+
                '<option value="failed">Failed</option>'+
                '<option value="incomplete">Incomplete</option>'+
                '<option value="callback">Transfer Callback</option>'+
                '</select>');
	$('input[name=inactivecampaign]').change(function(){
		var scvuserid = $("#scvuserid").val();	
		if($('input[name=inactivecampaign]').is(':checked')){
	        $.ajax({																  		
		        url: "qa.php?act=showinactivecampaign&userid="+scvuserid,							  		
		        success: function(data){
		        	location.reload();
		        }								
		    });	
	    } else {
	        $.ajax({																  		
		        url: "qa.php?act=showactivecampaign&userid="+scvuserid,							  		
		        success: function(data){
		        	location.reload();
		        }								
		    });
	    }
	});    
});
</script>
</html>