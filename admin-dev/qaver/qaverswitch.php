<?php
session_start();

$agents = getagentnames();
if ($act == 'getstatusoption')
{
    $res = mysql_query("SELECT options from statuses where statusid = '".$_REQUEST['statusid']."' ");
    $row = mysql_fetch_assoc($res);
    if (strlen($row['options']) < 1) echo 'none';
    else echo $row['options'];
    exit;
}
if ($act == 'selectslot')
{
    $leadid = $_REQUEST['leadid'];
    $slotid = $_REQUEST['slotid'];
    $opt = $_REQUEST['opt'];
    $now = time();
    if ($opt == 'move')
    {
        mysql_query("update client_contact_slots set leadid = '', taken = 0 where leadid = $leadid and slotstart > $now");
    }
    mysql_query("update client_contact_slots set leadid = $leadid, taken= 1 where slotid = $slotid");
    mysql_query("update leads_done set epoch_callable = $now where leadid = $leadid");
    mysql_query("update leads_raw set epoch_callable = $now where leadid = $leadid");
    exit;
}
if ($act == 'cslots')
{
    //$ispres = mysql_query("select * from projects where projectid = '$pid';");
    //$project = mysql_fetch_assoc($ispres);
    $clientid = $_REQUEST['clientid'];
    $leadid = $_REQUEST['leadid'];
    $headers[] = "Contact";
    $headers[] = "Date";
    $headers[] = "Start";
    $headers[] = "End";
    $headers[] = "Status";
    $ctr = 0;
    $cslots = getallbyparams("client_contact_slots","where clientid = '".$clientid."' and slotstart > ".time()." order by slotstart ASC");
    if (count($cslots) > 0) $doslots = true;
    $ccontacts = get("client_contacts","client_contactid");
    foreach ($cslots as $cslot)
            {
                    $pdate = date("Y-m-d H:i:s",$cslot['slotstart']);
                    $rows[$ctr]['contact'] = $ccontacts[$cslot['client_contactid']]['firstname']. ' '.$ccontacts[$cslot['client_contactid']]['lastname'];
                    $rows[$ctr]['date'] = date("Y-m-d",$cslot['slotstart']);
                    $rows[$ctr]['start'] = date("H:i:s",$cslot['slotstart']);
                    $rows[$ctr]['end'] = date("H:i:s",$cslot['slotend']);
                    $status = '<a href="#" onclick="selectslot(\''.$leadid.'\',\''.$cslot['slotid'].'\',\'\')">Add New<br><a href="#" onclick="selectslot(\''.$leadid.'\',\''.$cslot['slotid'].'\',\'move\')">Move - Cancel Previous</a>';
                    if ($cslot['taken'] > 0)
                            {
                                    $status = '<span style="color:red">taken</span>';
                            }
                    $rows[$ctr]['status'] = ucfirst($status);
                    $ctr++;
            }
    echo '<a href="#" onClick="usecal()">Use Calendar</a>';
    echo tablegen($headers,$rows,600,'','datatabslot');
    exit;
}
if ($act == 'dialer')
{
    $sub = $_REQUEST['sub'];
    if ($_SESSION['adminext'] < 1)
    {
        echo "setext";
        exit;
    }    
    $dialer = new dialer($_SESSION['adminext'],$_REQUEST['leadid']);
    $dialer->$sub();
    exit;
}
if ($act == 'bulkstatusupdate')
{
    $bcids = $_REQUEST['bcids'];
	$userid = $_REQUEST['userid'];
    $status = $_REQUEST['status'];
    $subaction = '';
    if ($status == 'assignto')
    {
        $status = 'approved';
        $subaction = 'assignto';
        $ato = $_REQUEST['contactid'];
    }
    foreach ($bcids as $dib)
    {
        mysql_query("UPDATE leads_done set status = '$status' where leadid = '$dib'");
        if ($status == 'approved')
        {
            $res = mysql_query("SELECT leadid from client_contact_leads where leadid = $dib");
             if ($subaction == 'assignto') {
                    if (mysql_num_rows($res) == 0){
                        mysql_query("insert into client_contact_leads set leadid = $dib, client_contactid = $ato, client_disposition = 1, approvedby = $userid");
                        }
                    else {
                        mysql_query("update client_contact_leads set client_contactid = $ato where leadid = $dib");
                    }
            }
            else {
                if (mysql_num_rows($res) == 0){
                mysql_query("insert into client_contact_leads set leadid = $dib, client_disposition = 1, approvedby = $userid");
                }               
            }
        }
    }
    exit;
}
if ($act == 'getclientcontacts')
{
    $pid = $_REQUEST['pid'];
    $client = projects::projectclient($bcid, $pid);
    $contacts = clients::getclientcontacts($client['clientid']);
    foreach ($contacts as $contact)
    {
        $coptions .= '<option value="'.$contact['client_contactid'].'">'.$contact['firstname'].' '.$contact['lastname'].'</option>';
    }
    ?>
Client Contact:<select name="assigntoclientcontact" id="assigntoclientcontact" onchange="doassignto()"><option></option>
    <?php echo $coptions;?>
</select>
<?php
    exit;
}
if ($act == 'getclientcontactsforlead')
{
    $pid = $_REQUEST['pid'];
    $bid = $_REQUEST['bid'];
    $client = projects::projectclient($bcid, $pid);
    $contacts = clients::getclientcontacts($client['clientid']);
    foreach ($contacts as $contact)
    {
        $coptions .= '<option value="'.$contact['client_contactid'].'">'.$contact['firstname'].' '.$contact['lastname'].'</option>';
    }
    ?>
Client Contact:<select name="assigntoclientcontact" id="assigntoclientcontact" onchange="doassigntolead(<?php echo $bid?>)"><option></option>
    <?php echo $coptions;?>
</select>
<?php
    exit;
}
if ($act == 'savesf')
{
    extract($_POST);
    $res = mysql_query("SELECT * from scriptdata where leadid = '$leadid'");
    $row = mysql_fetch_assoc($res);
    $cf = json_decode($row['scriptjson'],true);
    if ($cf && count($row) > 0)
    {
        foreach ($cf as $key=>$val)
        {
            if ($key == $field) $cf[$key] = $value;
        }
        mysql_query("UPDATE scriptdata set scriptjson = '".json_encode($cf)."' where leadid = '$leadid'");
    }
    else {
        $cf = array();
        $cf[$field] = $value;
        if (count($row) > 0)
        {
            mysql_query("UPDATE scriptdata set scriptjson = '".json_encode($cf)."' where leadid = '$leadid'");            
        }
        else mysql_query("INSERT into scriptdata set scriptjson = '".json_encode($cf)."', leadid = '$leadid'");
    }
    exit;
}
if ($act == 'savecf')
{
    extract($_POST);
    $res = mysql_query("SELECT * from leads_custom_fields where leadid = '$leadid'");
    $row = mysql_fetch_assoc($res);
    $cf = json_decode($row['customfields'],true);
    if ($cf && count($row) > 0)
    {
        foreach ($cf as $key=>$val)
        {
            if ($key == $field) $cf[$key] = $value;
        }
        mysql_query("UPDATE leads_custom_fields set customfields = '".json_encode($cf)."' where leadid = '$leadid'");
    }
    else {
        $cf = array();
        $cf[$field] = $value;
        if (count($row) > 0)
        {
            mysql_query("UPDATE leads_custom_fields set customfields = '".json_encode($cf)."' where leadid = '$leadid'");            
        }
        else mysql_query("INSERT into leads_custom_fields set customfields = '".json_encode($cf)."', leadid = '$leadid'");
    }
    exit;
}
if ($act =='dl')
	{
		pushrecord($_REQUEST['file']);
		exit();
	}
