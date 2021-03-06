<?php
session_start();
date_default_timezone_set($_SESSION['timezone']);

//error_reporting(E_ALL);
include "../../../dbconnect.php";
include "../../phpfunctions.php";
include "../../../classes/classes.php";
function nuform($num, $ch)
	{
		$ret = ($num / $ch) * 100;
		$ret2 = number_format($ret,2);
		return $ret2;
	}
function orz($num)
{
    if (strlen($num) < 1) {return '0';}
    return $num;
}
// Get Custom Report Setting
$customreportres = mysql_query("SELECT * FROM uiopt WHERE project_id = '".$_REQUEST["pid"]."' AND config = 'CustomReport' ORDER BY ts DESC LIMIT 1");
$customreportrow = mysql_fetch_assoc($customreportres);
$crlink = $customreportrow["value"];

// set mysql timezone
mysql_query("SET time_zone = '". $_SESSION['timezone'] ."'");

$bcid = getbcid();
$projects = getprojects($bcid,false);
$plist = $projects['list'];
$plistsql = $projects['sql'];
$lists = getlists('all',$bcid);
$act = $_REQUEST['act'];
$anameres = mysql_query("SELECT members.userid, memberdetails.afirst, alast from members left join memberdetails on members.userid = memberdetails.userid where members.bcid = $bcid");

while ($anamerow = mysql_fetch_assoc($anameres))
{
    $agents[$anamerow['userid']] = $anamerow['afirst'] . ' '. $anamerow['alast'];
}
$agents[0] = 'Unassigned';
if (!checkrights('reports'))
{
    echo "Permission Error.";
    exit;
}
if ($act == 'updatelist')
	{
		$projectid = $_REQUEST['pid'];
		$lists = getlists($projectid,$bcid);
		?>
        <select name="listid" id="listid">
    	<option value="all" selected="selected">All</option>
      	<?=$lists['options'];?>
    	</select>
        <?
		exit;
    }
if ($act == 'customreport') {
    $projectid = $_REQUEST['pid'];
    $customreportres = mysql_query("SELECT * FROM uiopt WHERE project_id = '".$_REQUEST['pid']."' AND config = 'CustomReport' ORDER BY ts DESC LIMIT 1");
    $customreportrow = mysql_fetch_assoc($customreportres);
    if ($customreportrow > 1) {
        $customreportlink = $customreportrow["value"];
        echo $customreportlink;
    } else {
        echo "false";
    }
    exit;
}
if ($act =='listview')
	{
		extract($_REQUEST);
		$list = getlists($projectid, $bcid);
		$q = "SELECT dispo, count(dispo) as counts from leads_raw where listid ";
		if ($listid == 'all')
			{
				$q.= " in (".$list['sql'].") ";
			}
		else {
				$q.= " = '$listid' ";
		}
		$q.= " group by dispo ";
		$headers[] = 'Disposition';
		$headers[] = 'Count';
		$res = mysql_query($q);
		while ($row = mysql_fetch_assoc($res))	
			{
				if (strlen($row['dispo']) < 1)
					{
						$row['dispo'] = 'Skipped';
					}
				$rows[] = $row;
				$xarr[] = $row['dispo'];
				$yarr[] = $row['counts'];
			}
		$file = "lists.xml";
		graph3d_single($xarr,$yarr,$file,"","","$listid",0);
		?>
        <table width="930">
        <tr>
        <td width="450"><?=tablegen($headers,$rows,"450");?></td><td width="480"><?=flashgraph("Column2D",$file,"480","350");?></td>
        </tr>
        </table>
        <?
		exit;
	}
