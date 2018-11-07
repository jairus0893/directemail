<?php
session_start();
error_reporting(0);
include "../../dbconnect.php";
include "../../admin/phpfunctions.php";
$ci = $_REQUEST['ci'];

//This gets today's date
$date     = time();
$usertype = $_SESSION['usertype'];

//This puts the day, month, and year in seperate variables
$day = date('d', $date);

//For Regular Slot
$unconres = mysql_query("SELECT client_contact_slots.* 
						FROM client_contact_slots 
						WHERE client_contact_slots.client_contactid IN ($ci)");

while ($urow = mysql_fetch_array($unconres)) {
    $timeslots[$urow['date']][$urow['slotid']] = $urow;
	if ($urow["taken"] == 0) {
		$timeslots[$urow['date']]['appcount']      = $timeslots[$urow['date']]['appcount'] + 1;
	}
}
//For Recurring Slot
$unconres_recurring = mysql_query("SELECT client_recurring_slots1.*, client_contact_slots.taken 
								FROM client_recurring_slots1 
								JOIN client_contact_slots ON client_contact_slots.slotid = client_recurring_slots1.clientcontactslotsid 
								WHERE client_recurring_slots1.client_contactid in ($ci)");

while ($urow_recurring = mysql_fetch_array($unconres_recurring)) {
    $timeslots_recurring[] = $urow_recurring;
}
/*****************************************************************************************************************/
if ((checkrights('admin_portal') || $usertype = 'client') && $_REQUEST['mode'] != 'booking') {
    $legend = '<div id="legend" style="padding:10px">
	<div style="background-color:#00FF66; width:15px; height:10px; float:left"></div><div style="float:left; padding-left:10px">With Added Appointment Time Slots</div>
	<div style="clear:both"></div>
	</div>';
} else {
    $uu       = $_SESSION['uid'];
    $unconres = mysql_query("SELECT SUBSTR(dtime,1,10) AS sdate, SUBSTR(dtime,12,5) AS stime FROM dateandtime WHERE ci = '$ci';");
    
    while ($urow = mysql_fetch_array($unconres)) {
        $appointments[$urow['sdate']][] = $urow['stime'];
        if ($appointments[$urow['sdate']]['appcount'] > 0) {
            $appointments[$urow['sdate']]['appcount'] = $appointments[$urow['sdate']]['appcount'] + 1;
        } else {
            $appointments[$urow['sdate']]['appcount'] = 1;
        }
    }
    $legend = '<div id="legend" style="padding:10px">
	<div style="background-color:#00FF66; width:15px; height:10px; float:left"></div><div style="float:left; padding-left:10px">With Added Appointment Time Slots</div>
	<div style="clear:both"></div>
	<div style="background-color:#00A3EF; width:15px; height:10px; float:left"></div><div style="float:left; padding-left:10px">No Appointments</div>
	<div style="clear:both"></div>
	</div>';
}
if ($_REQUEST['mon'] != "") {
    $month = $_REQUEST['mon'];
} else {
    $month = date('m', $date);
}
$yearCal = date("n/j/Y", mktime(0, 0, 0, $month, date("d") - date("d") + 1, date("Y")));
$string = strtotime($yearCal);
$year = date('Y', $string);
$next_month = $month + 1;
$prev_month = $month - 1;
if ($month > 12)
    $fmonth = $month % 12;
else
    $fmonth = $month;
//Here we generate the first day of the month
$first_day = mktime(0, 0, 0, $fmonth, 1, $year);
//This gets us the month name
$title = date('F', $first_day);
//Here we find out what day of the week the first day of the month falls on
$day_of_week = date('D', $first_day);
//Here we find out what day of the week the first day of the month falls on
$dtFirstDay = date("n/j/y", mktime(0, 0, 0, $month, date("d") - date("d") + 1, date("Y")));
$dtLastDay = date("n/j/y", mktime(0, 0, 0, $month + 1, date("d") - date("d"), date("Y")));
//Once we know what day of the week it falls on, we know how many blank days occure before it. If the first day of the week is a Sunday then it would be zero
switch ($day_of_week) {
    case "Sun":
        $blank = 0;
        break;
    case "Mon":
        $blank = 1;
        break;
    case "Tue":
        $blank = 2;
        break;
    case "Wed":
        $blank = 3;
        break;
    case "Thu":
        $blank = 4;
        break;
    case "Fri":
        $blank = 5;
        break;
    case "Sat":
        $blank = 6;
        break;
}
// making timestamp for month foreg: if month value is 13 then it will be 01
$monthStamp    = date("m", mktime(0, 0, 0, $month, 1, $year));
//We then determine how many days are in the current month
$days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year)); 
// Navigation for the monthly calender view
$Prenavigation = "<a href=\"#\" onclick=\"javascript:monthViewCalNavigation('" . $prev_month . "','" . $ci . "');\">
<< </a>";
$Nextnavigation = "<a href=\"#\" onclick=\"javascript:monthViewCalNavigation('" . $next_month . "','" . $ci . "');\">
>> </a>";
//Here we start building the table heads
echo "<table border=1 width=\"100%\" id=\"calheader\" style='border-collapse:collapse;'>";
echo "<tr><th colspan=7> ";
echo "<div class=\"my-calander-top\"><div class=\"clear\"></div><br />
		<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"pd-top\">
			<tr>
				<td width=\"20\">&nbsp;</td>
				<td width=\"8\" height=\"5\" align=\"center\" valign=\"middle\">" . $Prenavigation . "</td>
				<td width=\"434\" align=\"center\" valign=\"middle\">" . $title . "&nbsp;" . $year . "</td>                        
				<td width=\"8\" height=\"5\" align=\"center\" valign=\"middle\">" . $Nextnavigation . "</td>
				<td width=\"60\">&nbsp;</td>
			 </tr>
		</table>
	</div>";