if ($act == 'savelead')
	{
            $dib = $_REQUEST['leadid'];
            $res = mysql_query("SELECT leadid from client_contact_leads where leadid = $dib");
                    if (mysql_num_rows($res) == 0){
                        mysql_query("insert into client_contact_leads set leadid = $dib, client_contactid = $ato, client_disposition = 1");
                        }
                    else {
                        mysql_query("update client_contact_leads set client_contactid = $ato where leadid = $dib");
                    }
		savelead($_REQUEST['leadid']);
		exit();
    }
    if ($act == 'saveleadforqa')
	{
            $dib = $_REQUEST['leadid'];
            $res = mysql_query("SELECT leadid from client_contact_leads where leadid = $dib");
                    if (mysql_num_rows($res) == 0){
                        mysql_query("insert into client_contact_leads set leadid = $dib, client_contactid = $ato, client_disposition = 1");
                        }
                    else {
                        mysql_query("update client_contact_leads set client_contactid = $ato where leadid = $dib");
                    }
		saveleadforqa($_REQUEST['leadid']);
		exit();
	}
if ($act =='updatedispolist')
	{
		if ($_REQUEST['projectid'] != 'all')
			{
			$d = dispolist($_REQUEST['projectid']);
			}
		else {
			$d = dispolist('all');
		}
		$disp = createdropdown($d,"statusname","statusname");
		echo $disp;
		exit();
	}