if ($act == 'viewgraph')
{
    $labels = unserialize($_REQUEST['labels']);
    $values = unserialize($_REQUEST['values']);
    
    $file = 'summgraph'.$bcid.'.xml';
    graph3d_single($labels,$values,$file,"","","",0);
    echo flashgraph("Column2D",$file,"480","350");exit;
}
if ($act == 'summary' || ($_REQUEST['type'] == 'export' && $_REQUEST['view'] == 'summary'))
{
    
    $projid = $_REQUEST['projectid'];
    $listid = $_REQUEST['listid'];
    if ($projid == 'all')
    {
        $projectq = "in ($plistsql)";
        $projectqstat = "in (0,$plistsql)";
    }
    else {
        $projectq = "= $projid";
        $projectqstat = "in (0,$projid)";
        $pclients = projects::projectclients($bcid);
        $client = $pclients[$projid];
        }
    if ($listid != 'all')
    {
        $listq = "and listid = '".$listid."'";
    }
    extract($_REQUEST);
    if (strlen($start) < 1 || strlen($end)< 1) 
    {
        echo "Start and End Date must be selected!";
        exit;
    }
    $rangeq = "startepoch >= '".strtotime($start. "00:00:00")."' and startepoch <= '".strtotime($end." 23:59:59")."'";
    $qdispo = "SELECT systemdisposition,agentdisposition,count(agentdisposition) as counts from history where $rangeq and projectid $projectq $listq group by systemdisposition,agentdisposition";
    //var_dump($qdispo);
    $qres = mysql_query($qdispo);
    $totalcalls = 0;
    $totalsystemdispo = 0;
    $totalpotentials = 0;
    $totalappointmentset = 0;
    while ($row = mysql_fetch_assoc($qres)) {
        $totalcalls = $totalcalls + $row['counts'];
        if ($row['systemdisposition'] == 'ANSWER') {
            $totalanswered = $totalanswered + $row['counts'];
        } else if ($row['systemdisposition'] != 'ANSWER') {
        	if ($row['systemdisposition'] == 'BUSY') {
				$totalsystemdispo = $totalsystemdispo + 1;
            } elseif ($row['systemdisposition'] == 'CANCEL') {
				$totalsystemdispo = $totalsystemdispo + 1;
            } elseif ($row['systemdisposition'] == 'CHANUNAVAIL' || $row['systemdisposition'] == 'CONGESTION') {
				$totalsystemdispo = $totalsystemdispo + 1;
            } else {
				$totalsystemdispo = $totalsystemdispo + 1;
            }
        }
		
        if (strlen($row['agentdisposition']) > 0) {
            $dispos[$row['agentdisposition']] += $row['counts'];
            if ($row['agentdisposition'] == 'Appointment Set') {
                $totalpotentials = $totalpotentials + $row['counts'];
                $totalappointmentset = $totalappointmentset + $row['counts'];
            } elseif ($row['agentdisposition'] == 'DoNotCall') {
                $totalpotentials = $totalpotentials + $row['counts'];
            } elseif ($row['agentdisposition'] == 'Information Requested') {
                $totalpotentials = $totalpotentials + $row['counts'];
            } elseif ($row['agentdisposition'] == 'ScheduledCallback') {
                $totalpotentials = $totalpotentials + $row['counts'];
            }  elseif ($row['agentdisposition'] == 'Busy') {
                $totalpotentials = $totalpotentials + $row['counts'];
            } 
        } else {
            if ($row['systemdisposition'] == 'BUSY') {
                $dispos['Busy'] += $row['counts'];
            } elseif ($row['systemdisposition'] == 'CANCEL') {
                $dispos['No Answer'] += $row['counts'];
            } elseif ($row['systemdisposition'] == 'CHANUNAVAIL' || $row['systemdisposition'] == 'CONGESTION') {
                $dispos['CallFailed'] += $row['counts'];
            } else {
                $dispos['No Disposition'] += $row['counts'];
            }
        }
    }
    $headers = array();
    $headers[] = 'Disposition';
    $headers[] = 'Count';
    $headers[] = '% of Total Calls';
    foreach ($dispos as $dispo=>$value)
        {
            
            $rows[$dispo]['name'] = $dispo;
            $dgraph['labels'][] = $dispo;
            $rows[$dispo]['value']= $value;
            $dgraph['count'][] = $value;
            $p = ($value / $totalcalls) * 100;
            $percent = number_format($p,2);
            $rows[$dispo]['percent'] = $percent."%";
	}
    $disporeport = tablegen($headers,$rows,1024);
	// CLIENT DISPOSITION
    $crangeq = "epoch_timeofcall >= '".strtotime($start. " 00:00:00")."' and epoch_timeofcall <= '".strtotime($end. " 23:59:59")."'";
	$qcdispo = "SELECT client_statuses.statusname as clientdisposition, count(client_statuses.statusname) as counts FROM leads_done 
    		JOIN client_contact_leads ON client_contact_leads.leadid = leads_done.leadid 
    		JOIN client_statuses ON client_statuses.client_statusid = client_contact_leads.client_disposition 
    		WHERE $crangeq and projectid $projectq $listq group by client_statuses.statusname";
    $qcres = mysql_query($qcdispo);
    $ctotalcalls = 0;
    while ($row = mysql_fetch_assoc($qcres))
    {
        $ctotalcalls = $ctotalcalls + $row['counts'];
        $cdispos[$row['clientdisposition']] += $row['counts'];
    }
    $cheaders = array();
    $cheaders[] = 'Client Disposition';
    $cheaders[] = 'Count';
    $cheaders[] = '% of Total Client Disposition';
    foreach ($cdispos as $cdispo=>$value)
        {
            
            $crows[$cdispo]['name'] = $cdispo;
            $cdgraph['labels'][] = $cdispo;
            $crows[$cdispo]['value']= $value;
            $cdgraph['count'][] = $value;
            $p = ($value / $ctotalcalls) * 100;
            $percent = number_format($p,2);
            $crows[$cdispo]['percent'] = $percent."%";
	}
    $cdisporeport = tablegen($cheaders,$crows,1024);
    
    $agentq = "SELECT userid, count(userid) as counts from history where $rangeq and projectid $projectq $listq group by userid";
    $agentres = mysql_query($agentq);
    $headers = array();
    $rows = array();
    $headers[] = 'Agent';
    $headers[] = 'CallCount';
    $headers[] = '% of Total Calls';
    while ($row = mysql_fetch_assoc($agentres))
    {
        if ($row['userid'] != 0)
        {
        $rows[$row['userid']]['agent'] = $agents[$row['userid']];
        
        }
        else 
        {
            $rows[$row['userid']]['agent'] = 'Unassigned';
        }
        $alabels[] = $rows[$row['userid']]['agent'];
        $avalues[] = $row['counts'];
        $rows[$row['userid']]['callcount'] = $row['counts'];
        $p = ($row['counts'] / $totalcalls) * 100;
        $percent = number_format($p,2);
        $rows[$row['userid']]['percent'] = $percent."%";
    }
    $agentreport = tablegen($headers,$rows,1024);
    $headers = array();
    $rows = array();
    //List summary
    if ($listid == 'all')
    {
        $listqq = ' in (';
        $listlists = mysql_query("SELECT * from lists where projects $projectq");
        $listql = array();
        while ($llrow = mysql_fetch_assoc($listlists))
        {
            $listql[] = "'".$llrow['listid']."'";
        }
        $listqq .= implode(",",$listql);
        $listqq .= ') ';
    }
    
    else $listqq = "= '$listid'";
    //var_dump($listqq);
    $listrecres = mysql_query("SELECT listid, count(*) as 'ct' from leads_raw where listid $listqq group by listid");
    while ($row = mysql_fetch_assoc($listrecres))
    {
        $listrecs[$row['listid']] = $row['ct'];
    }
    $listquery = "SELECT listid as 'ListId',systemdisposition,count(*) as 'ct' from history where $rangeq and projectid $projectq $listq group by listid, systemdisposition";
    $listres = mysql_query($listquery);
    
    $headers[] = 'ListId';
    $headers[] = 'Loaded Records';
    $headers[] = 'Call Attempts';
    $headers[] = 'Calls Answered';
    $headers[] = '% Answered';
    $headers[] = 'Dials per Record';
    
    $listsumm = array();
    while ($listrow = mysql_fetch_assoc($listres))
    {
        $listsumm[$listrow['ListId']]['ListId'] = $listrow['ListId'];
        
        $listsumm[$listrow['ListId']]['Loaded Records'] = $listrecs[$listrow['ListId']];
        $total_['records'][$listrow['ListId']] = $listrecs[$listrow['ListId']];
        $listsumm[$listrow['ListId']]['Call Attempts'] += $listrow['ct'];
        $total['callattempts'] += $listrow['ct'];
        if ($listrow['systemdisposition'] == 'ANSWER')
        {
            $listsumm[$listrow['ListId']]['Calls Answered'] += $listrow['ct'];
            $total['answered'] += $listrow['ct'];
        }
        $percentanswered = ($listsumm[$listrow['ListId']]['Calls Answered'] / $listsumm[$listrow['ListId']]['Call Attempts']) * 100;
         $listsumm[$listrow['ListId']]["% Answered"] = number_format($percentanswered,2,'.','') . "%";
         $dialsperrecord = $listsumm[$listrow['ListId']]['Call Attempts'] / $listsumm[$listrow['ListId']]['Loaded Records'];
         $listsumm[$listrow['ListId']]['Dials per Record'] = number_format($dialsperrecord,2,".",'');
    }
    $listsumm['total']['ListId'] = '<b>Total</b>';
    $total['records'] = array_sum($total_['records']);
    $listsumm['total']['Loaded Records'] = $total['records'];
    $listsumm['total']['Call Attempts'] = $total['callattempts'];
    $listsumm['total']['Calls Answered'] = $total['answered'];
    $tpa = ($total['answered'] / $total['callattempts']) * 100;
    $listsumm['total']['% Answered'] = number_format($tpa,2,'.','') .'%';
    $dpr = $total['callattempts'] / $total['records'];
    $listsumm['total']['Dials per Record'] = number_format($dpr,2,'.','');
    foreach ($headers as $ll)
    {
        if ($ll != 'ListId') $llabels_l[] = $ll;
    }
    foreach ($listsumm['total'] as $key=>$vv)
    {
        if ($key != 'ListId') $lvalues_l[] = floatval($vv);
    }
    $llabels = serialize($llabels_l);
    $lvalues = serialize($lvalues_l);
    ob_start();
    tablegen2($headers, $listsumm,1024);
    $listrep=ob_get_contents();
    ob_end_clean();
    //$llabels = serialize
    $headers = array();
    $rows = array();
    $headers[] = '';
    $headers[] = '';
    $rows['total'] = array('l'=>'Total Call Attempts','v'=>$totalcalls);
    //$rows['answered'] = array('l'=>'Total Answered','v'=>$totalanswered);
    $rows['potentials'] = array('l'=>'Total Potentials','v'=>$totalpotentials);
    $p = ($totalpotentials / $totalcalls) * 100;
    //$lcp = ($totalanswered - $totalsystemdispo) / $totalanswered * 100;
    $lcp = ($totalappointmentset / $totalpotentials) * 100;
    $percent = number_format($p,2);
	$lcpercent = number_format($lcp,2);
    //$rows['ap'] = array('l'=>'Answered Percent','v'=>$percent."%");
    $rows['ap'] = array('l'=>'Potential Percent','v'=>$percent."%");
    //$rows['lcp'] = array('l'=>'Live Calls Percent','v'=>$lcpercent."%");
    $rows['lcp'] = array('l'=>'Appointment Set Conversion','v'=>$lcpercent."%");
    $genstats = tablegen($headers,$rows,500);
    $dlabels = serialize($dgraph['labels']);
    $dvalues = serialize($dgraph['count']);
	$cdlabels = serialize($cdgraph['labels']);
    $cdvalues = serialize($cdgraph['count']);
    $alabels = serialize($alabels);
    $avalues = serialize($avalues);
    //Status Summary
    if ($listid == 'all')
    {
        $listqqstat = ' in (';
        $listlists = mysql_query("SELECT * from lists where projects $projectq and active = 1");
        $listql = array();
        while ($llrow = mysql_fetch_assoc($listlists))
        {
            $listql[] = "'".$llrow['listid']."'";
        }
        $listqqstat .= implode(",",$listql);
        $listqqstat .= ') ';
    }
    
    else $listqqstat = "= '$listid'";
    $liststatquery = "SELECT lr.listid,lr.dispo, statuses.category, count(dispo) as ct from leads_raw lr "
            . "left join (SELECT category,statusname from statuses where projectid $projectqstat group by statusname) statuses on lr.dispo = statuses.statusname "
            . " where lr.listid $listqqstat  group by lr.listid, lr.dispo,statuses.category";
    $liststatres = mysql_query($liststatquery);
    while ($liststatrow = mysql_fetch_assoc($liststatres))
    {
        $liststats[$liststatrow["listid"]]['listid'] = $liststatrow["listid"];
        $scategory = $liststatrow["category"];
        $sdispo = $liststatrow["dispo"];
        if ($sdispo == 'NEW')
        {
            $liststats[$liststatrow["listid"]]['NEW'] += $liststatrow["ct"];
        }
        elseif ($scategory == 'final')
        {
            $liststats[$liststatrow["listid"]]['final'] += $liststatrow["ct"];
        }
        else {
            $liststats[$liststatrow["listid"]]['callable'] +=  $liststatrow["ct"];
        }
    }
    foreach ($liststats as $liststat)
    {
        $liststrow[$liststat['listid']]['listid'] = $liststat['listid'];
        $liststrow[$liststat['listid']]['new'] = orz($liststat['NEW']);
         $sttotal['new'] +=$liststat['NEW'];
        $pnew = nuform($liststat['NEW'],$listrecs[$liststat['listid']]);
        $liststrow[$liststat['listid']]['pnew'] = $pnew.'%';
        $liststrow[$liststat['listid']]['recallables']= orz($liststat['callable']);
        $sttotal['recallable'] += $liststat['callable'];
        $pcallable = nuform($liststat['callable'],$listrecs[$liststat['listid']]);
        $liststrow[$liststat['listid']]['pcallable'] = $pcallable.'%';
        $liststrow[$liststat['listid']]['final'] = orz($liststat['final']);
        $sttotal['final'] +=$liststat['final'];
        $pfinal = nuform($liststat['final'],$listrecs[$liststat['listid']]);
        $liststrow[$liststat['listid']]['pfinal'] = $pfinal.'%';
        $sttotal['lists'] += $listrecs[$liststat['listid']];
    }
    $liststrow['totals']['listid'] = '<b>Totals</b>';
    $liststrow['totals']['new'] = $sttotal['new'];
    $liststrow['totals']['pnew'] = nuform($sttotal['new'],$sttotal['lists']).'%';
    $liststrow['totals']['recallable'] = $sttotal['recallable'];
     $liststrow['totals']['precallable'] = nuform($sttotal['recallable'],$sttotal['lists']).'%';
    $liststrow['totals']['final'] = $sttotal['final'];
     $liststrow['totals']['pfinal'] = nuform($sttotal['final'],$sttotal['lists']).'%';
    $headers= array();
    $headers[] = 'ListId';
    $headers[] = 'NEW';
    $headers[] = '% NEW';
    $headers[] = 'Recallables';
    $headers[] = '% Recallables';
    $headers[] = 'Final';
    $headers[] = '% Final';
    $lslabels[] = 'NEW';
    $lslabels[] = 'Recallables';
    $lslabels[] = 'Final';
    $lsvalues[] = $liststrow['totals']['new'];
    $lsvalues[] = $liststrow['totals']['recallables'];
    $lsvalues[] = $liststrow['totals']['final'];
    $lslabels = serialize($lslabels);
    $lsvalues = serialize($lsvalues);
    $liststatsummary = tablegen($headers,$liststrow,1024);
    $disp = "
    <div id=apdiv><div id=\"mainsummary\"><p><b> General Statistics </b></p>
    ".$genstats."
    <p><b> Disposition Summary - <a href=\"#\" onclick=\"viewgraph('".urlencode($dlabels)."','".urlencode($dvalues)."');\"> View Graph</a> </b></p>
    
    ".$disporeport."</div>
    <p><b> Client Disposition Summary - <a href=\"#\" onclick=\"viewgraph('".urlencode($cdlabels)."','".urlencode($cdvalues)."');\"> View Graph</a> </b></p>
    
    ".$cdisporeport."</div>
        <div id=\"agentsummary\">
    <p><b> Agent Summary -  <a href=\"#\" onclick=\"viewgraph('".urlencode($alabels)."','".urlencode($avalues)."');\"> View Graph</a> </b>
    </p>".$agentreport."</div>
        <div id=\"listsummary\">
    <p><b> List Calls Summary - <a href=\"#\" onclick=\"viewgraph('".urlencode($llabels)."','".urlencode($lvalues)."');\"> View Graph</a></b>
    </p>".$listrep
            . "<p><b> List Status Summary - <a href=\"#\" onclick=\"viewgraph('".urlencode($lslabels)."','".urlencode($lsvalues)."');\"> View Graph</a></b> </p>".$liststatsummary."</div></div>";
//var_dump($liststatquery);
    if ($type == "export")
    {
        createdoc("excel",$disp,true,"summaryreport-$projid-$start-$end");
    }
    else {
        echo $disp;
        if ($client > 0) 
            {
               $repname = 'Campaign Performance Summary for '.projects::getprojectname($projectid).' ('.$start.' to '.$end.')';
                echo '<p><a href="#" onclick="exportsel(\''.$client.'\',\''.$repname.'\')"><b>Export to Client Reports</b></a></p><br><br>';
               
            }
        }
exit;
}
if ($act =='view')
	{
		$projid = $_REQUEST['projectid'];
                $listid = $_REQUEST['listid'];
		extract($_REQUEST);
                if (strlen($start) < 1 || strlen($end)< 1) 
                {
                    if ($type !='graph')
                    {
                        echo "Start and End Date must be selected!";
                        exit;
                    }
                }
                if ($view == 'byagent')
                {
                    $datetabclass = 'inactive';
                    $agenttabclass = 'active';
                    $agentrow = 'userid,';
                    $daterow = '';
                    $group = 'userid';
                    $dateheader = '<span id="agenth">Agent</span>';
                }
                if ($view == 'bydate')
                {
                    $datetabclass = 'active';
                    $agenttabclass = 'inactive';
                    $agentrow = '';
                    $daterow = ", substr(FROM_UNIXTIME(startepoch),1,10) as dates";
                    $group = 'substr(FROM_UNIXTIME(startepoch),1,10)';
                    $dateheader = '<span id="dateh">Date</span>';
                }
                    

		$q = "SELECT $agentrow systemdisposition,agentdisposition, count(agentdisposition) as counts $daterow from history where projectid ";
		if ($projid == 'all')
			{
				$q.= " in ($plistsql)";
			}
		else {
                    $q.= " = '$projid'";
                    $pclients = projects::projectclients($bcid);
                    $client = $pclients[$projid];
                }
                if ($listid != 'all')
                {
                    $q.= " and listid = '$listid'";
                }
		if ($type == 'graph')
			{
                            if ($view == 'bydate')
                            {
				$q .= " and substr(FROM_UNIXTIME(startepoch),1,10) = '$date'";
                            }
                            if ($view == 'byagent')
                            {
                                $q .= " and startepoch >= '".strtotime($start)."' and startepoch <= '".strtotime($end." 24:59:59")."'";
                                $q .= " and userid ='".$date."'";
                            }
			}
                        
		else $q .= " and startepoch >= '".strtotime($start)."' and startepoch <= '".strtotime($end." 24:59:59")."'";
		$q .= " group by $group,systemdisposition,agentdisposition ";
                //var_dump($q);
		$res = mysql_query($q);
		while ($row = mysql_fetch_assoc($res))
			{
                                $idispo = $row['agentdisposition'];
                                if ($row['agentdisposition'] == '') $idispo = getdispofromsystem ($row['systemdisposition']);
				if ($view == 'byagent')
                                {
                                    $data[$idispo][$row['userid']] += $row['counts'];
                                    $dates[$row['userid']] = $row['userid'];
                                     $callcount[$row['userid']] += $row['counts'];
                                }
                                else {
                                     $data[$idispo][$row['dates']] += $row['counts'];
                                $dates[$row['dates']]= $row['dates'];
                                $callcount[$row['dates']] += $row['counts'];
                                }
				$dispos[$idispo] = $idispo;
				
			}
		$headers[] = $dateheader;
                $totalrow['label'] = '<b>Total</b>';
		foreach ($dispos as $dispo)
			{
				$headers[] = '<div class="vertlabel">'.$dispo.'</div>';
			}
                //var_dump($agents);
		foreach ($dates as $date)
			{
                                if ($view == 'byagent')
                                {
                                    $rows[$date]['date'] = '<a href="#" onclick="showgraphagent(\''.$date.'\')">'.$agents[$date].'</a>';
                                }
				else $rows[$date]['date'] = '<a href="#" onclick="showgraph(\''.$date.'\')">'.$date.'</a>';
				foreach ($dispos as $dispo)
					{
						if (strlen($data[$dispo][$date]) > 0)
							{
								$entry = $data[$dispo][$date];
							}
						else $entry = "0";
						$rows[$date][$dispo] = $entry;
						$counts[] = $entry;
                                                $totalrow[$dispo] +=$entry;
					}
				
			}
		//var_dump $dispos;
		//echo $q;
                $ct = 1;        
                foreach ($totalrow as $t)
                {
                    if (strlen($t) < 1) $t = "0";
                    $totalrowf[$ct] = "<b>$t</b>";
                    $ct++;
                }
                $rows['total'] = $totalrowf;
		if ($type == 'graph')
			{
				$file = "callperfdail$bcid.xml";
                                if ($view == 'byagent')
                                {
                                    graph3d_single($dispos,$counts,$file,"","","Graph for ".$agents[$date],0);
                                }
				else graph3d_single($dispos,$counts,$file,"","","Graph for $date",0);
				$head['dispo'] = "Dispo";
				$head['count'] = "Count";
				$head['percalls'] = "% of Calls";
				//$head['perrecords'] = "% of Records";
				
				foreach ($dispos as $dispo)
					{
						$c = $data[$dispo][$date];
						$pcalls = $c / $callcount[$date];
						$pc = $pcalls * 100;
						$precs = $c / $totalrecords;
						$pr = $precs * 100;
						$r[$dispo]['dispo'] = $dispo;
						$r[$dispo]['c'] = $c;
						$r[$dispo]['numform'] = number_format($pc,2) . "%";
						//$r[$dispo]['numform2'] = number_format($pr,2) . "%";
						
					}
				//echo '<img src="../graphgen.php?size=medium-wide&xarr='.$xaxis.'&yarr='.$yaxis.'" width="480" height="350" alt="Loading Graph.. Please Wait" />';
				?><table width="930">
        <tr>
        <td width="450" valign="top">Total Calls:<?=$callcount[$date];?><br /><?=tablegen($head,$r,"450");?></td><td width="480"><?=flashgraph("Column2D",$file,"480","350");?></td>
        </tr>
        </table><?
			}
                
		elseif ($type == 'export')
			{
				$table = tablegen($headers,$rows);
				createdoc("excel",$table,true,"report$view-$projid-$start-$end");
			}
		else {
                    echo '<div id="apdiv'.$view.'">';
                    echo tablegen($headers,$rows,930);
                    echo '</div>';
                    if ($client > 0) 
                    {
                        $summ = $view == 'byagent' ? 'by Agents':'by Date';
                       $repname = 'Campaign Performance '.$summ.' for '.projects::getprojectname($projectid).' ('.$start.' to '.$end.')';
                        echo '<p><a href="#" onclick="exportviewtoclient(\''.$client.'\',\''.$repname.'\',\''.$view.'\')"><b>Export to Client Reports</b></a></p><br><br>';

                    }
                }
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script type="text/javascript" src="../../../jquery/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../../jquery/js/jquery-ui-1.8.12.custom.min.js"></script>
<script type="text/javascript" src="../../../jquery/js/blockui.js"></script>
<script type="text/javascript" src="../../../jquery/js/pleasewait.js"></script>
<link href="../../../jquery/css/redmond/jquery-ui-1.8.12.custom.css" rel="stylesheet" type="text/css" />
<link href="../cstyle.css" rel="stylesheet" type="text/css" />
<style>
.graphviewer {
	visibility:hidden;
}
#tabs .btab {
    float:left;
    border:#008080 solid 1px;
    padding: 10px;
    width: 80px;
}
.active {
    float:left;
     border-top:#000 solid 1px;
     border-right:#000 solid 1px;
     border-left:#000 solid 1px;
    border-bottom:#FFF solid 1px;
    background-color: #FFF;
    padding: 5px;
    width: 100px;
    z-index: 1;
    position:relative;
    
}
.inactive {
    float:left;
    border-top:#008080 solid 1px;
     border-right:#008080 solid 1px;
     border-left:#008080 solid 1px;
    border-bottom:#008080 solid 1px;
    background-color: #008080;
    padding: 5px;
    width: 100px;
    cursor:pointer;
}