echo "</th></tr>";
echo "</table><table border=1 width=\"100%\" id=\"caltable\" style='border-collapse:collapse;'>";
echo "<tr><td width=42>Sunday</td><td width=42>Monday</td><td width=42>Tuesday</td><td width=42>Wed</td><td width=42>Thursday</td><td width=42>Friday</td><td width=42>Saturday</td></tr>";
//This counts the days in the week, up to 7
$day_count = 1;
echo "<tr height='50'>";
//first we take care of those blank days
while ($blank > 0) {
    echo "<td></td>";
    $blank = $blank - 1;
    $day_count++;
}
//sets the first day of the month to 1
$day_num = 1;
//count up the days, untill we've done all of them in the month
while ($day_num <= $days_in_month) {
    if ($day_num < 10) {
        $dd = "0" . $day_num;
    } else {
        $dd = $day_num;
    }
    if ($fmonth < 10 && strlen($fmonth) < 2) {
        $mm = "0" . $fmonth;
    } else {
        $mm = $fmonth;
    }
    $rdate = $year . '-' . $mm . '-' . $dd;
    if ($usertype == 'admin') {
    } else {
        //For Regular Slot
        if ($timeslots[$rdate]['appcount'] > 0) {
            $color = "tdhover selectable";
        } else {
            $timeslots[$rdate]['appcount'] = "0";
            $color = "tdhover_ selectable";
        }
    }
    $tod = date("Y-m-d");
    if ($rdate <= $tod) {
        echo '<td > ' . $day_num . ' </td>';
    } elseif ($_REQUEST['mode'] == 'booking') {
        echo '<td  id="' . $rdate . '" class="' . $color . '"> ' . $day_num . '<br>';
        echo $timeslots[$rdate]['takencount'] > 0 ? $timeslots[$rdate]['takencount'] : "No";
        echo $timeslots[$rdate]['takencount'] > 1 ? ' Appointments' : ' Appointment';
        echo '</td>';
    } elseif (checkrights('admin_portal') || $usertype == 'client') {
        //For Recurring Slot
        $sum_daily   = 0;
        $sum_weekly  = 0;
        $sum_monthly = 0;
        $sum_yearly  = 0;
        $value       = 1;
        foreach ($timeslots_recurring as $timeslot_recurring) {
            $date    = $_REQUEST['date'];
            $datenow = $date;
            $tslot_recurring = array();
            if (strtotime($timeslot_recurring['date']) != strtotime($rdate)) {
                if ($timeslot_recurring['recurring'] == "Daily") {
                    $date1 = date_create($timeslot_recurring['date']);
                    $date2 = date_create($rdate);
                    $diff  = date_diff($date1, $date2);
                    $days  = $diff->format("%R%a days");
					$recurrenceenddate	= $timeslot_recurring['recurrenceenddate'];
					$notnegativesign = explode(" ", $days);
					if ($notnegativesign[0] > 0) {
						$calculate      = strtotime($days, strtotime($timeslot_recurring['date']));
	                    $calculateddate = date('Y-m-d', $calculate);
	                    if (strtotime($calculateddate) == strtotime($rdate)) {
	                    	if ($calculateddate <= $recurrenceenddate) {
	                    		$sum_daily += $value;
	                        	$color = "tdhover selectable";
	                    	} else {
	                    		$sum_daily = 0;
	                        	$color     = "tdhover_ selectable";
	                    	}
	                    } else if (strtotime($calculateddate) != strtotime($rdate)) {
	                        $sum_daily = 0;
	                        $color     = "tdhover_ selectable";
	                    }
					}
                } else if ($timeslot_recurring['recurring'] == "Weekly") {
                    $date1            	= date_create($timeslot_recurring['date']);
                    $date2            	= date_create($rdate);
                    $diff             	= date_diff($date1, $date2);
                    $numberofweeks    	= $diff->format("%R%a days");
                    $splitweeksresult 	= explode(" ", $numberofweeks);
                    $numweeks         	= $splitweeksresult[0] / 7;
					$recurrenceenddate	= $timeslot_recurring['recurrenceenddate'];
					$notnegativesign = explode(" ", $numberofweeks);
					if ($notnegativesign[0] > 0) {
	                  	if (is_int($numweeks)) {
	                        $weeks = $numweeks . " weeks";
	                        $calculate      = strtotime($weeks, strtotime($timeslot_recurring['date']));
	                        $calculateddate = date('Y-m-d', $calculate);
	                        if (strtotime($calculateddate) == strtotime($rdate)) {
	                        	if ($calculateddate <= $recurrenceenddate) {
	                        		$sum_weekly += $value;
	                            	$color = "tdhover selectable";
								} else {
		                    		$sum_weekly = 0;
		                        	$color     = "tdhover_ selectable";
		                    	}
	                        } else if (strtotime($calculateddate) != strtotime($rdate)) {
	                            $sum_weekly = 0;
	                            $color      = "tdhover_ selectable";
	                        }
	                    }
					}
                } else if ($timeslot_recurring['recurring'] == "Monthly") {
                    $date1          = date_create($timeslot_recurring['date']);
                    $date2          = date_create($rdate);
                    $diff           = date_diff($date1, $date2);
                    $splitdate      = $diff->format('%y years,%m months,%d days');
					$recurrenceenddate	= $timeslot_recurring['recurrenceenddate'];
                    $splitformonths = explode(",", $splitdate);
                    $numforyears  = $splitformonths[0];
                    $numformonths = $splitformonths[1];
                    $numfordays   = $splitformonths[2];
                    $splityears  = explode(" ", $numforyears);
                    $splitmonths = explode(" ", $numformonths);
                    $splitdays   = explode(" ", $numfordays);
                    if ($splityears[0] == 0 && $splitdays[0] == 0) {
                        if (strtotime($timeslot_recurring['date']) > strtotime($rdate)) {
                            $months         = "-" . $splitmonths[0] . " months";
                            $calculate      = strtotime($months, strtotime($timeslot_recurring['date']));
                            $calculateddate = date('Y-m-d', $calculate);
                            if (strtotime($calculateddate) == strtotime($rdate)) {
                            	if ($calculateddate <= $recurrenceenddate) {
	                                $sum_monthly += $value;
	                                $color = "tdhover selectable";
								}
                            } else if (strtotime($calculateddate) != strtotime($rdate)) {
                                $sum_monthly = 0;
                                $color       = "tdhover_ selectable";
                            }
                        } else if (strtotime($timeslot_recurring['date']) < strtotime($rdate)) {
                            $months         = "+" . $splitmonths[0] . " months";
                            $calculate      = strtotime($months, strtotime($timeslot_recurring['date']));
                            $calculateddate = date('Y-m-d', $calculate);
                            
                            if (strtotime($calculateddate) == strtotime($rdate)) {
                            	if ($calculateddate <= $recurrenceenddate) {
	                                $sum_monthly += $value;
	                                $color = "tdhover selectable";
								}
                            } else if (strtotime($calculateddate) != strtotime($rdate)) {
                                $sum_monthly = 0;
                                $color       = "tdhover_ selectable";
                            }
                        }
                    } else if ($splityears[0] > 0 && $splitdays[0] == 0) {
                        if (strtotime($timeslot_recurring['date']) > strtotime($rdate)) {
                            $yearsintomonths  = $splityears[0] * 12;
                            $addyearstomonths = $yearsintomonths + $splitmonths[0];
                            $months           = "-" . $addyearstomonths . " months";
                            $calculate      = strtotime($months, strtotime($timeslot_recurring['date']));
                            $calculateddate = date('Y-m-d', $calculate);
                            if (strtotime($calculateddate) == strtotime($rdate)) {
                            	if ($calculateddate <= $recurrenceenddate) {
	                                $sum_monthly += $value;
	                                $color = "tdhover selectable";
								}
                            } else if (strtotime($calculateddate) != strtotime($rdate)) {
                                $sum_monthly = 0;
                                $color       = "tdhover_ selectable";
                            }
                        } else if (strtotime($timeslot_recurring['date']) < strtotime($rdate)) {
                            $yearsintomonths  = $splityears[0] * 12;
                            $addyearstomonths = $yearsintomonths + $splitmonths[0];
                            $months           = "+" . $addyearstomonths . " months";
                            $calculate      = strtotime($months, strtotime($timeslot_recurring['date']));
                            $calculateddate = date('Y-m-d', $calculate);
                            if (strtotime($calculateddate) == strtotime($rdate)) {
                            	if ($calculateddate <= $recurrenceenddate) {
	                                $sum_monthly += $value;
	                                $color = "tdhover selectable";
								}
                            } else if (strtotime($calculateddate) != strtotime($rdate)) {
                                $sum_monthly = 0;
                                $color       = "tdhover_ selectable";
                            }
                        }
                    }
                } else if ($timeslot_recurring['recurring'] == "Yearly") {
                    $datetime1      = new DateTime($timeslot_recurring['date']);
                    $datetime2      = new DateTime($rdate);
                    $interval       = $datetime1->diff($datetime2);
                    $splitdate      = $interval->format('%y years,%m months,%d days');
					$recurrenceenddate	= $timeslot_recurring['recurrenceenddate'];
                    $splitformonths = explode(",", $splitdate);
                    $numforyears  = $splitformonths[0];
                    $numformonths = $splitformonths[1];
                    $numfordays   = $splitformonths[2];
                    $splityears  = explode(" ", $numforyears);
                    $splitmonths = explode(" ", $numformonths);
                    $splitdays   = explode(" ", $numfordays);
                    if ($splityears[0] > 0 && $splitmonths[0] == 0 && $splitdays[0] == 0) {
                        if (strtotime($timeslot_recurring['date']) > strtotime($rdate)) {
                            $years = "-" . $splityears[0] . " years";
                            $calculate      = strtotime($years, strtotime($timeslot_recurring['date']));
                            $calculateddate = date('Y-m-d', $calculate);
                        } else if (strtotime($timeslot_recurring['date']) < strtotime($rdate)) {
                            $years = "+" . $splityears[0] . " years";
                            $calculate      = strtotime($years, strtotime($timeslot_recurring['date']));
                            $calculateddate = date('Y-m-d', $calculate);
                        }
                        if (strtotime($calculateddate) == strtotime($rdate)) {
                        	if ($calculateddate <= $recurrenceenddate) {
	                            $sum_yearly += $value;
	                            $color = "tdhover selectable";
							}
                        } else if (strtotime($calculateddate) != strtotime($rdate)) {
                            $sum_yearly = 0;
                            $color      = "tdhover_ selectable";
                        }
                    }
                }
            }
        }
        $tslot                 = $timeslots[$rdate]['appcount'];
        $total_tslot_recurring = $sum_daily + $sum_weekly + $sum_monthly + $sum_yearly;
        if (strlen($tslot) == 0 && strlen($total_tslot_recurring) == 0) {
            $tslot_final_count = '0';
        } else if (strlen($tslot) > 0 && strlen($total_tslot_recurring) == 0) {
            $tslot_final_count = $tslot;
        } else if (strlen($tslot) == 0 && strlen($total_tslot_recurring) > 0) {
            $tslot_final_count = $total_tslot_recurring;
        } else if (strlen($tslot) > 0 && strlen($total_tslot_recurring) > 0) {
            $tslot_final_count = $tslot + $total_tslot_recurring;
        }
        if ($tslot_final_count > 0) {
        	$color = "tdhover selectable";
        } else if ($tslot_final_count == 0) {
        	$color = "tdhover_ selectable";	
		}
        echo '<td id="' . $rdate . '" class="' . $color . '"> ' . $day_num . '<br> ' . $tslot_final_count . ' Time Slot(s) </td>';
    } else {
        echo '<td  id="' . $rdate . '" onclick="appclick(\'' . $rdate . '\')"  class="' . $color . '"> ' . $day_num . '<br>' . $appointments[$rdate]['appcount'] . ' Appointments</td>';
    }
    $day_num++;
    $day_count++;
    //Make sure we start a new row every week
    if ($day_count > 7) {
        echo "</tr><tr  height='50'>";
        $day_count = 1;
    }
}
//Finaly we finish out the table with some blank details if needed
while ($day_count > 1 && $day_count <= 7) {
    echo "<td> </td>";
    $day_count++;
}
echo "</tr></table>";
echo $legend;
?>