if ($act == 'export')
{
    extract($_POST);

    /***************************/
    /* ADDED BY Vincent Castro */
    /***************************/
    $vfs = $_REQUEST['viewfields'];
	$customdataArray = arraycustomdata($_REQUEST['projectid']); /* ADDED BY Vincent Castro */
    foreach ($viewfields as $key=>$vf) {
        $viewfields[$key][1] = 0;
    }
    $viewfields = array_merge($viewfields,$customdataArray); /* ADDED BY Vincent Castro */
    // print_r($viewfields);
    foreach ($vfs as $vf) {
        $viewfields[$vf][1] = 1;
    }

    $_REQUEST['type'] = 'search';
    include "../export.php";
}
if ($act == 'search' || $act == 'newexport') {
    extract($_POST);
    $vfs             = $_REQUEST['viewfields'];
	$multidisposition= $_REQUEST['disposition'];
	if (strpos($multidisposition, ",")) {
		$multidispo = explode(",", $multidisposition);
	} else {
		$multidispo= $multidisposition;
	}
	
	if($_REQUEST['projectid'] != 'all'){
        $customdataArray = arraycustomdata($_REQUEST['projectid']);
    }
    foreach ($viewfields as $key => $vf) {
        $viewfields[$key][1] = 0;
    }
    if($_REQUEST['projectid'] != 'all'){
        $viewfields = array_merge($viewfields, $customdataArray);
    }
    foreach ($vfs as $vf) {
        $viewfields[$vf][1] = 1;
    }
    $projects = projectlist($bcid);
    if ($projectid == 'all') {
        foreach ($projects as $project) {
            $p[] = $project['id'];
        }
        $inpid = implode(",", $p);
    } else
        $inpid = $projectid;
    
    $recres = mysql_query("SELECT * from recordinglog where projectid in ($inpid)");
    while ($recrow = mysql_fetch_assoc($recres)) {
        $rt                        = explode("_", $recrow['filename']);
        $reclid                    = $rt[0];
        $recordinglog[$reclid]     = $recordinglog[$reclid] ? $recordinglog[$reclid] + 1 : 1;
        $recordingproject[$reclid] = $recrow['projectid'];
    }
    
    $results = listrecords($projectid, $multidispo, $start, $end, $datetype);
    if ($act != 'newexport') {
        $headers['0'] = '<input type="checkbox" id="checkboxall" onclick="togglecheckbox()">';
    }
	if ($act == 'newexport') {
        $headers['0'] = "Campaign";
    	$headers['01'] = "Agent";
	}
    $headers['02'] = 'Date';
    $headers['1']  = 'QA Status';
	$headers['1a'] = 'Approved By';
	$headers['1b'] = 'Approved To';
	$headers['1c'] = 'Assigned By';
	$headers['1d'] = 'Assigned To';
	$headers['2']  = 'Client Disposition';
    $headers['2a'] = 'Disposition';
    $headers['3']  = 'Agent';
    $headers['4']  = 'Phone';
    $headers['4a'] = 'AltPhone';
    $headers['4b'] = 'Mobile';
    $headers['5']  = 'Name';
    $headers['6']  = 'Company';
    $headers['6a'] = 'Email';
    $headers['6b'] = 'Address1';
    $headers['6c'] = 'Address2';
    $headers['7']  = 'Suburb';
    $headers['7a'] = 'City';
    $headers['8']  = 'Postcode';
    $headers['9']  = 'State';
    $headers['9a'] = 'Agent Comments';
    $headers['9b'] = 'QA Comments';
    $headers['9c'] = 'Notes';
    $headers['10'] = 'Date set';
    $headers['11'] = 'Position Title';
	$headers['12'] = 'SIC';
    $rct           = 0;
    foreach ($results as $result) {
        if ($act == 'newexport') {
            $scriptres = mysql_query("SELECT scriptjson from scriptdata where leadid = '" . $result['leadid'] . "'");
            $scriptrow = mysql_fetch_array($scriptres);
            $sdata     = json_decode($scriptrow['scriptjson']);
            foreach ($sdata as $key => $value) {
                $scriptdata[$key][$result['leadid']] = $value;
            }
            }
        $rct++;
    }
    foreach ($results as $result) {
        
        $resultCustomFields = get_object_vars(json_decode($result['customfields']));
        foreach ($customdataArray as $key => $value) {
            $result[$key] = $resultCustomFields[$key];
        }
        
        if ($agentid != 'all' && $result['assigned'] != $agentid) {
            
        } elseif ($act == 'newexport') {
        	$headers   = array();
			$crecres = mysql_query("SELECT client_contact_leads.*,client_statuses.* FROM client_contact_leads "
                        . "LEFT JOIN client_statuses ON client_contact_leads.client_disposition = client_statuses.client_statusid "
                        . "WHERE leadid = '".$result['leadid']."'");
            $clientrecord = mysql_fetch_assoc($crecres);
			$ccrecres = mysql_query("SELECT * FROM client_contacts WHERE client_contactid = '".$clientrecord["client_contactid"]."'");
            $clientcontactrecord = mysql_fetch_assoc($ccrecres);
			$abccrecres = mysql_query("SELECT * FROM client_contacts WHERE client_contactid = '".$clientrecord["assignedby"]."'");
            $abclientcontactrecord = mysql_fetch_assoc($abccrecres);
			$atccrecres = mysql_query("SELECT * FROM client_contacts WHERE client_contactid = '".$clientrecord["assignedto"]."'");
            $atclientcontactrecord = mysql_fetch_assoc($atccrecres);
			if ($clientrecord["approvedby"] != 0) {
				$mdrecres = mysql_query("SELECT * FROM memberdetails WHERE userid = '".$clientrecord["approvedby"]."'");
            	$memberdetailsrecord = mysql_fetch_assoc($mdrecres);
			} else {
				$memberdetailsrecord = "";
            }
        	foreach ($viewfields as $key => $vals) {
        		$projectnames = projectnames($bcid);
				$allmembers = getallmemberdetails($bcid);
	        	$rows[$result['leadid']]['Campaign'] = $projectnames[$result['projectid']];
			    $rows[$result['leadid']]['Agent']    = $result['assigned'] == 0 ? '' : $allmembers[$result['assigned']]['afirst'] . ' ' . $allmembers[$result['assigned']]['alast'];
			    $headers['campaign'] = "Campaign";
    			$headers['agent'] = "Agent";
                // if ($vals[0] == 'Notes') {
                    // continue;
                // }
                if ($vals[1] > 0) {
                    if ($key != 'projectid') {
                        $headers[$key] = $vals[0];
                    if ($key == 'assigned') {
                        $aval = $result['assigned'] > 0 ? $agents[$result['assigned']] : '';
                    } elseif ($key == 'epoch_timeofcall' || $key == 'epoch_callable') {
                        $aval = $result[$key] > 0 ? date('Y-m-d H:i:s', $result[$key]) : '';
                    } elseif ($key == 'approvedby') {
                        $aval = !empty($memberdetailsrecord['afirst']) ? $memberdetailsrecord["afirst"]." ".$memberdetailsrecord["alast"] : '';
                    } elseif ($key == 'approvedto') {
                        $aval = !empty($clientcontactrecord['firstname']) ? $clientcontactrecord["firstname"]." ".$clientcontactrecord["lastname"] : '';
                    } elseif ($key == 'assignedby') {
                        $aval = !empty($abclientcontactrecord['firstname']) ? $abclientcontactrecord["firstname"]." ".$abclientcontactrecord["lastname"] : '';
                    } elseif ($key == 'assignedto') {
                        $aval = !empty($atclientcontactrecord['firstname']) ? $atclientcontactrecord["firstname"]." ".$atclientcontactrecord["lastname"] : '';
                    } elseif ($key == 'clientdispo') {
                        $aval = !empty($clientrecord['statusname']) ? $clientrecord["statusname"] : '';
                    } elseif ($key == 'note') {
                        $notes = json_decode($result[$key],true);
                        $agentnotes = '';
                        foreach ($notes as $note)
                        {
                            $agentnotes .= "[".$note['user']."[".date("Y-m-d H:i:s",$note['timestamp'])."]:".$note['message']."]<br/>";
                        }
                        $aval = !empty($agentnotes) ? $agentnotes : '';
    } else {
                        $aval = $result[$key];
                    }
                    $rows[$result['leadid']][$key] = $aval;
                }
            }
            }
        } else {
            if (!empty($result['leadid'])) {
                $projectnames = projectnames($bcid);
                
                $clientidres = mysql_query("SELECT clientid from projects where projectid = '".$result['projectid']."' ");
                $clientidrow = mysql_fetch_assoc($clientidres);
                $clientid = $clientidrow['clientid'];	
                
                $rows[$result['leadid']]['bulk'] = '<input type="checkbox" name="bulkaction" value="' . $result['leadid'] . '"> &nbsp;';
                $_debug                          = sprintf("%s-%s-%s-%s\n", $recordingproject[$result['leadid']], $result['leadid'], $projects[$recordingproject[$result['leadid']]]['linkurl']);
                if ($projects[$recordingproject[$result['leadid']]]['linkurl'] == '' || $projects[$recordingproject[$result['leadid']]]['linkurl'] == null)
                    $rows[$result['leadid']]['bulk'] .= $recordinglog[$result['leadid']] > 0 ? '<img src="../icons/recorded.png" title="Recorded (' . $_debug . ')" />' : '';
                else
                    $rows[$result['leadid']]['bulk'] .= $recordinglog[$result['leadid']] > 0 ? '<img src="../icons/recorded.png" title="Recorded" onclick="player_window(' . $recordingproject[$result['leadid']] . ' ,' . $result['leadid'] . ' ,\'' . $projects[$recordingproject[$result['leadid']]]['linkurl'] . '\')" />' : '';
                
                $headers   = array();
                $headers[] = '<input type="checkbox" id="checkboxall" onclick="togglecheckbox()">';
				$crecres = mysql_query("SELECT client_contact_leads.*,client_statuses.* FROM client_contact_leads "
	                        . "LEFT JOIN client_statuses ON client_contact_leads.client_disposition = client_statuses.client_statusid "
	                        . "WHERE leadid = '".$result['leadid']."'");
	            $clientrecord = mysql_fetch_assoc($crecres);
				$ccrecres = mysql_query("SELECT * FROM client_contacts WHERE client_contactid = '".$clientrecord["client_contactid"]."'");
	            $clientcontactrecord = mysql_fetch_assoc($ccrecres);
				$abccrecres = mysql_query("SELECT * FROM client_contacts WHERE client_contactid = '".$clientrecord["assignedby"]."'");
	            $abclientcontactrecord = mysql_fetch_assoc($abccrecres);
				$atccrecres = mysql_query("SELECT * FROM client_contacts WHERE client_contactid = '".$clientrecord["assignedto"]."'");
	            $atclientcontactrecord = mysql_fetch_assoc($atccrecres);
				if ($clientrecord["approvedby"] != 0) {
					$mdrecres = mysql_query("SELECT * FROM memberdetails WHERE userid = '".$clientrecord["approvedby"]."'");
	            	$memberdetailsrecord = mysql_fetch_assoc($mdrecres);
				} else {
					$memberdetailsrecord = "";
				}
                foreach ($viewfields as $key => $vals) {
                    // if ($vals[0] == 'Notes') {
                        // continue;
                    // }
                    if ($vals[1] > 0) {
                        $headers[$key] = $vals[0];
                        if ($key == 'assigned') {
                            $aval = $result['assigned'] > 0 ? $agents[$result['assigned']] : '';
                        } elseif ($key == 'epoch_timeofcall' || $key == 'epoch_callable') {
                            $aval = $result[$key] > 0 ? date('Y-m-d H:i:s', $result[$key]) : '';
	                    } elseif ($key == 'approvedby') {
	                        $aval = !empty($memberdetailsrecord['afirst']) ? $memberdetailsrecord["afirst"]." ".$memberdetailsrecord["alast"] : '';
	                    } elseif ($key == 'approvedto') {
	                        $aval = !empty($clientcontactrecord['firstname']) ? $clientcontactrecord["firstname"]." ".$clientcontactrecord["lastname"] : '';
	                    } elseif ($key == 'assignedby') {
	                        $aval = !empty($abclientcontactrecord['firstname']) ? $abclientcontactrecord["firstname"]." ".$abclientcontactrecord["lastname"] : '';
	                    } elseif ($key == 'assignedto') {
	                        $aval = !empty($atclientcontactrecord['firstname']) ? $atclientcontactrecord["firstname"]." ".$atclientcontactrecord["lastname"] : '';
	                    } elseif ($key == 'clientdispo') {
                            $aval = !empty($clientrecord['statusname']) ? $clientrecord["statusname"] : '';
                        } elseif ($key == 'projectid') {
	                        $aval = !empty($projectnames[$result['projectid']]) ? $projectnames[$result['projectid']] : '';
						} elseif ($key == 'note') {
							$notes = json_decode($result[$key],true);
			                $agentnotes = '';
			                foreach ($notes as $note)
			                {
			                    $agentnotes .= "[".$note['user']."[".date("Y-m-d H:i:s",$note['timestamp'])."]:".$note['message']."]<br/>";
			                }
	                        $aval = !empty($agentnotes) ? $agentnotes : '';
                        } else {
                            $aval = $result[$key];
                        }
                        $rows[$result['leadid']][$key] = $aval;
                    }
                }
                $rows[$result['leadid']]['options'] = 'title="' . $result['resultcomments'] . '" onclick="getlead(\'' . $result['leadid'] . '\',\'' . $start . '\',\'' . $end . '\')"';
                $rows[$result['leadid']]['actions'] = '<a href="#" onclick="lastrecording(\'' . $result['leadid'] . '\')" title="Play Recording" style="display:none"><img src="../icons/recorded.png" /></a>
                <a href="#" onclick="qacall(\'' . $result['leadid'] . '\',event)" title="Call" ><img src="../icons/dial.png" /></a>
                <a href="#" onclick="emaillead(\'' . $result['leadid'] . '\',\'' . $result['projectid'] . '\',\'' . $clientid . '\',\'' . $bcid . '\')" title="Email to Client"><img src="../icons/mail.png" /></a>   
                ';
                
            }
            
        }
    }
    
    if ($act == 'newexport') {
        $table = tablegen($headers, $rows, "930");
        createdoc('excel', $table, true);
    } else {
        $headers['actions'] = 'Action';
        $dcont              = '<div id="searchresults">' . tablegen($headers, $rows, "100%") . '</div>';
    }
}
// if ($act == 'emailtoclientnew')
// {

