<?php
session_start();
include "../../dbconnect.php";
include "../../admin/phpfunctions.php";
$type = $_SESSION["usertype"];
$bcid = $_SESSION['bcid'];
$cid  = $_REQUEST['cid'];
$ci   = $_REQUEST['ci'];
$act  = $_REQUEST['act'];
if ($act == "createtakenslot") {
	$leadid = $_REQUEST['leadid'];
	$calendar = $_REQUEST['calendar'];
	$calendarstrtotime = strtotime($_REQUEST['calendar']);
	$explodedate = explode(" ", $calendar);
	$date = $explodedate[0];
	$slotdatecalendar = strtotime($_REQUEST['slotdatecalendar']);
    $slotid = $_REQUEST['slotid'];
	$slotstart = $_REQUEST['slotstart'];
	$slotend = $_REQUEST['slotend'];
	$client_contactid = $_REQUEST['client_contactid'];
	$clientid = $_REQUEST['clientid'];
	$recurring = $_REQUEST['recurring'];
	mysql_query("insert into client_contact_slots set slotstart = '$slotstart', slotend = '$slotend', client_contactid = '$client_contactid', clientid= '$clientid', date = '$date', taken = 2, leadid = '$leadid', orig_slotid = '$slotid'");
    mysql_query("update leads_raw set epoch_callable = '$calendarstrtotime' where leadid = $leadid limit 1");
	mysql_query("update leads_done set epoch_callable = '$calendarstrtotime' where leadid = $leadid limit 1");
		exit;
}
if ($act == "updatetakenslot") {
	$calendar = strtotime($_REQUEST['calendar']);
	$slotdatecalendar = strtotime($_REQUEST['slotdatecalendar']);
	$leadid = $_REQUEST['leadid'];
    $slotid = $_REQUEST['slotid'];
	$dispo = $_REQUEST['dispo'];
	if ( $calendar == $slotdatecalendar ) {
		mysql_query("update client_contact_slots set taken = 2, leadid = '$leadid' where slotid = '$slotid'");
		mysql_query("update leads_raw set dispo = '$dispo', epoch_callable = '$calendar' where leadid = $leadid limit 1");
		mysql_query("update leads_done set dispo = '$dispo', epoch_callable = '$calendar' where leadid = $leadid limit 1");
	} else if ( $calendar != $slotdatecalendar ) {
		mysql_query("update leads_raw set dispo = '$dispo', epoch_callable = '$calendar' where leadid = $leadid limit 1");
		mysql_query("update leads_done set dispo = '$dispo', epoch_callable = '$calendar' where leadid = $leadid limit 1");
	}
    exit;
}
if ($act == "slots") {
    $date = $_REQUEST['date'];
    if (strpos($date, ",")) {
        $dates = explode(",", $date);
        $darr  = array();
        foreach ($dates as $dt) {
            $d     = explode("-", $dt);
            $year  = $d[0];
            $month = $d[1];
            $day   = $d[2];
            if ($month > 12)
                $month = 1;
            if ($month < 1)
                $month = 12;
            $fdate = implode(array(
                "'",
                $year,
                '-',
                $month,
                '-',
                $day,
                "'"
            ));
            $darr[] = $fdate;
        }
        $dstring = implode(",", $darr);
    } else {
        $d     = explode("-", $date);
        $year  = $d[0];
        $month = $d[1];
        $day   = $d[2];
        if ($month > 12)
            $month = 1;
        if ($month < 1)
            $month = 12;
        $fdate = implode(array(
            "'",
            $year,
            '-',
            $month,
            '-',
            $day,
            "'"
        ));
        $dstring = $fdate;
    }
    //For Regular Slot
    $slres = mysql_query("SELECT client_contact_slots.*, client_contacts.*, client_recurring_slots1.recurring 
							FROM client_contact_slots 
							JOIN client_contacts ON client_contacts.client_contactid = client_contact_slots.client_contactid 
							LEFT JOIN client_recurring_slots1 ON client_recurring_slots1.clientcontactslotsid = client_contact_slots.slotid
							WHERE client_contact_slots.client_contactid in ($ci) and client_contact_slots.date in ($dstring) 
							ORDER BY client_contact_slots.slotstart ASC");
    $slots = array();
    while ($slrow = mysql_fetch_assoc($slres)) {
        $slots[$slrow["slotid"]] = $slrow;
    }
    //For Recurring Slot
    $slres_recurring = mysql_query("SELECT client_recurring_slots1.*, client_contact_slots.date as ccsdate, client_contact_slots.slotstart, client_contact_slots.slotend, client_contact_slots.taken, client_contact_slots.slotid, client_contacts.firstname, client_contacts.lastname 
										FROM client_recurring_slots1 
										JOIN client_contact_slots ON client_contact_slots.slotid = client_recurring_slots1.clientcontactslotsid 
										JOIN client_contacts ON client_contacts.client_contactid = client_contact_slots.client_contactid 
										WHERE client_recurring_slots1.client_contactid in ($ci)");
    $slots_recurring = array();
    while ($row = mysql_fetch_assoc($slres_recurring)) {
        $slots_recurring[] = $row; // Inside while loop
    }
    $headers[] = 'Contact';
    $headers[] = 'Date';
    $headers[] = "From";
    $headers[] = "To";
    $headers[] = "Status";
    $rows = array();
    //For Regular Slot
    foreach ($slots as $slot) {
        $pdate                                = date("Y-m-d H:i:s", $slot['slotstart']);
        $rows[$slot['slotid']]['contactname'] = $slot['firstname'] . ' ' . $slot['lastname'];
        $rows[$slot['slotid']]['date']        = $slot['date'];
        $rows[$slot['slotid']]['start']       = date("h:i:s a", $slot['slotstart']);
        $rows[$slot['slotid']]['end']         = date("h:i:s a", $slot['slotend']);
        $status                               = '<span style="color:#66FF33">free</span>';
        if ($slot['taken'] > 0) {
            $status = '<span style="color:red">taken</span>';
        }
        $rows[$slot['slotid']]['status'] = ucfirst($status);
        if ($slot['taken'] < 1) {
            $rows[$slot['slotid']]['options'] = 'onclick="popdate_(\'' . $pdate . '\',\'' . $slot['slotid'] . '\',\'' . $slot['slotstart'] . '\',\'' . $slot['slotend'] . '\',\'' . $slot['client_contactid'] . '\',\'' . $slot['clientid'] . '\')"';
        }
    }
	
    $rows_recurring = array();
    //For Recurring Slot
    foreach ($slots_recurring as $slot_recurring) {
        $datenow = $date;
        if (strtotime($slot_recurring['date']) != strtotime($datenow)) {
			//For Checking Regular Slot
		    $get_regular_slot_for_checking = mysql_query("SELECT client_contact_slots.*, client_contacts.* 
									FROM client_contact_slots 
									JOIN client_contacts ON client_contacts.client_contactid = client_contact_slots.client_contactid 
									WHERE client_contact_slots.client_contactid in ($ci) and client_contact_slots.date LIKE '$datenow' and client_contact_slots.taken > 0 
									ORDER BY client_contact_slots.slotstart ASC");
		    while ($regular_slot_for_checking = mysql_fetch_assoc($get_regular_slot_for_checking)) {
		        $regularslotsforchecking[] = $regular_slot_for_checking;
		    }
            if ($slot_recurring['recurring'] == "Daily") {
            	$date1 = date_create($slot_recurring['tempcalculateddate']);
                $date2 = date_create($datenow);
                $diff  = date_diff($date1, $date2);
                $days  = $diff->format("%R%a days");
				$recurrenceenddate	= $slot_recurring['recurrenceenddate'];
                $calculate      = strtotime($days, strtotime($slot_recurring['tempcalculateddate']));
                $calculateddate = date('Y-m-d', $calculate);
                if (strtotime($calculateddate) == strtotime($datenow)) {
                	if ($calculateddate >= $slot_recurring['date']) {
                		if ($calculateddate <= $recurrenceenddate) {
                			$pdate                                          = $calculateddate . " " . date("h:i:s a", $slot_recurring['slotstart']);
		                    $rows[$slot_recurring['slotid']]['contactname'] = $slot_recurring['firstname'] . ' ' . $slot_recurring['lastname'];
		                    $rows[$slot_recurring['slotid']]['date']        = $calculateddate;
		                    $rows[$slot_recurring['slotid']]['start']       = date("h:i:s a", $slot_recurring['slotstart']);
		                    $rows[$slot_recurring['slotid']]['end']         = date("h:i:s a", $slot_recurring['slotend']);
		                    $status                                         = '<span style="color:#66FF33">free</span>';
		                    $rows[$slot_recurring['slotid']]['status'] = ucfirst($status);
		                    $rows[$slot_recurring['slotid']]['options'] = 'onclick="popdate_(\'' . $pdate . '\',\'' . $slot_recurring['slotid'] . '\',\'' . $slot_recurring['slotstart'] . '\',\'' . $slot_recurring['slotend'] . '\',\'' . $slot_recurring['client_contactid'] . '\',\'' . $slot_recurring['clientid'] . '\',\'' . $slot_recurring['recurring'] . '\')"';
                		}
                    }
                }
            } else if ($slot_recurring['recurring'] == "Weekly") {
            	$date1            = date_create($slot_recurring['tempcalculateddate']);
                $date2            = date_create($datenow);
                $diff             = date_diff($date1, $date2);
                $numberofweeks    = $diff->format("%R%a days");
				$recurrenceenddate	= $slot_recurring['recurrenceenddate'];
                $splitweeksresult = explode(" ", $numberofweeks);
                $numweeks         = $splitweeksresult[0] / 7;
                if (is_int($numweeks)) {
                    $weeks = $numweeks . " weeks";
                    $calculate      = strtotime($weeks, strtotime($slot_recurring['tempcalculateddate']));
                    $calculateddate = date('Y-m-d', $calculate);
                    if (strtotime($calculateddate) == strtotime($datenow)) {
                    	if ($calculateddate >= $slot_recurring['date']) {
	                		if ($calculateddate <= $recurrenceenddate) {
	                				$pdate                                          = $calculateddate . " " . date("h:i:s a", $slot_recurring['slotstart']);
				                    $rows[$slot_recurring['slotid']]['contactname'] = $slot_recurring['firstname'] . ' ' . $slot_recurring['lastname'];
				                    $rows[$slot_recurring['slotid']]['date']        = $calculateddate;
				                    $rows[$slot_recurring['slotid']]['start']       = date("h:i:s a", $slot_recurring['slotstart']);
				                    $rows[$slot_recurring['slotid']]['end']         = date("h:i:s a", $slot_recurring['slotend']);
				                    $status                                         = '<span style="color:#66FF33">free</span>';
				                    $rows[$slot_recurring['slotid']]['status'] = ucfirst($status);
				                    $rows[$slot_recurring['slotid']]['options'] = 'onclick="popdate_(\'' . $pdate . '\',\'' . $slot_recurring['slotid'] . '\',\'' . $slot_recurring['slotstart'] . '\',\'' . $slot_recurring['slotend'] . '\',\'' . $slot_recurring['client_contactid'] . '\',\'' . $slot_recurring['clientid'] . '\',\'' . $slot_recurring['recurring'] . '\')"';
							}
						}
                    }
                    if ($numweeks == 0) {
                        if (strtotime($slot_recurring['tempcalculateddate']) == strtotime($datenow)) {
                        	if ($calculateddate >= $slot_recurring['date']) {
                        		$pdate                                          = $datenow . " " . date("h:i:s a", $slot_recurring['slotstart']);
	                            $rows[$slot_recurring['slotid']]['contactname'] = $slot_recurring['firstname'] . ' ' . $slot_recurring['lastname'];
	                            $rows[$slot_recurring['slotid']]['date']        = $datenow;
	                            $rows[$slot_recurring['slotid']]['start']       = date("h:i:s a", $slot_recurring['slotstart']);
	                            $rows[$slot_recurring['slotid']]['end']         = date("h:i:s a", $slot_recurring['slotend']);
	                            $status                                         = '<span style="color:#66FF33">free</span>';
	                            $rows[$slot_recurring['slotid']]['status'] = ucfirst($status);
	                            $rows[$slot_recurring['slotid']]['options'] = 'onclick="popdate_(\'' . $pdate . '\',\'' . $slot_recurring['slotid'] . '\',\'' . $slot_recurring['slotstart'] . '\',\'' . $slot_recurring['slotend'] . '\',\'' . $slot_recurring['client_contactid'] . '\',\'' . $slot_recurring['clientid'] . '\',\'' . $slot_recurring['recurring'] . '\')"';
	                    	}    
						}
                    }
                }
            } else if ($slot_recurring['recurring'] == "Monthly") {
            	$datetime1      = new DateTime($slot_recurring['tempcalculateddate']);
                $datetime2      = new DateTime($datenow);
                $interval       = $datetime1->diff($datetime2);
                $splitdate      = $interval->format('%y years,%m months,%d days');
				$recurrenceenddate	= $slot_recurring['recurrenceenddate'];
                $splitformonths = explode(",", $splitdate);
                $numforyears  = $splitformonths[0];
                $numformonths = $splitformonths[1];
                $numfordays   = $splitformonths[2];
                $splityears  = explode(" ", $numforyears);
                $splitmonths = explode(" ", $numformonths);
                $splitdays   = explode(" ", $numfordays);
                if ($splityears[0] == 0 && $splitmonths[0] > 0 && $splitdays[0] == 0) {
                    if (strtotime($slot_recurring['tempcalculateddate']) > strtotime($datenow)) {
                        $months         = "-" . $splitmonths[0] . " months";
                        $calculate      = strtotime($months, strtotime($slot_recurring['tempcalculateddate']));
                        $calculateddate = date('Y-m-d', $calculate);
                    } else if (strtotime($slot_recurring['tempcalculateddate']) < strtotime($datenow)) {
                        $months         = "+" . $splitmonths[0] . " months";
                        $calculate      = strtotime($months, strtotime($slot_recurring['tempcalculateddate']));
                        $calculateddate = date('Y-m-d', $calculate);
                    }
                    if (strtotime($calculateddate) == strtotime($datenow)) {
                    	if ($calculateddate <= $recurrenceenddate) {
	                    	$pdate                                          = $calculateddate . " " . date("h:i:s a", $slot_recurring['slotstart']);
		                    $rows[$slot_recurring['slotid']]['contactname'] = $slot_recurring['firstname'] . ' ' . $slot_recurring['lastname'];
		                    $rows[$slot_recurring['slotid']]['date']        = $calculateddate;
		                    $rows[$slot_recurring['slotid']]['start']       = date("h:i:s a", $slot_recurring['slotstart']);
		                    $rows[$slot_recurring['slotid']]['end']         = date("h:i:s a", $slot_recurring['slotend']);
		                    $status                                         = '<span style="color:#66FF33">free</span>';
		                    $rows[$slot_recurring['slotid']]['status'] = ucfirst($status);
		                    $rows[$slot_recurring['slotid']]['options'] = 'onclick="popdate_(\'' . $pdate . '\',\'' . $slot_recurring['slotid'] . '\',\'' . $slot_recurring['slotstart'] . '\',\'' . $slot_recurring['slotend'] . '\',\'' . $slot_recurring['client_contactid'] . '\',\'' . $slot_recurring['clientid'] . '\',\'' . $slot_recurring['recurring'] . '\')"';
		            	}
					}
                } else if ($splityears[0] > 0 && $splitmonths[0] > 0 && $splitdays[0] == 0) {
                    if (strtotime($slot_recurring['tempcalculateddate']) > strtotime($datenow)) {
                        $yearsintomonths  = $splityears[0] * 12;
                        $addyearstomonths = $yearsintomonths + $splitmonths[0];
                        $months           = "-" . $addyearstomonths . " months";
                        $calculate      = strtotime($months, strtotime($slot_recurring['tempcalculateddate']));
                        $calculateddate = date('Y-m-d', $calculate);
                    } else if (strtotime($slot_recurring['tempcalculateddate']) < strtotime($datenow)) {
                        $yearsintomonths  = $splityears[0] * 12;
                        $addyearstomonths = $yearsintomonths + $splitmonths[0];
                        $months           = "+" . $addyearstomonths . " months";
                        $calculate      = strtotime($months, strtotime($slot_recurring['tempcalculateddate']));
                        $calculateddate = date('Y-m-d', $calculate);
                    }
                    if (strtotime($calculateddate) == strtotime($datenow)) {
                    	if ($calculateddate <= $recurrenceenddate) {
                    		$pdate                                          = $calculateddate . " " . date("h:i:s a", $slot_recurring['slotstart']);
		                    $rows[$slot_recurring['slotid']]['contactname'] = $slot_recurring['firstname'] . ' ' . $slot_recurring['lastname'];
		                    $rows[$slot_recurring['slotid']]['date']        = $calculateddate;
		                    $rows[$slot_recurring['slotid']]['start']       = date("h:i:s a", $slot_recurring['slotstart']);
		                    $rows[$slot_recurring['slotid']]['end']         = date("h:i:s a", $slot_recurring['slotend']);
		                    $status                                         = '<span style="color:#66FF33">free</span>';
		                    $rows[$slot_recurring['slotid']]['status'] = ucfirst($status);
		                    $rows[$slot_recurring['slotid']]['options'] = 'onclick="popdate_(\'' . $pdate . '\',\'' . $slot_recurring['slotid'] . '\',\'' . $slot_recurring['slotstart'] . '\',\'' . $slot_recurring['slotend'] . '\',\'' . $slot_recurring['client_contactid'] . '\',\'' . $slot_recurring['clientid'] . '\',\'' . $slot_recurring['recurring'] . '\')"';
						}
                    }
                }  else if ($splityears[0] > 0 && $splitmonths[0] > 0 && $splitdays[0] > 0) {
                    if (strtotime($slot_recurring['tempcalculateddate']) > strtotime($datenow)) {
                        $yearsintomonths  = $splityears[0] * 12;
                        $addyearstomonths = $yearsintomonths + $splitmonths[0];
                        $months           = "-" . $addyearstomonths . " months";
                        $calculate      = strtotime($months, strtotime($slot_recurring['tempcalculateddate']));
                        $calculateddate = date('Y-m-d', $calculate);
                    } else if (strtotime($slot_recurring['tempcalculateddate']) < strtotime($datenow)) {
                        $yearsintomonths  = $splityears[0] * 12;
                        $addyearstomonths = $yearsintomonths + $splitmonths[0];
                        $months           = "+" . $addyearstomonths . " months";
                        $calculate      = strtotime($months, strtotime($slot_recurring['tempcalculateddate']));
                        $calculateddate = date('Y-m-d', $calculate);
                    }
                    if (strtotime($calculateddate) == strtotime($datenow)) {
                    	if ($calculateddate <= $recurrenceenddate) {
                    		$pdate                                          = $calculateddate . " " . date("h:i:s a", $slot_recurring['slotstart']);
		                    $rows[$slot_recurring['slotid']]['contactname'] = $slot_recurring['firstname'] . ' ' . $slot_recurring['lastname'];
		                    $rows[$slot_recurring['slotid']]['date']        = $calculateddate;
		                    $rows[$slot_recurring['slotid']]['start']       = date("h:i:s a", $slot_recurring['slotstart']);
		                    $rows[$slot_recurring['slotid']]['end']         = date("h:i:s a", $slot_recurring['slotend']);
		                    $status                                         = '<span style="color:#66FF33">free</span>';
		                    $rows[$slot_recurring['slotid']]['status'] = ucfirst($status);
		                    $rows[$slot_recurring['slotid']]['options'] = 'onclick="popdate_(\'' . $pdate . '\',\'' . $slot_recurring['slotid'] . '\',\'' . $slot_recurring['slotstart'] . '\',\'' . $slot_recurring['slotend'] . '\',\'' . $slot_recurring['client_contactid'] . '\',\'' . $slot_recurring['clientid'] . '\',\'' . $slot_recurring['recurring'] . '\')"';
						}
                    }
                } else if ($splityears[0] == 0 && $splitmonths[0] == 0 && $splitdays[0] == 0) {
                    if (strtotime($slot_recurring['tempcalculateddate']) == strtotime($datenow)) {
                    	$pdate                                          = $calculateddate . " " . date("h:i:s a", $slot_recurring['slotstart']);
	                    $rows[$slot_recurring['slotid']]['contactname'] = $slot_recurring['firstname'] . ' ' . $slot_recurring['lastname'];
	                    $rows[$slot_recurring['slotid']]['date']        = $calculateddate;
	                    $rows[$slot_recurring['slotid']]['start']       = date("h:i:s a", $slot_recurring['slotstart']);
	                    $rows[$slot_recurring['slotid']]['end']         = date("h:i:s a", $slot_recurring['slotend']);
	                    $status                                         = '<span style="color:#66FF33">free</span>';
	                    $rows[$slot_recurring['slotid']]['status'] = ucfirst($status);
	                    $rows[$slot_recurring['slotid']]['options'] = 'onclick="popdate_(\'' . $pdate . '\',\'' . $slot_recurring['slotid'] . '\',\'' . $slot_recurring['slotstart'] . '\',\'' . $slot_recurring['slotend'] . '\',\'' . $slot_recurring['client_contactid'] . '\',\'' . $slot_recurring['clientid'] . '\',\'' . $slot_recurring['recurring'] . '\')"';
                    }
                }
            } else if ($slot_recurring['recurring'] == "Yearly") {
            	$datetime1      = new DateTime($slot_recurring['tempcalculateddate']);
                $datetime2      = new DateTime($datenow);
                $interval       = $datetime1->diff($datetime2);
                $splitdate      = $interval->format('%y years,%m months,%d days');
                $recurrenceenddate	= $slot_recurring['recurrenceenddate'];
                $splitformonths = explode(",", $splitdate);
                $numforyears  = $splitformonths[0];
                $numformonths = $splitformonths[1];
                $numfordays   = $splitformonths[2];
                $splityears  = explode(" ", $numforyears);
                $splitmonths = explode(" ", $numformonths);
                $splitdays   = explode(" ", $numfordays);
                if ($splityears[0] > 0 && $splitmonths[0] == 0 && $splitdays[0] == 0) {
                    if (strtotime($slot_recurring['tempcalculateddate']) > strtotime($datenow)) {
                        $years = "-" . $splityears[0] . " years";
                        $calculate      = strtotime($years, strtotime($slot_recurring['tempcalculateddate']));
                        $calculateddate = date('Y-m-d', $calculate);
                    } else if (strtotime($slot_recurring['tempcalculateddate']) < strtotime($datenow)) {
                        $years = "+" . $splityears[0] . " years";
                        $calculate      = strtotime($years, strtotime($slot_recurring['tempcalculateddate']));
                        $calculateddate = date('Y-m-d', $calculate);
                    }
                    if (strtotime($calculateddate) == strtotime($datenow)) {
                    	if ($calculateddate <= $recurrenceenddate) {
                    		$pdate                                          = $calculateddate . " " . date("h:i:s a", $slot_recurring['slotstart']);
		                    $rows[$slot_recurring['slotid']]['contactname'] = $slot_recurring['firstname'] . ' ' . $slot_recurring['lastname'];
		                    $rows[$slot_recurring['slotid']]['date']        = $calculateddate;
		                    $rows[$slot_recurring['slotid']]['start']       = date("h:i:s a", $slot_recurring['slotstart']);
		                    $rows[$slot_recurring['slotid']]['end']         = date("h:i:s a", $slot_recurring['slotend']);
		                    $status                                         = '<span style="color:#66FF33">free</span>';
		                    $rows[$slot_recurring['slotid']]['status'] = ucfirst($status);
		                    $rows[$slot_recurring['slotid']]['options'] = 'onclick="popdate_(\'' . $pdate . '\',\'' . $slot_recurring['slotid'] . '\',\'' . $slot_recurring['slotstart'] . '\',\'' . $slot_recurring['slotend'] . '\',\'' . $slot_recurring['client_contactid'] . '\',\'' . $slot_recurring['clientid'] . '\',\'' . $slot_recurring['recurring'] . '\')"';
						}
                	}
                } else if ($splityears[0] == 0 && $splitmonths[0] == 0 && $splitdays[0] == 0) {
                    if (strtotime($slot_recurring['tempcalculateddate']) == strtotime($datenow)) {
                    	$pdate                                          = $calculateddate . " " . date("h:i:s a", $slot_recurring['slotstart']);
	                    $rows[$slot_recurring['slotid']]['contactname'] = $slot_recurring['firstname'] . ' ' . $slot_recurring['lastname'];
	                    $rows[$slot_recurring['slotid']]['date']        = $calculateddate;
	                    $rows[$slot_recurring['slotid']]['start']       = date("h:i:s a", $slot_recurring['slotstart']);
	                    $rows[$slot_recurring['slotid']]['end']         = date("h:i:s a", $slot_recurring['slotend']);
	                    $status                                         = '<span style="color:#66FF33">free</span>';
	                    $rows[$slot_recurring['slotid']]['status'] = ucfirst($status);
	                    $rows[$slot_recurring['slotid']]['options'] = 'onclick="popdate_(\'' . $pdate . '\',\'' . $slot_recurring['slotid'] . '\',\'' . $slot_recurring['slotstart'] . '\',\'' . $slot_recurring['slotend'] . '\',\'' . $slot_recurring['client_contactid'] . '\',\'' . $slot_recurring['clientid'] . '\',\'' . $slot_recurring['recurring'] . '\')"';
                    }
                }
            }
        }
    }
?>	
	<h3>Appointment Slots for: <?php
	echo strlen($date) > 32 ? substr($date, 0, 32) . "..." : $date;
	?></h3>
	<?
	echo tablegen($headers, $rows, 600, '', 'datatabs');
	?>
	<script text='javascript'>
		console.log("fdate: "+"<?= $fdate; ?>");
	</script>
	<?
	exit;
	}
	$cres = mysql_query("SELECT * from client_contacts where clientid = '$cid' and bcid = '$bcid' and active = '1'");
	while ($crow = mysql_fetch_assoc($cres)) {
	    $selected = " ";
	    if ($crow['client_contactid'] == $ci)
	        $selected = "selected = \"selected\"";
	    $clist .= '<option value="' . $crow['client_contactid'] . '" ' . $selected . ' >' . $crow['firstname'] . ' ' . $crow['lastname'] . '</option>';
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Ajax Calander</title>
<script type="text/javascript" src="../../jquery/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../jquery/js/jquery-ui-1.8.10.custom.min.js"></script>
<script type="text/javascript" src="../../jquery/timepicker/jquery.ui.timepicker.js"></script><script src="../../jquery/js/jquery.multiselect.js"></script>
<link href="../../jquery/css/redmond/jquery-ui-1.8.12.custom.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="../../jquery/timepicker/jquery.ui.timepicker.css"/>
<link rel="stylesheet" type="text/css" href="../../admin/styles/style.css"/><link href="../../jquery/css/jquery.multiselect.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
$(document).ready(function(){
	//FOR MULTIPLE SELECT
    $("#checkAll").click(function(){

        $('#ci option').prop('selected', true);
        $('#checkAll').attr("checked", "checked");
        $('#unCheckAll').removeAttr("checked");
    });
    $("#unCheckAll").click(function(){

        $('#ci option').prop('selected', false);
        $('#unCheckAll').attr("checked", "checked");
        $('#checkAll').removeAttr("checked");
     });
});
function slots(dt)
	{
		var ci =$("#ci").val();
		$.ajax({
  		url: "appbook.php?act=slots&date="+dt+"&ci="+ci,
  		success: function(data){
    	 $('#slots').html(data);
  	}
	});
	}

function agclick(dt)
	{
		parent.getusersched(dt);
	}
	
function appclick(dt)
	{
            console.log('appclick');
		var ci =$("#ci").val();
		$.ajax({
  		url: "appbook.php?act=slots&date="+dt+"&ci="+ci,
  		success: function(data){
    	 $('#slots').html(data);
		 $("#slots").dialog({width:500,height:300, close: showMonthViewCalender});
  	}
	});	

	}
function appselect() {
	console.log('appselect');
	var ci =$("#ci").val();
    var dates = '';
    var ct = 0;
    $(".ui-selected").each(function(){
        if (ct > 0) dates += ',';
        dates += $(this).attr('id');
        ct = ct + 1;
    });
    if (dates.length > 1) {
    	if (dates.indexOf(',') > -1) {
    		//do nothing
    	} else {
    		$.ajax({
				url: "appbook.php?act=slots&date="+dates+"&ci="+ci,
				success: function(data){
			        $('#slots').html(data);
					$("#slots").dialog({width:500,height:300, close: showMonthViewCalender});
					$(".jbut").button();
				}
			});
    	}
	}
}
var mon = '';
function showMonthViewCalender(str){
		$('#month_view_calender').html("");
		var ci =$("#ci").val();
			//First build the navigation panel
		var uri="appbookback.php"
        uri=uri+"?mon="+mon
		uri=uri+"&ci="+ci+"&sid="+Math.random()
		$.ajax({
  		url: uri,  		
  		success: function(data){					
                    $('#month_view_calender').html(data);
                    $("#caltable").selectable({
                        filter: 'td.selectable',
                        stop: appselect
                       });
                   }
	});	
}

function monthViewCalNavigation(mons){
        mon = mons;
		var ci =$("#ci").val();
			//First build the navigation panel
		var uri="appbookback.php"
		uri=uri+"?mon="+mon
		uri=uri+"&ci="+ci+"&sid="+Math.random()
		$.ajax({
  		url: uri,
  		success: function(data){					
                    $('#month_view_calender').html(data);
                     $("#caltable").selectable({
                          filter: 'td.selectable',
                          stop: appselect
                       });
                   }
	});		
}
</script>
<link  href="../../sched/css/style.css" rel="stylesheet" type="text/css"  />
<style>
a {
	color:#069;
	text-decoration:underline;
}
td.tdhover {
	background-color: #00FF66;
	cursor:pointer;
}
td.tdhover_ {
	background-color: #00A3EF;
	cursor:pointer;
}

td.tdhover:hover {
	background-color:#0FC;
}
td.tdhover_:hover {
	background-color:#936;
}
td.tdhover_.ui-selecting {
    background-color:#00A3EF;
  }
td.tdhover_.ui-selected {
    background-color:#00A3EF;
  }
td.tdhover.ui-selecting {
    background-color:#0FC;
  }
td.tdhover.ui-selected {
    background-color:#0FC;
  }.container {	   overflow: auto;   }.left {		float:left;	}.right {		float:right;}​​
</style>
<link href="../../admin/styles/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<h4>Select one or more to apply the Appointment Slot:</h4>	
	<div id="multiple_client_contacts" class="left">	
		<?= createinput("clientid", $cid, "hidden"); ?>		
		<select name="ci" id="ci" onChange="showMonthViewCalender('hello')" multiple="multiple" size="20">			
			<?= $clist; ?>		
		</select>
		<br/>
		<input type="radio" id="checkAll">Select All</input>
		<input type="radio" id="unCheckAll">Un-select All</input>
	</div>
	<div id="month_view_calender" class="right">	
	</div>
<div id="slots" style="display:none; width:500px;height:300px;">		
</div>
</body>
<script type="text/javascript">
 showMonthViewCalender('hello');
 $(document).ready(function (){
 });
</script>
</html>