#tabs #boundary {
    width: 100%;
    float:left;
    border: #008080 solid 1px;
    position:relative;
    top: -2px;
    z-index: 0;
}
.clear {
    clear:both;
}
#viewrep {
    
}
a.repbutton {
    border: 1px solid #16A6BD;
    color: #16A6BD;
    float: left;
    padding: 10px;
    width: 120px;
    background:#E0FFFF;
    border-radius:10px;
    text-decoration: none;
}
#exportrep {
    position: absolute;
    right: 0;
}
.vertlabel {
   padding:2px;
}
.tableitem:hover,.tableitem_:hover {
    background-color: #16A6BD;
}
</style>
</head>

<body>
<div id="container" style="width:auto">
<div id="header">
<img src="../../images/bclogo-small.png" />
<div id="reporttitle">Campaign Performance Report</div>

</div>
<hr />
<div id="query">
<form action="campperformance.php" method="get" name="filterform" id="filterform">
<input type="hidden" name="act" value="dosearch" />
<table width="320" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td width="80">Campaign</td>
    <td width="245"><select name="projectid" id="projectid" onChange="updatelist()">
    <option value="all" selected="selected">All</option>
      <?=$plist;?>
    </select></td>
    <td width="80">ListId</td>
    <td width="245"><div id="listselect"><select name="listid" id="listid">
    <option value="all" selected="selected">All</option>
      <?=$lists['options'];?>
    </select></div></td>
  </tr>
  <tr>
    <td>Date Start</td>
    <td><input type="text" name="start" class="dates" id="start" /></td>
  
    <td>Date End</td>
    <td><input type="text" name="end" class="dates" id="end"/></td>
    
  </tr>
  <tr><td colspan="2"><a href="#" onClick="viewrep()" id="viewrep">View</a> | <a href="#" onClick="exportrep()">Export</a></td></tr>
