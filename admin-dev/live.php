<?php
$tstart = microtime(true);
session_start();
date_default_timezone_set($_SESSION['timezone']);
include "../dbconnect.php";
require "../classes/classes.php";
include "phpfunctions.php";
ini_set("display_errors","0");
error_reporting(0);

if (!checkrights('livemonitor'))
{
    exit;
}

$epochnow = time();
$bcid = $_SESSION['bcid'];
$clients = new clients($bcid);
$act = $_REQUEST['act'];
$sub = $_REQUEST["sub"];
if ($act == 'disc') {
		$u = $_REQUEST['id'];
		members::disconnect($u);
                exit;
	}
if (!$act) {
if ($bcid >= 1) {
	$liveres = mysql_query("SELECT liveagents.*, members.userlogin, members.bcid from liveagents left join members on liveagents.userid = members.userid where members.bcid = '$bcid'");
} else {
	$liveres = mysql_query("SELECT liveagents.*, members.userlogin, members.bcid from liveagents left join members on liveagents.userid = members.userid where bcid <= '1'");
}
$cts = 0;
while ($row = mysql_fetch_array($liveres)) {
	$users[$row['userid']] = $row;
	$cts++;
	$cc = $campaigns[$row['projectid']]['users'];
	if (strlen($cc) == 0) $campaigns[$row['projectid']]['users'] = 0;
	$campaigns[$row['projectid']]['active'] = 1;
	$campaigns[$row['projectid']]['users'] = $campaigns[$row['projectid']]['users'] + 1;
	}
 members::clearInactive($users);
$livedisp = '<table width="100%" cellspacing="0" cellpadding="0" id="liveuserstable">
				<thead><tr><th width="198" class="tableheadercenter" style="height:4em">Agent</th><th width="85" class="tableheadercenter">Extension</th>
				<th width="85" class="tableheadercenter">Status</th><th width="67" class="tableheadercenter">Duration</th><th width="67" class="tableheadercenter">Campaign</th>
				<th width="67" class="tableheadercenter">Logout</th></tr></thead><tbody>';
$ct = 0;
foreach($users as $user) {
		if ($user['status'])  {
			$getliveusers = mysql_query("SELECT statustimestamp FROM liveusers WHERE userid = '".$user['userid']."'");
			$getstatustimestamp = mysql_fetch_row($getliveusers);
			$datetime = time();
			$lastactivitydatetime = $getstatustimestamp[0];
			$getduration = $datetime - $lastactivitydatetime;
			$duration = gmdate("H:i:s", $getduration);
			$getliveusers = mysql_query("SELECT projectid_inbound FROM liveusers WHERE userid = '".$user['userid']."'");
			$getprojectid_inbound = mysql_fetch_row($getliveusers);
			$getprojects = mysql_query("SELECT projectname FROM projects WHERE projectid IN (".$getprojectid_inbound[0].")");
			while ($getprojectnames = mysql_fetch_array($getprojects)) {
			    $getprojectid_inbound_name = $getprojectnames[0];
			    $projectid_inbound_name .= $getprojectid_inbound_name . ",";
			}
			$projectiinboundname = rtrim($projectid_inbound_name, ",");
			if ($projectiinboundname != "") {
				$blendedcampaign = "(".$projectiinboundname.")";
			}
				if ($user['status'] == "incall" || $user['status'] == "oncall") {
					$style = 'style="background-color:#77ef77"';
				} else if ($user['status'] == "available" || $user['status'] == "preview") {
					$style = 'style="background-color:#f7f725"';
				} else if ($user['status'] == "ended" || $user['status'] == "paused") {
					$style = 'style="background-color:#f44242"';
				} else if ($user['status'] == "loggedin") {
					$style = 'style="background-color:#f2911a"';
				} else if ($user['status'] == "dialing") {
					$style = 'style="background-color:#b4f7b4"';
				}
			$livedisp.= '<tr><td class="datas"'.$style.'><a href="#" onclick="parent.chatWith(\''.$user['userlogin'].'\')">'.$user['afirst'].' '.$user['alast'].'</a></td><td class="datas"'.$style.'>';
	        $livedisp .= $user['extension'] ? $user['extension'].' (<a href="#" onclick="agentspy(\''.$user['extension'].'\', \'whisper\')">Whisper</a>)': '';
		    $livedisp .='<td class="datas"'.$style.'><span id="agentstat">'.$user['status'].'</span></td><td class="datas"'.$style.'><span id="agentduration">'.$duration.'</span></td><td class="datas"'.$style.'>'.$user['projectname'].' '.$blendedcampaign.'</a></td><td class="datas" style="text-decoration:none"><img src="icons/disconnect.png" alt="Logout Agent" onclick="lgt(\''.$user['userid'].'\')"></td></tr>';
		}
	}
$livedisp .= '</tbody></table>';
	
	$rpres = mysql_query("SELECT projectid from liveagents where bcid = '$bcid' group by projectid");
	$rpct = 0;
	while ($rprow = mysql_fetch_array($rpres)) {
		if ($rpct > 0 ) $projectidlist .= ",";
		$projectidlist .= "'".$rprow['projectid']."'";
		$rpct++;
	}
	$campres = mysql_query("select * from projects where projectid in ($projectidlist) and active = 1 and bcid = '$bcid' order by lastactive DESC, projectid DESC limit 20");
	//$campres = mysql_query("select * from projects where active = 1 and bcid = '$bcid' order by lastactive DESC, projectid DESC limit 20");
$pct = 0;
while ($camprow = mysql_fetch_array($campres)) {
		$campr[$camprow['projectid']] = $camprow;
		if ($pct > 0 ) $projectlist .= ",";
		$projectlist .= "'".$camprow['projectid']."'";
                $projectids[$camprow['projectid']] = $camprow['projectid'];
		$pct++;
	}
$datares = mysql_query("select listid, projects from lists where projects in ($projectlist) and lists.active = 1 and is_deleted = 0");
$inct = array();
while ($datarow = mysql_fetch_array($datares)) {
		if ($inct[$datarow['projects']] >0 ) $inlists[$datarow['projects']] .= ",";
		$inlists[$datarow['projects']] .= "'".$datarow['listid']."'";
		$lists[$datarow['listid']]['projectid'] = $datarow['projects'];
		$inct[$datarow['projects']]=$inct[$datarow['projects']] + 1;
	}
$ft = 0;
$statres = mysql_query("SELECT projectid, statusname from statuses where category = 'callable' and projectid in (0,$projectlist)");
while ($statrow = mysql_fetch_assoc($statres)) {
		$stats[$statrow['projectid']][$statrow['statusname']] = "'".$statrow['statusname']."'";
	}
$fstatres = mysql_query("SELECT projectid, statusname from statuses where category = 'final' and projectid in (0,$projectlist)");
while ($fstatrow = mysql_fetch_assoc($fstatres)) {
            $finalstats[$fstatrow['projectid']][$fstatrow['statusname']] = "'".$fstatrow['statusname']."'";
	}
$dropres = mysql_query("SELECT * from projects_droprate");
while ($droprow = mysql_fetch_assoc($dropres)) {
    $tans = $droprow['count_ans'];
    $tdrop = $droprow['count_drop'];
    if ($tans < 500) $tans = 500;
    $droprate[$droprow['projectid']] = ( $tdrop / ($tans + $tdrop) ) * 100;
	$d_droprate[$droprow['projectid']] = ( $droprow['d_drops'] / ($droprow['d_answers'] + $droprow['d_drops']) ) * 100;
	$d_ansmacs[$droprow['projectid']] = ( $droprow['d_ansmacs'] / ($droprow['d_answers'] + $droprow['d_drops'] + $droprow['d_ansmacs']) ) * 100;
	$d_waitavg[$droprow['projectid']] = ( $droprow['d_wait'] / ($droprow['d_answers']) );
}
$fres = mysql_query("SELECT * from filters group by projectid");
$activefilters = array();
while ($frow = mysql_fetch_assoc($fres)) {
    $activefilters[$frow['projectid']] = $activefilters[$frow['projectid']] + 1;
    $filters[$frow['projectid']][$frow['filterid']] = $frow['filterdata'];
}
foreach ($projectids as $tpid) {
	$params_res = mysql_query("INSERT INTO params SELECT 'proactiv.v_leads_raw_client', projects, listid, connection_id() FROM lists WHERE projects = '$tpid' AND active=1");
	if ($stats[$tpid]) {
		$recstatuses =  array_merge($stats[0],$stats[$tpid]);
	} else {
		$recstatuses = $stats[0];
	}
	if ($finalstats[$tpid]) {
		$finalstatuses_arr = array_merge($finalstats[0],$finalstats[$tpid]);
	} else {
		$finalstatuses_arr = $finalstats[0];
	}
	$finalstatuses = implode(",",$finalstatuses_arr);
	$recallstatuses = implode(",",$recstatuses);
	if (count($filters[$tpid]) > 0) {
	    $pfilters = " and (".implode(" and ",$filters[$tpid]).")";
	} else {
		$pfilters = '';
	}
	$recquery = "select count(*) as recallable from v_leads_done_client where hopper = 1 and dispo in ($recallstatuses) and dispo not in ($finalstatuses) and epoch_callable < $epochnow and phone > 0 $pfilters";
	$recres = mysql_query($recquery);
	while ($unrow = mysql_fetch_assoc($recres)) {
	    $campr[$tpid]['recallable'] = $campr[$tpid]['recallable'] + $unrow['recallable'];
	}
	$unres_qry = "select count(*) as 'unused' from v_leads_done_client where hopper = 0 and dispo in ($recallstatuses) and dispo not in ($finalstatuses) and epoch_callable < $epochnow and phone > 0 $pfilters";
	$unres = mysql_query($unres_qry);	
	while ($unrow = mysql_fetch_array($unres)) {
	    $campr[$tpid]['calledava'] = $campr[$tpid]['calledava'] + $unrow['unused'];
	}
	$recres = mysql_query("select listid, count(*) as recallable from v_leads_raw_client where hopper = 1 and dispo in ('Drop','NEW','ANSMAC', 'Busy', '') and epoch_callable < $epochnow and phone > 0 $pfilters group by listid");
	while ($unrow = mysql_fetch_array($recres)) {
		$campr[$tpid]['recallable'] = $campr[$tpid]['recallable'] + $unrow['recallable'];
	}
	$unres_qry = "select listid, count(*) as 'unused' from v_leads_raw_client where dispo = 'NEW' and hopper = 0 and epoch_callable < $epochnow  and phone > 0 $pfilters group by listid";
	$unres = mysql_query($unres_qry);	
	while ($unrow = mysql_fetch_array($unres)) {
		$campr[$tpid]['unused'] = $campr[$tpid]['unused'] + $unrow['unused'];
	}
	$usedavaquery = "select listid, count(*) as 'unused' from v_leads_raw_client where dispo != 'NEW' and hopper = 0 and dispo not in ($finalstatuses) and epoch_callable < $epochnow and phone > 0 group by listid";
	$unres = mysql_query($usedavaquery);	
	while ($unrow = mysql_fetch_array($unres)) {
		$campr[$lists[$unrow['listid']]['projectid']]['usedava'] = $campr[$lists[$unrow['listid']]['projectid']]['usedava'] + $unrow['unused'];
	}
	$params_res = mysql_query("DELETE FROM params WHERE connection_id = connection_id()");
}
$hopperres = mysql_query("SELECT projectid, count(*) as 'hcount' from hopper  where projectid in ($projectlist) and called = 0 group by projectid");
while ($hrow = mysql_fetch_array($hopperres)) {
		$campr[$hrow['projectid']]['hopper'] = $hrow['hcount'];
	}
$callmanres = mysql_query("SELECT projectid, count(*) as 'dials' from callman where projectid in ($projectlist) group by projectid");
while ($crow = mysql_fetch_array($callmanres)) {
		$campr[$crow['projectid']]['dials'] = $crow['dials'];
	}
$cmres = mysql_query("SELECT * from last_recycle");
while ($cmrow = mysql_fetch_assoc($cmres)) {
		$last_recycle[$cmrow['projectid']] = $cmrow['last_recycle'];
	}
$activecamp = '<table width="100%" id="liveMonitor" cellspacing="0" cellpadding="0" class="datatabs">
<thead>
<tr>
<th class="tableheadercenter">Campaign</th>
<th width="67" class="tableheadercenter" style="height:4em">Pacing</th>
<th width="67" class="tableheadercenter" style="height:4em">Available Records</th>
<th width="67" class="tableheadercenter" style="height:4em">Recyclable Records</th>
<th width="67" class="tableheadercenter" style="height:4em">Calls Being Made</th>
<th width="67" class="tableheadercenter" style="height:4em">Numbers Dialed Today</th>
<th width="67" class="tableheadercenter" style="height:4em">Active Users</th>
<th width="67" class="tableheadercenter" style="height:4em">On queue</th><th class="tableheadercenter"  style="height:4em"></th>
</tr>
</thead>
<tbody>';
$ctc = 0;
foreach($campr as $camp) {		
		$pid = $camp['projectid'];
		$lr = substr($last_recycle[$pid],0,10);
		if (strlen($lr) < 1) {
				$lr = 'Never';
			}
		if ($pid >0 ) {
		$unused = $camp['unused'] + $camp['usedava'] + $camp['calledava'];
                $untitle = "Unused: ".$camp['unused']."\r\n Used: ". $camp['usedava']."\r\n Called:". $camp['calledava'];
		$recy = $camp['recallable'];
		if ($camp['dials'] > 0) {
				$dstatus = 'Active';
		} else {
			$dstatus = 'Idle';
		}
		$activeusers = $campaigns[$pid]['users'];
		$total = $recallable + $recyclable;
		if (strlen($activeusers) < 1) $activeusers = 0;
		$currentDateEpoch = strtotime(date("Y-m-d"));
		$currentDateEpoch--;
		$callCount = mysql_query("SELECT count(*) as callcount FROM finalhistory WHERE startepoch > $currentDateEpoch AND projectid = $pid");
		$ccr = mysql_fetch_assoc($callCount);
                $callCountRow = $ccr['callcount'];		
        if ($callCountRow < 1) $callCountRow = "0";        
		$itemclass = ($ctc % 2) == 0 ? 'tableitem':'tableitem_';
                $af = $activefilters[$pid] ? $activefilters[$pid]:'0';
		$activecamp.= '<tr class="'.$itemclass.'">
		<td><a href="#" onclick="parent.manage(\''.$pid.'\')"><span class="lm_projectname">'.$camp['projectname'].'</span>('.$camp['dialmode'].')</a>
                    <br />
                    Client: <a href="#" onclick="clientdetails(\''.$camp['clientid'].'\')">'.$clients->getclientname($camp['clientid']).'</a><br/>
                    Answering Machine: <div class="bctooltip">'.number_format($d_ansmacs[$pid],0).'% <span class="bctooltiptext">TODAY\'s answering machine percentage of calls. Resets daily at 12:45 UTC Time</span></div>
					<br/>
                    Drop Rate: <div class="bctooltip">'.number_format($droprate[$pid],0).'% <span class="bctooltiptext">CUMULATIVE dialing drop rate since last list refresh</span></div>
						<div class="bctooltip">('.number_format($d_droprate[$pid],0).'%) <span class="bctooltiptext">TODAY\'s dialing drop rate. Resets daily at 12:45 UTC Time</span></div>
					<br/>
                    Waiting: <div class="bctooltip">'.number_format($d_waitavg[$pid],0).' seconds <span class="bctooltiptext">TODAY\'s dialing average waiting time. Resets daily at 12:45 UTC Time</span></div>
					<br/>
					Filters: '.$af.'</td>
		<td class="datas"><a href="#" onclick="parent.incpace(\''.$pid.'\');" title="Increase Pacing"><img src="icons/bullet_arrow_up.png"></a><br>'.$camp['dialpace'].'<br>
		<a href="#" onclick="parent.decpace(\''.$pid.'\');" title="Increase Pacing"><img src="icons/bullet_arrow_down.png"></a></td>		
		<td class="datas" title="'.$untitle.'">'.$unused.'</td>
		<td class="datas">'.$recy.'</td>		
		<!--<td class="datas">'.$dstatus.'</td>-->
		<td class="datas">'.$camp['dials'].'</td>
                    <td class="datas">'.$callCountRow .'</td>
		<td class="datas">'.$activeusers.'</td>
		<td class="datas">'.$camp['hopper'].'</td>
		<td class="datas" style="color:#0033CC;">
		<a href="#" class="refreshbuttons" onclick="parent.refreshleads(\''.$pid.'\');" title="Last Recycle: '.$lr.'"><img src="icons/refresh.png" alt="Recycle"></a>
		</td></tr>';
		}
                $ctc++;
	}
$activecamp.='</tbody></table>';
} else {
	if ($act == "refresh") {
		$rpres = mysql_query("SELECT projectid from liveagents where bcid = '$bcid' group by projectid");
		$rpct = 0;
		while ($rprow = mysql_fetch_array($rpres)){
			if ($rpct > 0 ) $projectidlist .= ",";
			$projectidlist .= "'".$rprow['projectid']."'";
			$rpct++;
		}
		$campres = mysql_query("select * from projects where projectid in ($projectidlist) and active = 1 and bcid = '$bcid' order by lastactive DESC, projectid DESC limit 20");
		$pct = 0;
		while ($camprow = mysql_fetch_array($campres)) {
				$campr[$camprow['projectid']] = $camprow;
				if ($pct > 0 ) $projectlist .= ",";
				$projectlist .= "'".$camprow['projectid']."'";
		                $projectids[$camprow['projectid']] = $camprow['projectid'];
				$pct++;
			}
		$datares = mysql_query("select listid, projects from lists where projects in ($projectlist) and lists.active = 1 and is_deleted = 0");
		$inct = array();
		while ($datarow = mysql_fetch_array($datares)) {
				if ($inct[$datarow['projects']] >0 ) $inlists[$datarow['projects']] .= ",";
				$inlists[$datarow['projects']] .= "'".$datarow['listid']."'";
				$lists[$datarow['listid']]['projectid'] = $datarow['projects'];
				$inct[$datarow['projects']]=$inct[$datarow['projects']] + 1;
			}
		$ft = 0;
		$statres = mysql_query("SELECT projectid, statusname from statuses where category = 'callable' and projectid in (0,$projectlist)");
		while ($statrow = mysql_fetch_assoc($statres)) {
				$stats[$statrow['projectid']][$statrow['statusname']] = "'".$statrow['statusname']."'";
			}
		$fstatres = mysql_query("SELECT projectid, statusname from statuses where category = 'final' and projectid in (0,$projectlist)");
		while ($fstatrow = mysql_fetch_assoc($fstatres)) {
		            $finalstats[$fstatrow['projectid']][$fstatrow['statusname']] = "'".$fstatrow['statusname']."'";
			}
		$dropres = mysql_query("SELECT * from projects_droprate");
		while ($droprow = mysql_fetch_assoc($dropres)) {
		    $tans = $droprow['count_ans'];
		    $tdrop = $droprow['count_drop'];
		    if ($tans < 500) $tans = 500;
		    $droprate[$droprow['projectid']] = ( $tdrop / ($tans + $tdrop) ) * 100;
			$d_droprate[$droprow['projectid']] = ( $droprow['d_drops'] / ($droprow['d_answers'] + $droprow['d_drops']) ) * 100;
			$d_ansmacs[$droprow['projectid']] = ( $droprow['d_ansmacs'] / ($droprow['d_answers'] + $droprow['d_drops'] + $droprow['d_ansmacs']) ) * 100;
			$d_waitavg[$droprow['projectid']] = ( $droprow['d_wait'] / ($droprow['d_answers']) );
		}
		$fres = mysql_query("SELECT * from filters group by projectid");
		$activefilters = array();
		while ($frow = mysql_fetch_assoc($fres)) {
		    $activefilters[$frow['projectid']] = $activefilters[$frow['projectid']] + 1;
		    $filters[$frow['projectid']][$frow['filterid']] = $frow['filterdata'];
		}
		foreach ($projectids as $tpid) {
			$params_res = mysql_query("INSERT INTO params SELECT 'proactiv.v_leads_raw_client', projects, listid, connection_id() FROM lists WHERE projects = '$tpid' AND active=1");
			if ($stats[$tpid]) {
				$recstatuses =  array_merge($stats[0],$stats[$tpid]);
			} else {
				$recstatuses = $stats[0];
			}
			if ($finalstats[$tpid]) {
				$finalstatuses_arr = array_merge($finalstats[0],$finalstats[$tpid]);
			} else {
				$finalstatuses_arr = $finalstats[0];
			}
			$finalstatuses = implode(",",$finalstatuses_arr);
			$recallstatuses = implode(",",$recstatuses);
			if (count($filters[$tpid]) > 0) {
			    $pfilters = " and (".implode(" and ",$filters[$tpid]).")";
			} else  {
				$pfilters = '';
			}
			$recquery = "select count(*) as recallable from v_leads_done_client where hopper = 1 and dispo in ($recallstatuses) and dispo not in ($finalstatuses) and epoch_callable < $epochnow and phone > 0 $pfilters";
			$recres = mysql_query($recquery);
			while ($unrow = mysql_fetch_assoc($recres)) {
			    $campr[$tpid]['recallable'] = $campr[$tpid]['recallable'] + $unrow['recallable'];
			}
			$unres_qry = "select count(*) as 'unused' from v_leads_done_client where hopper = 0 and dispo in ($recallstatuses) and dispo not in ($finalstatuses) and epoch_callable < $epochnow and phone > 0 $pfilters";
			$unres = mysql_query($unres_qry);	
			while ($unrow = mysql_fetch_array($unres)) {
			    $campr[$tpid]['calledava'] = $campr[$tpid]['calledava'] + $unrow['unused'];
			}
			$recres = mysql_query("select listid, count(*) as recallable from v_leads_raw_client where hopper = 1 and dispo in ('Drop','NEW','ANSMAC', 'Busy', '') and epoch_callable < $epochnow and phone > 0 $pfilters group by listid");
			while ($unrow = mysql_fetch_array($recres)) {
				$campr[$tpid]['recallable'] = $campr[$tpid]['recallable'] + $unrow['recallable'];
			}
			$unres_qry = "select listid, count(*) as 'unused' from v_leads_raw_client where dispo = 'NEW' and hopper = 0 and epoch_callable < $epochnow  and phone > 0 $pfilters group by listid";
			$unres = mysql_query($unres_qry);	
			while ($unrow = mysql_fetch_array($unres)) {
				$campr[$tpid]['unused'] = $campr[$tpid]['unused'] + $unrow['unused'];
			}
			$usedavaquery = "select listid, count(*) as 'unused' from v_leads_raw_client where dispo != 'NEW' and hopper = 0 and dispo not in ($finalstatuses) and epoch_callable < $epochnow and phone > 0 group by listid";
			$unres = mysql_query($usedavaquery);	
			while ($unrow = mysql_fetch_array($unres)) {
				$campr[$lists[$unrow['listid']]['projectid']]['usedava'] = $campr[$lists[$unrow['listid']]['projectid']]['usedava'] + $unrow['unused'];
			}
			$params_res = mysql_query("DELETE FROM params WHERE connection_id = connection_id()");
		}
		$hopperres = mysql_query("SELECT projectid, count(*) as 'hcount' from hopper  where projectid in ($projectlist) and called = 0 group by projectid");
		while ($hrow = mysql_fetch_array($hopperres)) {
				$campr[$hrow['projectid']]['hopper'] = $hrow['hcount'];
			}
		$callmanres = mysql_query("SELECT projectid, count(*) as 'dials' from callman where projectid in ($projectlist) group by projectid");
		while ($crow = mysql_fetch_array($callmanres)) {
				$campr[$crow['projectid']]['dials'] = $crow['dials'];
			}
		$cmres = mysql_query("SELECT * from last_recycle");
		while ($cmrow = mysql_fetch_assoc($cmres)) {
				$last_recycle[$cmrow['projectid']] = $cmrow['last_recycle'];
			}
		$activecamp = '<table width="100%" id="liveMonitor" cellspacing="0" cellpadding="0" class="datatabs">
		<thead>
		<tr>
		<th class="tableheadercenter">Campaign</th>
		<th width="67" class="tableheadercenter" style="height:4em">Pacing</th>
		<th width="67" class="tableheadercenter" style="height:4em">Available Records</th>
		<th width="67" class="tableheadercenter" style="height:4em">Recyclable Records</th>
		<th width="67" class="tableheadercenter" style="height:4em">Calls Being Made</th>
		<th width="67" class="tableheadercenter" style="height:4em">Numbers Dialed Today</th>
		<th width="67" class="tableheadercenter" style="height:4em">Active Users</th>
		<th width="67" class="tableheadercenter" style="height:4em">On queue</th>
		<th class="tableheadercenter" style="height:4em"></th>
		</tr>
		</thead>
		<tbody>';
		$ctc = 0;
		foreach($campr as $camp) {		
				$pid = $camp['projectid'];
				$lr = substr($last_recycle[$pid],0,10);
				if (strlen($lr) < 1) {
						$lr = 'Never';
					}
				if ($pid >0 ) {
				$unused = $camp['unused'] + $camp['usedava'] + $camp['calledava'];
		                $untitle = "Unused: ".$camp['unused']."\r\n Used: ". $camp['usedava']."\r\n Called:". $camp['calledava'];
				$recy = $camp['recallable'];
				if ($camp['dials'] > 0) {
						$dstatus = 'Active';
				} else {
					$dstatus = 'Idle';
				}
				$activeusers = $campaigns[$pid]['users'];
				$total = $recallable + $recyclable;
				if (strlen($activeusers) < 1) $activeusers = 0;
				$currentDateEpoch = strtotime(date("Y-m-d"));
				$currentDateEpoch--;
				$callCount = mysql_query("SELECT count(*) as callcount FROM finalhistory WHERE startepoch > $currentDateEpoch AND projectid = $pid");
				$ccr = mysql_fetch_assoc($callCount);
		                $callCountRow = $ccr['callcount'];		
		        if ($callCountRow < 1) $callCountRow = "0";        
				$itemclass = ($ctc % 2) == 0 ? 'tableitem':'tableitem_';
		                $af = $activefilters[$pid] ? $activefilters[$pid]:'0';
				$activecamp.= '
				<tr class="'.$itemclass.' projectid-'.$pid.'">
				<td>
				<a href="#" onclick="parent.manage(\''.$pid.'\')"><span class="lm_projectname">'.$camp['projectname'].'</span>('.$camp['dialmode'].')</a>
		        <br />
							Client: <a href="#" onclick="clientdetails(\''.$camp['clientid'].'\')">'.$clients->getclientname($camp['clientid']).'</a>
							<br/>
		                    Answering Machine: <div class="bctooltip">'.number_format($d_ansmacs[$pid],0).'% <span class="bctooltiptext">TODAY\'s answering machine percentage of calls. Resets daily at 12:45 UTC Time</span></div>
							<br/>
		                    Drop Rate: <div class="bctooltip">'.number_format($droprate[$pid],0).'% <span class="bctooltiptext">CUMULATIVE dialing drop rate since last list refresh</span></div>
								<div class="bctooltip">('.number_format($d_droprate[$pid],0).'%) <span class="bctooltiptext">TODAY\'s dialing drop rate. Resets daily at 12:45 UTC Time</span></div>
							<br/>
		                    Waiting: <div class="bctooltip">'.number_format($d_waitavg[$pid],0).' seconds <span class="bctooltiptext">TODAY\'s dialing average waiting time. Resets daily at 12:45 UTC Time</span></div>
							<br/>
							Filters: '.$af.'</td>
				<td class="datas">
				<a href="#" onclick="parent.incpace(\''.$pid.'\');" title="Increase Pacing"><img src="icons/bullet_arrow_up.png"></a><br>'.$camp['dialpace'].'<br>
				<a href="#" onclick="parent.decpace(\''.$pid.'\');" title="Increase Pacing"><img src="icons/bullet_arrow_down.png"></a></td>		
				<td class="datas" title="'.$untitle.'">'.$unused.'</td>
				<td class="datas">'.$recy.'</td>		
				<!--<td class="datas">'.$dstatus.'</td>-->
				<td class="datas">'.$camp['dials'].'</td>
		                    <td class="datas">'.$callCountRow .'</td>
				<td class="datas">'.$activeusers.'</td>
				<td class="datas">'.$camp['hopper'].'</td>
				<td class="datas" style="color:#0033CC;">
				<a href="#" class="refreshbuttons" onclick="parent.refreshleads(\''.$pid.'\');" title="Last Recycle: '.$lr.'"><img src="icons/refresh.png" alt="Recycle"></a>
				</td></tr>';
				}
		                $ctc++;
			}
		$activecamp.='</tbody></table>';
	}
	if ($act == "showidlecampaign") {
		if ($sub == "showidle") {
			$rpres = mysql_query("SELECT projectid from liveagents where bcid = '$bcid' group by projectid");
			$rpct = 0;
			while ($rprow = mysql_fetch_array($rpres)) {
				if ($rpct > 0 ) $projectidlist .= ",";
				$projectidlist .= "'".$rprow['projectid']."'";
				$rpct++;
			}
			if (!empty($projectidlist)) {
				$campres = mysql_query("SELECT * FROM projects where projectid NOT IN ($projectidlist) AND active = 1 AND bcid = '$bcid' ORDER BY lastactive DESC, projectid DESC limit 20");
			} else {
				$campres = mysql_query("SELECT * FROM projects where active = 1 AND bcid = '$bcid' ORDER BY lastactive DESC, projectid DESC limit 20");
			}
			$pct = 0;
			while ($camprow = mysql_fetch_array($campres)) {
					$campr[$camprow['projectid']] = $camprow;
					if ($pct > 0 ) $projectlist .= ",";
					$projectlist .= "'".$camprow['projectid']."'";
			                $projectids[$camprow['projectid']] = $camprow['projectid'];
					$pct++;
				}
			$datares = mysql_query("select listid, projects from lists where projects in ($projectlist) and lists.active = 1 and is_deleted = 0");
			$inct = array();
			while ($datarow = mysql_fetch_array($datares)) {
					if ($inct[$datarow['projects']] >0 ) $inlists[$datarow['projects']] .= ",";
					$inlists[$datarow['projects']] .= "'".$datarow['listid']."'";
					$lists[$datarow['listid']]['projectid'] = $datarow['projects'];
					$inct[$datarow['projects']]=$inct[$datarow['projects']] + 1;
				}
			$ft = 0;
			$statres = mysql_query("SELECT projectid, statusname from statuses where category = 'callable' and projectid in (0,$projectlist)");
			while ($statrow = mysql_fetch_assoc($statres)) {
					$stats[$statrow['projectid']][$statrow['statusname']] = "'".$statrow['statusname']."'";
				}
			$fstatres = mysql_query("SELECT projectid, statusname from statuses where category = 'final' and projectid in (0,$projectlist)");
			while ($fstatrow = mysql_fetch_assoc($fstatres)){
			            $finalstats[$fstatrow['projectid']][$fstatrow['statusname']] = "'".$fstatrow['statusname']."'";
				}
			$dropres = mysql_query("SELECT * from projects_droprate");
			while ($droprow = mysql_fetch_assoc($dropres)) {
			    $tans = $droprow['count_ans'];
			    $tdrop = $droprow['count_drop'];
			    if ($tans < 500) $tans = 500;
			    $droprate[$droprow['projectid']] = ( $tdrop / ($tans + $tdrop) ) * 100;
				$d_droprate[$droprow['projectid']] = ( $droprow['d_drops'] / ($droprow['d_answers'] + $droprow['d_drops']) ) * 100;
				$d_ansmacs[$droprow['projectid']] = ( $droprow['d_ansmacs'] / ($droprow['d_answers'] + $droprow['d_drops'] + $droprow['d_ansmacs']) ) * 100;
				$d_waitavg[$droprow['projectid']] = ( $droprow['d_wait'] / ($droprow['d_answers']) );
			}
			$fres = mysql_query("SELECT * from filters group by projectid");
			$activefilters = array();
			while ($frow = mysql_fetch_assoc($fres)) {
			    $activefilters[$frow['projectid']] = $activefilters[$frow['projectid']] + 1;
			    $filters[$frow['projectid']][$frow['filterid']] = $frow['filterdata'];
			}
			foreach ($projectids as $tpid) {
				$params_res = mysql_query("INSERT INTO params SELECT 'proactiv.v_leads_raw_client', projects, listid, connection_id() FROM lists WHERE projects = '$tpid' AND active=1");
				if ($stats[$tpid]) {
					$recstatuses =  array_merge($stats[0],$stats[$tpid]);
				} else {
					$recstatuses = $stats[0];
				}
				if ($finalstats[$tpid]) {
					$finalstatuses_arr = array_merge($finalstats[0],$finalstats[$tpid]);
				} else {
					$finalstatuses_arr = $finalstats[0];
				}
				$finalstatuses = implode(",",$finalstatuses_arr);
				$recallstatuses = implode(",",$recstatuses);
				if (count($filters[$tpid]) > 0) {
				    $pfilters = " and (".implode(" and ",$filters[$tpid]).")";
				} else {
					$pfilters = '';
				}
				$recquery = "select count(*) as recallable from v_leads_done_client where hopper = 1 and dispo in ($recallstatuses) and dispo not in ($finalstatuses) and epoch_callable < $epochnow and phone > 0 $pfilters";
				$recres = mysql_query($recquery);
				while ($unrow = mysql_fetch_assoc($recres)) {
				    $campr[$tpid]['recallable'] = $campr[$tpid]['recallable'] + $unrow['recallable'];
				}
				$unres_qry = "select count(*) as 'unused' from v_leads_done_client where hopper = 0 and dispo in ($recallstatuses) and dispo not in ($finalstatuses) and epoch_callable < $epochnow and phone > 0 $pfilters";
				$unres = mysql_query($unres_qry);	
				while ($unrow = mysql_fetch_array($unres)) {
				    $campr[$tpid]['calledava'] = $campr[$tpid]['calledava'] + $unrow['unused'];
				}
				$recres = mysql_query("select listid, count(*) as recallable from v_leads_raw_client where hopper = 1 and dispo in ('Drop','NEW','ANSMAC', 'Busy', '') and epoch_callable < $epochnow and phone > 0 $pfilters group by listid");
				while ($unrow = mysql_fetch_array($recres)) {
					$campr[$tpid]['recallable'] = $campr[$tpid]['recallable'] + $unrow['recallable'];
				}
				$unres_qry = "select listid, count(*) as 'unused' from v_leads_raw_client where dispo = 'NEW' and hopper = 0 and epoch_callable < $epochnow  and phone > 0 $pfilters group by listid";
				$unres = mysql_query($unres_qry);	
				while ($unrow = mysql_fetch_array($unres)) {
					$campr[$tpid]['unused'] = $campr[$tpid]['unused'] + $unrow['unused'];
				}
				$usedavaquery = "select listid, count(*) as 'unused' from v_leads_raw_client where dispo != 'NEW' and hopper = 0 and dispo not in ($finalstatuses) and epoch_callable < $epochnow and phone > 0 group by listid";
				$unres = mysql_query($usedavaquery);	
				while ($unrow = mysql_fetch_array($unres)) {
					$campr[$lists[$unrow['listid']]['projectid']]['usedava'] = $campr[$lists[$unrow['listid']]['projectid']]['usedava'] + $unrow['unused'];
				}
				$params_res = mysql_query("DELETE FROM params WHERE connection_id = connection_id()");
			}
			$hopperres = mysql_query("SELECT projectid, count(*) as 'hcount' from hopper  where projectid in ($projectlist) and called = 0 group by projectid");
			while ($hrow = mysql_fetch_array($hopperres)) {
					$campr[$hrow['projectid']]['hopper'] = $hrow['hcount'];
				}
			$callmanres = mysql_query("SELECT projectid, count(*) as 'dials' from callman where projectid in ($projectlist) group by projectid");
			while ($crow = mysql_fetch_array($callmanres)) {
					$campr[$crow['projectid']]['dials'] = $crow['dials'];
				}
			$cmres = mysql_query("SELECT * from last_recycle");
			while ($cmrow = mysql_fetch_assoc($cmres)) {
					$last_recycle[$cmrow['projectid']] = $cmrow['last_recycle'];
				}
			$activecamp = '<table width="100%" id="liveMonitor" cellspacing="0" cellpadding="0" class="datatabs">
			<thead>
			<tr>
			<th class="tableheadercenter">Campaign</th>
			<th width="67" class="tableheadercenter" style="height:4em">Pacing</th>
			<th width="67" class="tableheadercenter" style="height:4em">Available Records</th>
			<th width="67" class="tableheadercenter" style="height:4em">Recyclable Records</th>
			<th width="67" class="tableheadercenter" style="height:4em">Calls Being Made</th>
			<th width="67" class="tableheadercenter" style="height:4em">Numbers Dialed Today</th>
			<th width="67" class="tableheadercenter" style="height:4em">Active Users</th>
			<th width="67" class="tableheadercenter" style="height:4em">On queue</th><th class="tableheadercenter"  style="height:4em"></th>
			</tr>
			</thead>
			<tbody>';
			$ctc = 0;
			foreach($campr as $camp) {		
					$pid = $camp['projectid'];
					$lr = substr($last_recycle[$pid],0,10);
					if (strlen($lr) < 1) {
							$lr = 'Never';
						}
					if ($pid >0 ) {
					$unused = $camp['unused'] + $camp['usedava'] + $camp['calledava'];
			                $untitle = "Unused: ".$camp['unused']."\r\n Used: ". $camp['usedava']."\r\n Called:". $camp['calledava'];
					$recy = $camp['recallable'];
					if ($camp['dials'] > 0) {
							$dstatus = 'Active';
						} else {
							$dstatus = 'Idle';
						}
					$activeusers = $campaigns[$pid]['users'];
					$total = $recallable + $recyclable;
					if (strlen($activeusers) < 1) $activeusers = 0;
					$currentDateEpoch = strtotime(date("Y-m-d"));
					$currentDateEpoch--;
					$callCount = mysql_query("SELECT count(*) as callcount FROM finalhistory WHERE startepoch > $currentDateEpoch AND projectid = $pid");
					$ccr = mysql_fetch_assoc($callCount);
			                $callCountRow = $ccr['callcount'];		
			        if ($callCountRow < 1) $callCountRow = "0";        
					$itemclass = ($ctc % 2) == 0 ? 'tableitem':'tableitem_';
			                $af = $activefilters[$pid] ? $activefilters[$pid]:'0';
					$activecamp.= '<tr class="'.$itemclass.' projectid-'.$pid.'">
					<td><a href="#" onclick="parent.manage(\''.$pid.'\')"><span class="lm_projectname">'.$camp['projectname'].'</span>('.$camp['dialmode'].')</a>
			                    <br />
			                    Client: <a href="#" onclick="clientdetails(\''.$camp['clientid'].'\')">'.$clients->getclientname($camp['clientid']).'</a><br/>
			                    Answering Machine: <div class="bctooltip">'.number_format($d_ansmacs[$pid],0).'% <span class="bctooltiptext">TODAY\'s answering machine percentage of calls. Resets daily at 12:45 UTC Time</span></div>
								<br/>
			                    Drop Rate: <div class="bctooltip">'.number_format($droprate[$pid],0).'% <span class="bctooltiptext">CUMULATIVE dialing drop rate since last list refresh</span></div>
									<div class="bctooltip">('.number_format($d_droprate[$pid],0).'%) <span class="bctooltiptext">TODAY\'s dialing drop rate. Resets daily at 12:45 UTC Time</span></div>
								<br/>
			                    Waiting: <div class="bctooltip">'.number_format($d_waitavg[$pid],0).' seconds <span class="bctooltiptext">TODAY\'s dialing average waiting time. Resets daily at 12:45 UTC Time</span></div>
								<br/>
								Filters: '.$af.'</td>
					<td class="datas"><a href="#" onclick="parent.incpace(\''.$pid.'\');" title="Increase Pacing"><img src="icons/bullet_arrow_up.png"></a><br>'.$camp['dialpace'].'<br>
					<a href="#" onclick="parent.decpace(\''.$pid.'\');" title="Increase Pacing"><img src="icons/bullet_arrow_down.png"></a></td>		
					<td class="datas" title="'.$untitle.'">'.$unused.'</td>
					<td class="datas">'.$recy.'</td>		
					<!--<td class="datas">'.$dstatus.'</td>-->
					<td class="datas">'.$camp['dials'].'</td>
			                    <td class="datas">'.$callCountRow .'</td>
					<td class="datas">'.$activeusers.'</td>
					<td class="datas">'.$camp['hopper'].'</td>
					<td class="datas" style="color:#0033CC;">
					<a href="#" class="refreshbuttons" onclick="parent.refreshleads(\''.$pid.'\');" title="Last Recycle: '.$lr.'"><img src="icons/refresh.png" alt="Recycle"></a>
					</td></tr>';
					}
			                $ctc++;
				}
			$activecamp.='</tbody></table>';
		}
	}
	if ($act == "refreshagentstat") {
		if ($bcid >= 1) {
		$liveres = mysql_query("SELECT liveagents.*, members.userlogin, members.bcid from liveagents left join members on liveagents.userid = members.userid where members.bcid = '$bcid'");
		} else {
			$liveres = mysql_query("SELECT liveagents.*, members.userlogin, members.bcid from liveagents left join members on liveagents.userid = members.userid where bcid <= '1'");
		}
		$cts = 0;
		while ($row = mysql_fetch_array($liveres)) {
			$users[$row['userid']] = $row;
			$cts++;
			}
		 members::clearInactive($users);
		$livedisp = '<table width="100%" cellspacing="0" cellpadding="0" id="liveuserstable">
						<thead><tr><th width="198" class="tableheadercenter" style="height:4em">Agent</th><th width="85" class="tableheadercenter">Extension</th>
						<th width="85" class="tableheadercenter">Status</th><th width="67" class="tableheadercenter">Duration</th><th width="67" class="tableheadercenter">Campaign</th>
						<th width="67" class="tableheadercenter">Logout</th></tr></thead><tbody>';
		$ct = 0;
		foreach($users as $user) {
				if ($user['status'])  {
					$getliveusers = mysql_query("SELECT statustimestamp FROM liveusers WHERE userid = '".$user['userid']."'");
					$getstatustimestamp = mysql_fetch_row($getliveusers);
					$datetime = time();
					$lastactivitydatetime = $getstatustimestamp[0];
					$getduration = $datetime - $lastactivitydatetime;
					$duration = gmdate("H:i:s", $getduration);
					$getliveusers = mysql_query("SELECT projectid_inbound FROM liveusers WHERE userid = '".$user['userid']."'");
					$getprojectid_inbound = mysql_fetch_row($getliveusers);
					$getprojects = mysql_query("SELECT projectname FROM projects WHERE projectid IN (".$getprojectid_inbound[0].")");
					while ($getprojectnames = mysql_fetch_array($getprojects)) {
					    $getprojectid_inbound_name = $getprojectnames[0];
					    $projectid_inbound_name .= $getprojectid_inbound_name . ",";
					}
					$projectiinboundname = rtrim($projectid_inbound_name, ",");
					if ($projectiinboundname != "") {
						$blendedcampaign = "(".$projectiinboundname.")";
					}
					if ($user['status'] == "incall" || $user['status'] == "oncall") {
						$style = 'style="background-color:#77ef77"';
					} else if ($user['status'] == "available" || $user['status'] == "preview") {
						$style = 'style="background-color:#f7f725"';
					} else if ($user['status'] == "ended" || $user['status'] == "paused") {
						$style = 'style="background-color:#f44242"';
					} else if ($user['status'] == "loggedin") {
						$style = 'style="background-color:#f2911a"';
					} else if ($user['status'] == "dialing") {
						$style = 'style="background-color:#b4f7b4"';
					}
					$livedisp.= '<tr><td class="datas"'.$style.'><a href="#" onclick="parent.chatWith(\''.$user['userlogin'].'\')">'.$user['afirst'].' '.$user['alast'].'</a></td><td class="datas"'.$style.'>';
			        $livedisp .= $user['extension'] ? $user['extension'].' (<a href="#" onclick="agentspy(\''.$user['extension'].'\', \'whisper\')">Whisper</a>)': '';
			        $livedisp .='<td class="datas"'.$style.'><span id="agentstat">'.$user['status'].'</span></td><td class="datas"'.$style.'><span id="agentduration">'.$duration.'</span></td><td class="datas"'.$style.'>'.$user['projectname'].' '.$blendedcampaign.'</a></td><td class="datas" style="text-decoration:none"><img src="icons/disconnect.png" alt="Logout Agent" onclick="lgt(\''.$user['userid'].'\')"></td></tr>';
				}
			}
		$livedisp .= '</tbody></table>';
	}
}
if (!$act) {
?>
<style>
	#loading {
	    z-index: 100;
	    position:absolute;
	    top:0px;
	    left:0px;
	    width: 100%;
	    height: 100%;
	    text-align:center;
	    overflow: hidden;
	    background:rgba(255,255,255,0.5);
	}