//     $tid = $_REQUEST['templateid'];

//     $res            = mysql_query("SELECT * from templates where templateid = '$tid'");
//     $row            = mysql_fetch_array($res);

//     $templatebody   = $row['template_body'];
//     $templatename   = $row['template_name'];
  

//     echo $templatebody;

    

    

    
// }
if ($act == 'getlead' || $act == 'emailtoclient')
	{
		$startdate = $_REQUEST["startdate"];
		$enddate = $_REQUEST["enddate"];
		$record = getrecord($_REQUEST['leadid']);
		$lead = $record['info'];
		$clientidres = mysql_query("SELECT clientid from projects where projectid = '".$lead['projectid']."' ");
		$clientidrow = mysql_fetch_assoc($clientidres);
		$clientid = $clientidrow['clientid'];		
        // $ccontacts = getbyparams('client_contacts',"clientid = '".$clientid."'",'client_contactid');
        $ccontacts_res = mysql_query("SELECT client_contacts.*, members.userlogin, members.userpass, members.usertype as usermode from client_contacts 
                              left join members on client_contacts.userid = members.userid where clientid = $clientid and client_contacts.bcid = '$bcid' and client_contacts.active = 1");
        while ($ccontacts_row = mysql_fetch_array($ccontacts_res))
        {
            $ccontacts[$ccontacts_row['client_contactid']] = $ccontacts_row;
        }
		foreach ($ccontacts as $cc)
		{
			$cdrop .= '<option value="'.$cc['email'].'">'.$cc['firstname'].' '.$cc['lastname'].'</option>';
		}		
		$cdata = $record['scriptdata'];
                $cfdata = $record['customdata'];
		$appdate = $lead['epoch_callable'] > 0 ? date("Y-m-d H:i:s", $lead['epoch_callable']):'';
        $client_contact_slots_res = mysql_query("select concat (lastname,', ',firstname) as apptarget from client_contact_slots a cross join client_contacts b on a.client_contactid=b.client_contactid where leadid= " . $lead['leadid']);
        $client_contact_slots_row = mysql_fetch_assoc($client_contact_slots_res);
        $apptarget_appointment = $client_contact_slots_row['apptarget'];
		$d = dispolist($lead['projectid']);
            /***************************/
            /* ADDED BY Vincent Castro */
            /***************************/
            /* ADDED DATA-CATEGORY */
                foreach ($d as $disp)
                {
                    if ($disp['statustype'] == 'dateandtime' || $disp['statustype'] == 'transferdateandtime')
                            {
                                    $dispodrop .="<option onclick=\"createdateinput()\" data-category=\"".$disp['category']."\">";
                                    $dispodrop .=$disp['statusname'];
                                    $dispodrop .="</option>";
                            }
                    elseif ($disp['statustype'] == 'booking')
                                            {
                                    $dispodrop .="<option onclick=\"doslots('".$lead['leadid']."','$clientid')\" data-category=\"".$disp['category']."\">";
                                    $dispodrop .=$disp['statusname'];
                                    $dispodrop .="</option>";
                                            }
                    elseif ($disp['statustype'] == 'link')
                            {
                            $dispodrop .="<option onclick=\"showupdatepage('".$disp['statusid']."')\" data-category=\"".$disp['category']."\">";
                            $dispodrop .=$disp['statusname'];
                            $dispodrop .="</option>";
                            }
                    elseif ($disp['statustype'] == 'transfer')
                            {
                            $dispodrop .="<option onclick=\"cleardateinput()\" data-category=\"".$disp['category']."\">";
                            $dispodrop .=$disp['statusname'];
                            $dispodrop .="</option>";
                            }
                    elseif ($disp['statusname'] != 'all') {
                            $dispodrop .="<option onclick=\"cleardateinput();\" data-category=\"".$disp['category']."\">";
                            $dispodrop .=$disp['statusname'];
                            $dispodrop .="</option>";
                    }
                }
		//$dispdrop = createdropdown($d,"statusname","statusname");
                $dispdrop = $dispodrop;
                $projectname = projects::getprojectname($lead['projectid']);
		if (isset($_REQUEST['export']))
		{			
		}
		elseif ($act != 'emailtoclient' && !isset($_REQUEST['export']) ) {
                    //| <a href="qa.php?act=getlead&leadid=<?php echo $_REQUEST['leadid'];&export">Export</a> 
		?>
        <a href="#" onClick="saveleadforqa()">Save</a> | <a href="#" onClick="printdiv()" id="printlink">Print</a> | <a href="#" onclick="emaillead('<?php echo $lead['leadid'];?>','<?php echo $lead['projectid']; ?>','<?php echo $clientid; ?>','<?php echo $bcid;?>','<?php echo $_SESSION['username'];?>')">Email</a> |
        <?php

            $templistres = mysql_query("SELECT * from templates where projectid = '".$lead['projectid']."'");
            while ( $lrow = mysql_fetch_array($templistres) ) {
                $tlist[$lrow['templateid']] = $lrow;
                $toptions .= '<option value="'.$lrow['templateid'].'">'.$lrow['template_name'].'</option>';
            }

        ?>
        
        <div id="emailcontacts<?php echo $lead['leadid'];?>" class="dialogform">
           <input type="hidden" id="qadelivery" name="qadelivery">
            Select Template:
            <select name="emailtemplate" style="width: 152px;" onchange="ChangeTemplate(this)"><option></option><?=$toptions;?></select><br>
            Select Recipient:
            <select name="emailtoclient"><option></option><?php echo $cdrop;?></select><br>
            Subject: <input type="text" name="subject" id="lead_subject" value = ""  style="margin-left: 50px; width: 153px;">
            <br /><a href="#" onclick="sendemailtoclient()" id="setc">Send</a>

        </div>

        <?php 
                }
        if ($act == 'emailtoclient' || isset($_REQUEST['export'])) ob_start();
        ?>
        <script>
        	$(document).on('click','.show_more',function(e){
		        var ID = $(this).attr('id');
		        var leadid = <?php echo "'".$lead['leadid']."'";?>;
		        var projectid = <?php echo "'".$lead['projectid']."'";?>;
		        var startdate = <?php echo  "'".$startdate."'";?>;
		        var enddate = <?php echo "'".$enddate."'";?>;
		        $('.show_more').hide();
		        $('.loading').show();
		        $(".listrecordings"+leadid).remove();
		        $.ajax({
		            type:'POST',
		            url:'qaverswitch-include.php',
		            data: {
		            	'id' : ID, 'leadid' : leadid, 'projectid' : projectid, 'startdate' : startdate, 'enddate' : enddate
		            },
		            success:function(html){
		                $('#show_more_main'+ID).remove();
		                $('.recordings'+leadid).append(html);
		            }
		        });
		    });
        </script>
        <style>
        	#player {
			 width: 200px; 
			 display: block;
			}
			.show_more_main {
			margin: 15px 25px;
			}
			.show_more {
			background-color: #f8f8f8;
			background-image: -webkit-linear-gradient(top,#fcfcfc 0,#f8f8f8 100%);
			background-image: linear-gradient(top,#fcfcfc 0,#f8f8f8 100%);
			border: 1px solid;
			border-color: #d3d3d3;
			color: #333;
			font-size: 12px;
			outline: 0;
			}
			.show_more {
			cursor: pointer;
			display: block;
			padding: 10px 0;
			text-align: center;
			font-weight:bold;
			}
			.loading {
			background-color: #e9e9e9;
			border: 1px solid;
			border-color: #c6c6c6;
			color: #333;
			font-size: 12px;
			display: block;
			text-align: center;
			padding: 10px 0;
			outline: 0;
			font-weight:bold;
			}
			.loading_txt {
			background-position: left;
			background-repeat: no-repeat;
			border: 0;
			display: inline-block;
			height: 16px;
			padding-left: 20px;
			}​
        </style>

        <div id="msg_print">
        <form action="qa.php?act=saveleadforqa&leadid=<?php echo $lead['leadid'];?>" id="updatelead" name="updatelead" method="post">
        <table cellspacing="5" width="800">
        <tr>
        <td colspan="4" class="tableheader">Contact Information</td>
    </tr>
    <tr>
        <td class="tableitems">Leadid: </td>
        <td id="tlid"><?php echo $lead['leadid'];?><input type="hidden" name="leadid" id="leadidval" value="<?php echo $lead['leadid'];?>" /></td>
        </tr>
    <tr>
        <td>Name:</td>
        <td><input type="text" name="cname" style="width:200px" value="<?php echo $lead['cname'];?>" /></td>
        <td>Company:</td>
        <td><input type="text" name="company" style="width:200px" value="<?php echo $lead['company'];?>" /></td>
    </tr>
    <tr>
        <td>Title:</td>
        <td><input type="text" name="title" style="width:200px" value="<?php echo $lead['title'];?>" /></td>
        <td>Position:</td>
        <td><input type="text" name="positiontitle" style="width:200px" value="<?php echo $lead['positiontitle'];?>" /></td>
    </tr>
    <tr>
        <td>First Name:</td>
        <td><input type="text" name="cfname" style="width:200px" value="<?php echo $lead['cfname'];?>" /></td>
        <td>Last Name:</td>
        <td><input type="text" name="clname" style="width:200px" value="<?php echo $lead['clname'];?>" /></td>
    </tr>
    <tr>
        <td>Industry:</td>
        <td><input type="text" name="industry" style="width:200px" value="<?php echo $lead['industry'];?>" /></td>
        <td>SIC:</td>
        <td><input type="text" name="sic" style="width:200px" value="<?php echo $lead['sic'];?>" /></td>
    </tr>
    <tr>
        <td>Phone:</td>
        <td><?php echo $lead['phone'];?><input type="hidden" style="width:200px" name="phone" value="<?php echo $lead['phone'];?>" /></td>
        <td>AltPhone:</td>
        <td><input type="text" name="altphone" style="width:200px" value="<?php echo $lead['altphone'];?>" /></td>
    </tr>
    <tr>
        <td>Mobile:</td>
        <td><input type="text" name="mobile" style="width:200px" value="<?php echo $lead['mobile'];?>" /></td>
        <td>Email:</td>
        <td><input type="text" name="email" style="width:200px" value="<?php echo $lead['email'];?>" /></td>
    </tr
    <tr>
        <td>Address1:</td>
        <td colspan="3"><input type="text" name="address1" style="width:640px" value="<?php echo $lead['address1'];?>" /></td>
    </tr>
    <tr>
        <td>Address2:</td>
        <td colspan="3"><input type="text" name="address2" style="width:640px" value="<?php echo $lead['address2'];?>" /></td>
    </tr>
    <tr>
        <td>City/Suburb:</td>
        <td><input type="text" name="city" value="<?php echo strlen($lead['suburb']) > 0 ? $lead['suburb'] : $lead['city'];?>" /></td>
        <td>Postcode:</td>
        <td><input type="text" name="zip" style="width:70px" value="<?php echo $lead['zip'];?>" /></td>
    </tr>
    <tr>
        <td>State:</td>
        <td><input type="text" name="state" style="width:200px" value="<?php echo $lead['state'];?>" /></td>
        <td>Country:</td>
        <td><input type="text" name="country" style="width:200px" value="<?php echo $lead['country'];?>" /></td>
    </tr>
    <tr>
        <td>Comments:</td>
        <td colspan="3"><textarea name="comments" style="width:640px" ><?php echo $lead['comments'];?></textarea></td>
    </tr>
</table>
        <br>
        <hr>       
        <br>
        <table width="800" cellspacing="5">
        <tr><td colspan="4" class="tableheader">Custom Fields Data</td></tr>
         <?php
        //var_dump($record);
		if (is_array($cfdata))
		{
		foreach ($cfdata as $key=>$value)
			{
				echo '<tr><td colspan="2">'.ucfirst(str_replace("_"," ",$key)).'</td><td>
                                    <input type="text" class="qacf" value="'.$value.'" name="'.$key.'"></td></tr>';
			}
		}
		?>        
        </table>
<!--        <br>
        <hr>       
        <br>
        <table width="800" cellspacing="5">
        <tr><td colspan="4" class="tableheader">Script Captured Data</td></tr>-->
         <?php
		// if (is_array($cdata))
		// {
		// foreach ($cdata as $key=>$value)
			// {
			//	echo '<tr><td colspan="2">'.ucfirst(str_replace("_"," ",$key)).'</td><td>
            //                        <input type="text" class="qasf" value="'.$value.'" name="'.$key.'"></td></tr>';
			// }
		// }
		?>
<!--        </table> -->
        <br>
        <hr>
        <?php
        $audio = array();
        $i = 0;
        //$s3 = new S3("AKIAIFNBYO657IIJKOUQ", "w6Q/iJwhRYvS+RR1agf3zQoNrvtaw3T4as7qDpd2");
       	$s3 = new S3($GLOBAL_S3_ACCESS_KEY[0], $GLOBAL_S3_ACCESS_KEY[1]);
		$rclogres = mysql_query("SELECT bucket, prefix, filename FROM recordinglog WHERE projectid = '".$lead['projectid']."' AND filename LIKE ('".$lead["leadid"]."%') AND epoch >= ".strtotime($startdate." 00:00:00")." AND epoch <= ".strtotime($enddate." 23:59:59")."");
		while ($rclogrow = mysql_fetch_assoc($rclogres)) {
			$s3bucket = $rclogrow["bucket"]; 
			$filename = str_replace("wav", "mp3", $rclogrow["filename"]);
			$prefix = $rclogrow["prefix"].$lead['projectid']."/".$filename;
			$audio[$i."|".$s3bucket] = $s3->getBucket($s3bucket,$prefix);
			$i++;
		}
		if (is_array($audio) && count($audio) > 0)
		{
		?>
        <br>
        <table width="800" cellspacing="5" class="recordings<?php echo $lead['leadid'];?>">
        <tr><td colspan="5" class="tableheader">Recordings</td></tr>
        <tr><th>Date/Time</th><th>Size</th><th>Duration</th><th>Play</th><th>File</th></tr>
        <?php
		$cta = 0;                
		$recordingcount = 0; 
		$setcount = 5;
		foreach (array_reverse($audio) as $keyrecordings => $valrecordings) {
			$bucket = $keyrecordings;
			foreach ($valrecordings as $aud) {
			    	if ($recordingcount == $setcount) {
						break;
					} else {
                        $s3b = explode("|", $bucket);
                        
                    $m = $cta % 2;
				if ($m = 0) $cl = "tableitem";
				else $cl = "tableitem_";
                                $lc = filesizeformat($aud['size']);
                                $media = S3::getAuthenticatedURL($s3b[1], $aud['name'], 3600, false, true);
                                
                                $id3_tags = mp3_get_tags($media);
								$duration = $id3_tags["formatted_time"];
								
                                $dt = gettimefromname($aud['name']);                                
                                $dti = date("Y-m-d H:i:s",$dt);
			
                                echo '<tr class="listrecordings'.$lead['leadid'].' '.$cl.'"><td>'.$dti.'</td>
				<td>'.$lc.'</td>
				<td>'.$duration.'</td>';
                echo '<td width="230" align="center">';
                if (!isset($_REQUEST['export']) && $act != 'emailtoclient') {
                    // echo '<object type="application/x-shockwave-flash" data="../../jquery/player_mp3_mini.swf" width="200" height="20">
                        // <param name="movie" value="../../jquery/player_mp3_mini.swf" />
                        // <param name="bgcolor" value="#c7c7c7" />
                        // <param name="FlashVars" value="mp3='.urlencode($media).'&amp;bgcolor=c7c7c7&amp;slidercolor=404040" />
                    // </object>';
                    echo '<audio id="player" controls preload="none">
                    <source src="'.$media.'" type="audio/mpeg">
                    </audio>';
                        }
                                echo '</td>
				<td><a id="wavfile" href="'.$media.'" target="_blank">Download</a></td>
				</tr>';
                $cta++;
                $recordingcount++;
                }
			}
		}
        ?>
		<tr><td colspan="5">
        <div class="show_more_main" id="show_more_main<?php echo $setcount; ?>">
	        <span id="<?php echo $setcount; ?>" class="show_more">Show more</span>
	        <span class="loading" style="display: none;"><span class="loading_txt">Loading....</span></span>
	    </div>
	   	</td></tr>
        </table>
        <b><font color='blue'>NOTE:</font></b> Recordings expire in <b>90 days</b> from date of creation as a default policy. Please disregard this note if you have already made a prior arrangement to have a longer expiration for your recordings.
        <br>
        <br>
        <hr>
        <?php
		}
                $notesres = mysql_query("SELECT * from leads_notes where leadid = '".$_REQUEST['leadid']."'");
                $notesrow = mysql_fetch_assoc($notesres);
                $notes = json_decode($notesrow['note'],true);
                $agentcomments = '';
                foreach ($notes as $note)
                {
                    $agentcomments .= $note['user']."[".date("Y-m-d H:i:s",$note['timestamp'])."]:".$note['message']."\r\n";
                }
		?>
        <br>
        <input type="hidden" name="projectid" value="<?php echo $lead['projectid'];?>"/>
        <input type="hidden" name="assigned" id="assignedid" value="<?php echo $lead['assigned'];?>"/>
        <table width="800" cellspacing="5">
        <tr id="tableheader"><td colspan="4" class="tableheader">Results and Notes</td></tr>
        <tr id="oldagent">
            <td colspan="2">Agent: </td>
            <td>
                <?php echo $agents[$lead['assigned']];?>
            </td>
        </tr>       
        <?php if (!isset($_REQUEST['export']) && $act != 'emailtoclient')
        {
            ?>
        <tr><td colspan="2">Agent Notes:</td><td><textarea style="width:400px; height:90px" disabled><?php echo $agentcomments;?></textarea></td></tr>
        <?php
        }
        ?>
        <tr id="disposition">
            <td colspan="2">Disposition:</td>
            <td>
                <select name="dispo" onchange="disposelect(this)">
                    <option value="<?php echo $lead['dispo'];?>"><?php echo ucfirst($lead['dispo']);?></option>
                    <?php echo $dispdrop;?>
                </select>
            </td>
        </tr>
        <tr id="datetd">
        <td colspan="2">Date set:</td><td><input class="dtpick" type=text name="dtime" value="<?php echo $appdate;?>" /></td>
    </tr>
    <?php
        	$crecres = mysql_query("SELECT client_contact_leads.*,client_statuses.* FROM client_contact_leads "
                        . "LEFT JOIN client_statuses ON client_contact_leads.client_disposition = client_statuses.client_statusid "
                        . "WHERE leadid = '".$lead['leadid']."'");
            $clientrecord = mysql_fetch_assoc($crecres);
			$ccrecres = mysql_query("SELECT * FROM client_contacts WHERE client_contactid = '".$clientrecord["client_contactid"]."'");
            $clientcontactrecord = mysql_fetch_assoc($ccrecres);
			$abccrecres = mysql_query("SELECT * FROM client_contacts WHERE client_contactid = '".$clientrecord["assignedby"]."'");
            $abclientcontactrecord = mysql_fetch_assoc($abccrecres);
			$atccrecres = mysql_query("SELECT * FROM client_contacts WHERE client_contactid = '".$clientrecord["assignedto"]."'");
            $atclientcontactrecord = mysql_fetch_assoc($atccrecres);
			if ($clientrecord["approvedby"] != 0) {
				$mdrecres = mysql_query("SELECT * FROM memberdetails WHERE userid = '".$clientrecord["approvedby"]."'");
            	$memberdetailsrecord = mysql_fetch_assoc($mdrecres);
			} else {
				$memberdetailsrecord = "";
			}
        ?>
        <?php
        	if (!empty($clientrecord['statusname'])) {
        ?>
        <tr id="clientdisposition"><td colspan="2">Client Disposition:</td><td><?php echo $clientrecord['statusname'];?></td></tr>
        <?php
        	}
        ?>
        <?php
		if(strlen($appdate) > 1)
		{
                }
			?>
        <tr id="datetd">
            <td colspan="2">Booked for:</td><td><?php echo $apptarget_appointment;?></td>
        </tr>           
        <tr><td colspan="2">QA Comments:</td><td><textarea name="qa" style="width:400px; height:90px"><?php echo $lead['qa'];?></textarea></td></tr>
        <tr><td colspan="2">QA Status:</td><td><select id="status" name="status" onchange="leadqabulk(<?php echo $lead['leadid'];?>, <?php echo $lead['projectid'];?>)"><option value="<?php echo $lead['status'];?>"><?php echo ucfirst($lead['status']);?></option><option value="approved">Approved</option><option value="approvedto">Approve To</option><option value="failed">Failed</option><option value="incomplete">Incomplete</option><option value="callback">Transfer The Call</option></select></td></tr>
        <tr><td colspan="2">Approved By:</td><td><?php echo $memberdetailsrecord['afirst'];?>&nbsp;<?php echo $memberdetailsrecord['alast'];?></td></tr>
        <tr><td colspan="2">Approved To:</td><td><?php echo $clientcontactrecord['firstname'];?>&nbsp;<?php echo $clientcontactrecord['lastname'];?></td></tr>
        <tr><td colspan="2">Assigned By:</td><td><?php echo $abclientcontactrecord['firstname'];?>&nbsp;<?php echo $abclientcontactrecord['lastname'];?></td></tr>
        <tr><td colspan="2">Assigned To:</td><td><?php echo $atclientcontactrecord['firstname'];?>&nbsp;<?php echo $atclientcontactrecord['lastname'];?></td></tr>
        </table>
        </form>
        </div>        
        <?php
        if (isset($_REQUEST['export']))
        {           
            $body = ob_get_contents();
            ob_clean();
            $body = str_replace("<input ", "<input disabled  ", $body);
            $body = str_replace("<select ", "<select disabled  ", $body);
             createdoc("word", $body);
        }
        if ($act =='emailtoclient')
        {
             $body = ob_get_contents();
            $emailto = $_REQUEST['to'];
            $subject = $_REQUEST['subject'];
            if (strlen($subject) < 1)
            {
                $subject = 'Sent from '.$projectname;
            }
            ob_clean();
            $body = str_replace("<input ", "<input disabled  ", $body);
            $body = str_replace("<select ", "<select disabled  ", $body);
            $bcres = mysql_query("SELECT * from bc_clients where bcid ='$bcid'");
            $bc = mysql_fetch_assoc($bcres);
            $emailer = new Mailer();            
            $emailer->set_mail("noreply@bluecloudaustralia.com.au",$emailto,$subject,$body);
            $emailer->fromName($bc['company']);
            echo $emailer->send_mail();
            exit; 







        }
        if (!isset($_REQUEST['export']))
		{
			?>
        <a href="#" onClick="saveleadforqa()">Save</a> | <a href="#" onClick="printdiv()" id="printlink">Print</a> | <a href="#" onclick="emaillead('<?php echo $lead['leadid'];?>','<?php echo $lead['projectid']; ?>','<?php echo $clientid; ?>','<?php echo $bcid;?>')">Email</a> |
        <?php
		}
		exit();
    }
    if ($act == 'checkbookedslotdispo') {
        $dispo = $_REQUEST['dispo'];
        $projid = $_REQUEST['projid'];
        $leadid = $_REQUEST['leadid'];
        $statusnameres = mysql_query("SELECT * FROM statuses WHERE projectid = $projid AND statusname LIKE '$dispo'");
        if (mysql_num_rows($statusnameres) == 0){
            $statusnamedefaultrow = mysql_query("SELECT * FROM statuses WHERE projectid = 0 AND statusname LIKE '$dispo'");
            $statusnamerow = mysql_fetch_assoc($statusnamedefaultrow);
        } else {
            $statusnamerow = mysql_fetch_assoc($statusnameres);
        }
        if ($statusnamerow["statustype"] != "booking") {
            $ccsres = mysql_query("SELECT * FROM client_contact_slots WHERE leadid = $leadid");
            $ccsrow = mysql_fetch_assoc($ccsres);
            if ($ccsrow["orig_slotid"] != NULL) {
                mysql_query("DELETE FROM client_contact_slots WHERE slotid = ".$ccsrow["slotid"]."");
            } else {
                mysql_query("UPDATE client_contact_slots SET taken = 0, leadid = NULL WHERE slotid = ".$ccsrow["slotid"]."");
            }
        }
    }
        /***************************/
    /* ADDED BY Vincent Castro */
    /***************************/
    include('customfields/qaswitch-include.php');