</table>
</form>

</div>
<div id="tabs">
    <div class="active" id="summtab" onclick="viewsummary()">Summary</div>
    <div class="inactive" id="datetab" onclick="viewrepdate()">By Date</div>
    <div class="inactive" id="agenttab" onclick="viewrepagent()">By Agent</div>
    <div class="clear"></div>
    <div id="boundary"></div>
</div>
<div class="clear"></div>
<div id="summary"></div>
<div id="results"></div>
<div id="resultsagent"></div>
</div>
<div id="graphviewer"></div>
<div id="summsel" style="width:200px; display:none">
    Exporting to Client Reports...<br>
    <input type="checkbox" class="partselection" id="includeagentsummary"> Include Agent Summary<br>
        <input type="checkbox" class="partselection" id="includelistsummary"> Include List Summary<br>
                <input type="button" id="donesel" class="jbut" value="Done">
</div>

</body>
<script>
$(function() {
		$( ".dates" ).datepicker({ dateFormat: 'yy-mm-dd' });
        $("#projectid").val(<?php echo $_REQUEST["pid"] ?>);
        updatelist();
	});
function showform()
	{
		$('#dateform').dialog({width:350,height:200, title:'Select Date Range'})
	}
function showlist()
	{
		$('#listform').dialog({width:350,height:200, title:'Select Date Range'});
	}
