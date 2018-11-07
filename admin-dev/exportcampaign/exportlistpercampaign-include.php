<?php
require "../../classes/lists.php";
include "../../dbconnect.php";
require "phpfunctions-include.php";
ini_set("display_errors",'on');
error_reporting(1);
$projectid = $_POST["projectidexportlist"];
$multilistids= $_POST['listids'];
if (strpos($multilistids, ",")) {
	$multilist = explode(",", $multilistids);
} else {
	$multilist = $multilistids;
}
$vfs = array(
	'listid',
    'cname',
    'cfname',
    'clname',
    'title',
    'company',
    'address1',
    'address2',
    'suburb',
    'city',
    'state',
    'country',
    'zip',
    'phone',
    'altphone',
    'email',
    'comments',
    'status',
    'assigned',
    'industry',
    'sic',
    'dispo',
    'positiontitle',
    'mobile',
    'epoch_timeofcall',
    'epoch_callable'
);
// function arraycustomdata($projectid){
	// $res = mysql_query("SELECT customfields from projects where projectid = '$projectid'");
	// $row = mysql_fetch_assoc($res);
	// $row = json_decode($row['customfields']);
	// foreach ($row as $key => $value) {
		// $customarray[$key] = array($key, 0, 'custom');
	// }
	// return $customarray;
// }
//$customdataArray = arraycustomdata($projectid);
foreach ($viewfields as $key => $vf) {
    $viewfields[$key][1] = 0;
}
foreach ($vfs as $vf) {
    $viewfields[$vf][1] = 1;
}
//$viewfields = array_merge($viewfields, $customdataArray);
foreach ($viewfields as $key => $value) {
    if ($value[1] && !$value[2]) {
        $exportfields[] = $key;
    }
    if ($key == 'notes' && $value[1] == 1) {
        $isNotes = true;
    }
}
if($isNotes){
    $key = array_search('notes', $exportfields);
    unset($exportfields[$key]);
}

$records		= lists::listrecordsexportcampaignlists($projectid, $exportfields, $multilist);
$resprojects	= mysql_query("SELECT * from projects where projectid = '".$projectid."'");
$projectname	= mysql_fetch_assoc($resprojects);
$filename    		= str_replace(" ", "_", $projectname['projectname']) . ".xls";
$headers			= array();
$cdata				= $records['cdata'];
$cdheaders			= array();
$sdata				= $records['sdata'];
$sheaders			= array();
$headers["agent"]	= "Agent";

foreach ($exportfields as $hd) {
    $headers[$hd] = $hd;
}
$rows =& $records["records"];
$resmembers = mysql_query("SELECT *, memberdetails.afirst, memberdetails.alast, memberdetails.team from members left join memberdetails on members.userid = memberdetails.userid where bcid = 
".$projectname['bcid']." AND alast <> '' AND afirst <> '' ORDER BY alast,afirst,userlogin");
while ($rowmembers = mysql_fetch_assoc($resmembers)) {
	$allmembers[$rowmembers['userid']] = $rowmembers;
}

foreach ($rows as $r) {
    $rows[$r['leadid']]['Agent']    = $r['assigned'] == 0 ? '' : $allmembers[$r['assigned']]['afirst'] . ' ' . $allmembers[$r['assigned']]['alast'];
}

//CDATA
foreach ($cdata as $leadid => $customfields) {
    $customdata = json_decode($customfields['customfields'], true);
    foreach ($customdata as $key => $value) {    	
        $key                 = "CF_" . $key;
        $rows[$leadid][$key] = $value;
        $cdheaders[$key]     = $key;
    }
    unset($customdata);
}
unset($cdata);
unset($records['cdata']);

//SDATA
foreach ($sdata as $leadid => $customfields) {
    $customdata = json_decode($customfields['scriptjson'], true);
    foreach ($customdata as $key => $value) {    	
        $key                 = "SF_" . $key;
        $rows[$leadid][$key] = $value;
        $sheaders[$key]     = $key;
    }
    unset($customdata);
}
unset($sdata);
unset($records['sdata']);

//NOTES
if ($isNotes) {
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
foreach ($cdheaders as $ch) {
    $headers[$ch] = $ch;
}
foreach ($sheaders as $sh) {
    $headers[$sh] = $sh;
}
foreach ($noteheaders as $nh) {
    $headers[$nh] = $nh;
}
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=" . $filename);
header("Pragma: no-cache");
header("Expires: 0");
tablegen2($headers, $rows);
exit;