.bctooltipsic {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black;
}

.bctooltipsic .bctooltiptextsic {
	font-size: 0.7em;
    visibility: hidden;
    width: 170px;
    background-color: #3366cc;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px 0;
    position: absolute;
    z-index: 1;
    top: 125%;
    left: 50%;
    margin-left: -60px;
    opacity: 0;
    transition: opacity 1s;
}
.bctooltipsic:hover .bctooltiptextsic {
    visibility: visible;
    opacity: 1;
}
</style>
	<div id="loading" style="display:none">    
	<img style="margin-top:200px;" src="loading_big.gif" alt="loading" />
	<br/><br/>
	<h1 style="color:#8B7637;">Loading Please Wait..</h1>                
</div>
<div class="apptitle">Live Monitor&nbsp;&nbsp;<input type="checkbox" id="idleprojectid" name="idleprojectid" disabled="disabled" onclick="idleprojectid()">&nbsp;</input>
<div class="bctooltipsic">
		<span style="font-size: 0.7em">Show Idle Campaigns</span>
		<span class="bctooltiptextsic">Ticking the box will show ONLY idle campaigns. Unticking will show ONLY the active campaigns that agents are calling.</span>
	</div>
</div>
<div id="activecamp"  align="left" style="position:relative;float:left;width:63%; min-width: 750px; padding-right:2%">
    <?php echo $activecamp;?>
</div>
<div id="liveusers"  align="left" style="position:relative;float:left;width:35%">
    <?php echo $livedisp;?>
</div>
<?php
} else {
    $ret['liveusers'] = $livedisp;
    $ret['activecamp'] = $activecamp;
    $tend = microtime(true);
    $elapse = $tend - $tstart;
    $ret['elapse'] = $elapse;
    echo json_encode($ret);
}
?>