function updatelist()
	{
        var url      = window.location.href; 
		var proj = document.getElementById('projectid').value;
        $.ajax({
            url: "<?php echo $crlink; ?>?act=updatelist&pid="+proj,
            success: function(data){
            $('#listselect').html(data);
            }
        });
	}
function listrep()
{
	var proj = document.getElementById('projectid2').value;
	var listid = document.getElementById('listid').value;
	$.ajax({
  		//url: "campperformance.php?act=listview&projectid="+proj+"&type=list&listid="+listid,
  		url: "<?php echo $crlink; ?>?act=listview&type=list",
  		data: {
  			"projectid": proj,
  			"listid": listid
  		},
  		success: function(data){
    	 $('#results').html(data);
  	}
	});
}
var agentretr = true;
var dateretr = true;
var currenttab = 'summary';
function viewsummary()
{
    currenttab = 'summary';
    $("#resultsagent").hide();
    $('#results').hide();
    $("#summary").show();
    $("#datetab").attr("class","inactive");
        $("#agenttab").attr("class","inactive");
        $("#summtab").attr("class","active");
}
function viewrepdate()
{
    currenttab = 'bydate';
    $("#resultsagent").hide();
    $("#summary").hide();
    $('#results').show();
    $("#datetab").attr("class","active");
    $("#agenttab").attr("class","inactive");
    $("#summtab").attr("class","inactive");
    var proj = document.getElementById('projectid').value;
	var start = document.getElementById('start').value;
	var end = document.getElementById('end').value;
        var listid = document.getElementById('listid').value;
    if (dateretr == false)
        {
            $.ajax({
  		//url: "campperformance.php?act=view&projectid="+proj+"&listid="+listid+"&start="+start+"&end="+end+"&view=bydate",
  		url: "<?php echo $crlink; ?>?act=view&view=bydate",
  		data: {
  			"projectid": proj,
  			"listid": listid,
  			"start": start,
  			"end": end
  		},
  		success: function(data){
    	 $('#results').html(data);
         dateretr = true;
  	}
	});
        }
}
function viewrepagent()
{
        currenttab = 'byagent';
        var proj = document.getElementById('projectid').value;
	var start = document.getElementById('start').value;
	var end = document.getElementById('end').value;
        var listid = document.getElementById('listid').value;
        
        $('#results').hide();
        $("#summary").hide();
        $("#resultsagent").show();
        $("#datetab").attr("class","inactive");
        $("#agenttab").attr("class","active");
        $("#summtab").attr("class","inactive");
        if (agentretr == false)
            {
	$.ajax({
  		//url: "campperformance.php?act=view&projectid="+proj+"&listid="+listid+"&start="+start+"&end="+end+"&view=byagent",
  		url: "<?php echo $crlink; ?>?act=view&view=byagent",
  		data: {
  			"projectid": proj,
  			"listid": listid,
  			"start": start,
  			"end": end
  		},
  		success: function(data){
                agentretr = true;
                $('#resultsagent').html(data);
                    
  	}
	});
            }
            
}
function viewrep()
{
        agentretr = false;
        dateretr = false;
        currenttab = 'summary';
	var proj = document.getElementById('projectid').value;
	var start = document.getElementById('start').value;
	var end = document.getElementById('end').value;
        var listid = document.getElementById('listid').value;
	$.ajax({
  		//url: "campperformance.php?act=summary&projectid="+proj+"&listid="+listid+"&start="+start+"&end="+end,
  		url: "<?php echo $crlink; ?>?act=summary",
  		data: {
  			"projectid": proj,
  			"listid": listid,
  			"start": start,
  			"end": end
  		},
  		success: function(data){
    	 $('#summary').html(data);
         viewsummary();
  	}
	});
}
function exportrep()
{
        // var listid = document.getElementById('listid').value;
	// var proj = document.getElementById('projectid').value;
	// var start = document.getElementById('start').value;
	// var end = document.getElementById('end').value;
	var ff = $("#filterform").serialize();
  	var url = "<?php echo $crlink; ?>?act=view&"+ff+"&type=export&view="+currenttab;
	window.open(url);
  		
}
function showgraph(date)
{
	var proj = document.getElementById('projectid').value;
        var listid = document.getElementById('listid').value;
	$.ajax({
  		//url: "campperformance.php?act=view&projectid="+proj+"&listid="+listid+"&date="+date+"&type=graph&view=bydate",
  		url: "<?php echo $crlink; ?>?act=view&type=graph&view=bydate",
  		data: {
  			"projectid": proj,
  			"listid": listid,
  			"date": date
  		},
  		success: function(data){
    	 $('#graphviewer').html(data);
		 $('#graphviewer').dialog({width:950,height:400, title:'Graph Viewer'})
  	}
	});
}
function viewgraph(labels,values)
{
    $.ajax({
  		url: "<?php echo $crlink; ?>?act=viewgraph&labels="+labels+"&values="+values,
  		success: function(data){
                    $('#graphviewer').html(data);
                    $('#graphviewer').dialog({width:500,height:400, title:'Graph Viewer'})
  	}
	});
}
function showgraphagent(userid)
{
	var proj = document.getElementById('projectid').value;
        var listid = document.getElementById('listid').value;
        var start = document.getElementById('start').value;
	var end = document.getElementById('end').value;
	$.ajax({
  		//url: "campperformance.php?act=view&projectid="+proj+"&listid="+listid+"&date="+userid+"&type=graph&view=byagent&start="+start+"&end="+end,
  		url: "<?php echo $crlink; ?>?act=view&type=graph&view=byagent",
  		data: {
  			"projectid": proj,
  			"listid": listid,
  			"date": userid,
  			"start": start,
  			"end": end
  		},
  		success: function(data){
    	 $('#graphviewer').html(data);
		 $('#graphviewer').dialog({width:950,height:400, title:'Graph Viewer'})
  	}
	});
}
function exporttoclient(cid,repname)
{
    var body = $("#mainsummary").html();
    if ($("#includeagentsummary").prop('checked') ==true)
        {
            body += $("#agentsummary").html();
        }
    if ($("#includelistsummary").prop('checked') ==true)
        {
            body += $("#listsummary").html();
        }
    
    var texts = encodeURI(body);
    texts = encodeURIComponent(texts);
    $.ajax({
        url: '../../admin.php?act=savereport&cid='+cid+'&rname='+repname,
        type: 'POST',
        data: 'tex='+texts,
        success: function(){
            alert('Client Report Generated!');
        }
    })
}
function exportsel(cid,repname)
{
    $("#summsel").dialog();
    $(".jbut").button();
    $("#donesel").click(function(){
         exporttoclient(cid,repname);
         $("#summsel").dialog('close');
    })
}
function exportviewtoclient(cid,repname,view)
{
    var body = $("#apdiv"+view).html();
    var texts = encodeURI(body);
    texts = encodeURIComponent(texts);
    $.ajax({
        url: '../../admin.php?act=savereport&cid='+cid+'&rname='+repname,
        type: 'POST',
        data: 'tex='+texts,
        success: function(){
            alert('Client Report Generated!');
        }
    })
}
</script>