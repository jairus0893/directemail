<?php
// date_default_timezone_set("Australia/Sydney");
$type = $_REQUEST['type'];
$id   = $_REQUEST['id'];
ini_set("display_errors", 'on');
error_reporting(1);
function searchrecords($bcid,$projectid,$disposition,$userid, $start, $end, $exfields = NULL) {
    $records = array();
    $leadids = array();
    $cdata = array();
    $sdata = array();
    if ($exfields == NULL)
    {
        $qf = "*";
    }
    else {
        $q = implode(",",$exfields);
        $qf = "leadid,$q";
    }
    if ($projectid == 'all')
    {
        $projs = array();
        $pidres = mysql_query("SELECT projectid from projects where bcid = '$bcid'");
        while ($pidrow = mysql_fetch_assoc($pidres))
        {
            $projs[] = $pidrow['projectid'];
        }
        $projectid = implode(",",$projs);
        unset($projs);
    }
	if (is_array($disposition))
		{
			foreach ($disposition as $disp)
				{
					$dispo_arr[] = "'".$disp."'";
				}
			$dispo_str = implode(",",$dispo_arr);
		}
	else
		{			
			$dispo_str = "'".$disposition."'";
		}
	if ($disposition == 'all')
		{
			$dispo_q = '';
		}
	else $dispo_q = " and dispo in ($dispo_str) ";
	
    $userq = '';
    if ($userid != 'all')
    {
        $userq = "and assigned = '$userid'";
    }
     $res = mysql_query("SELECT $qf, projectid, assigned from leads_done where projectid in ($projectid) and epoch_timeofcall >= ".strtotime($start)." 
     and epoch_timeofcall <= ".strtotime($end." 23:59:59")." $userq $dispo_q") or die(mysql_error());
    while ($row = mysql_fetch_assoc($res))
    {
        $records[$row['leadid']] = $row;
        $leadids[$row['leadid']] = $row['leadid'];
    }
    
    unset($res);
    $res2 = mysql_query("SELECT $qf from leads_raw where leadid in (".implode(',',$leadids).")");
    while ($row = mysql_fetch_assoc($res2))
    {
        foreach ($row as $field=>$value)
        {
            if (strlen($records[$row['leadid']][$field]) < 1 || !$records[$row['leadid']][$field])
            {
                $records[$row['leadid']][$field] = $value;

                }
        }
    }
    unset($res2);
    $dateres = mysql_query("SELECT * from dateandtime where leadid in (".implode(',',$leadids).")");
    while ($daterow = mysql_fetch_assoc($dateres))
    {
        $records[$daterow['leadid']]['DateSet'] = $daterow['dtime'];
    }
    $leadidlist = implode(",",$leadids);
   $res3 = mysql_query("SELECT * from leads_custom_fields where leadid in ($leadidlist)");
   if ($res3)
   {
   while ($row = mysql_fetch_assoc($res3))
   {
       $cdata[$row['leadid']] = $row;
   }
   }
   unset($res3);
   $res4 = mysql_query("SELECT * from scriptdata where leadid in ($leadidlist)");
   if ($res4)
   {
   while ($row = mysql_fetch_assoc($res4))
   {
       $sdata[$row['leadid']] = $row;
   }
   }
   unset($res4);
   $res5 = mysql_query("SELECT * from leads_notes where leadid in ($leadidlist)");
   if ($res5)
   {
   while ($row = mysql_fetch_assoc($res5))
   {
       $ndata[$row['leadid']] = $row;
   }
   }
   unset($res5);
   $ret["records"] = $records;
   $ret["cdata"] = $cdata;
   $ret["sdata"] = $sdata;
  $ret["ndata"] = $ndata;
    return $ret;
}
function projectnames($bcid) {
    $ret = array();
    $res = mysql_query("SELECT projectid,projectname,active from projects where bcid = '$bcid'");
    while ($row = mysql_fetch_assoc($res)) {
		if ($row['active'] == 1)
            $ret[$row['projectid']] = $row['projectname'];
        else
            $ret[$row['projectid']] = $row['projectname'] . " (DEACTIVATED)";
    }
    return $ret;
}
function getallmemberdetails($bcid) {
	$res = mysql_query("SELECT *, memberdetails.afirst, memberdetails.alast, memberdetails.team from members left join memberdetails on members.userid = memberdetails.userid where bcid = $bcid AND alast <> '' AND afirst <> '' ORDER BY alast,afirst,userlogin");
	while ($row = mysql_fetch_assoc($res)){
		$ret[$row['userid']] = $row;
	}
	return $ret;
}
if ($type == 'export') {
    foreach ($viewfields as $key => $value) {
        if ($value[1] && !$value[2]) {
            $exportfields[] = $key;
        }
        if ($value[1] && $value[2]) {
            $customfieldsheader[$key] = $key;
        }
        if ($key == 'notes' && $value[1] == 1) {
            $isNotes = true;
        }
    }    
    if($isNotes){
        $key = array_search('notes', $exportfields);
        unset($exportfields[$key]);
    }
    if ($type == 'export') {
        $projectnames = projectnames($bcid);
        $records      = searchrecords($bcid, $projectid, $multidispo, $agentid, $start, $end, $exportfields);
        $filename     = substr(md5(time()), -5);
        $filename .= ".xls";
    }
    $headers             = array();
    $cdata               = $records['cdata'];
    $cdheaders           = array();
    $sdata               = $records['sdata'];
    $sheaders            = array();
    $headers["campaign"] = "Campaign";
    $headers["agent"]    = "Agent";
    foreach ($exportfields as $hd) {
        $headers[$hd] = $hd;
    }
    $rows =& $records["records"];
    $allmembers = getallmemberdetails($bcid);
    foreach ($rows as $r) {
        $rows[$r['leadid']]['Campaign'] = $projectnames[$r['projectid']];
        $rows[$r['leadid']]['Agent']    = $r['assigned'] == 0 ? '' : $allmembers[$r['assigned']]['afirst'] . ' ' . $allmembers[$r['assigned']]['alast'];
    }
    foreach ($cdata as $leadid => $customfields) {
        $customdata = json_decode($customfields['customfields'], true);
        //unset($cdata[$record['leadid']]["customfields"]);
        foreach ($customdata as $key => $value) {
            if ($customfieldsheader[$key]) {
                /* ADDED BY Vincent Castro */
                $key                 = "cd_" . $key;
                $rows[$leadid][$key] = $value;
                $cdheaders[$key]     = $key;
            }
        }
        unset($customdata);
    }
    unset($cdata);
    unset($records['cdata']);
    if ($isNotes) {
        /* ADDED BY Vincent Castro */
        foreach ($records['ndata'] as $leadid => $nc) {
            $notedata          = json_decode($nc["note"], true);
            $key               = 'notes';
            $noteheaders[$key] = $key;
            foreach ($notedata as $nd) {
                $rows[$leadid][$key] .= '<br>' . $nd['user'] . "(" . $nd['timestamp'] . "):" . $nd['message'];
            }
            unset($notedata);
        }
    }
    foreach ($cdheaders as $cbh) {
        $headers[$cbh] = $cbh;
    }
    foreach ($sheaders as $cbh) {
        $headers[$cbh] = $cbh;
    }
    foreach ($noteheaders as $cbh) {
        $headers[$cbh] = $cbh;
    }
    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=" . $filename);
    header("Pragma: no-cache");
    header("Expires: 0");
    tablegen2($headers, $rows);
    exit;
}
?>