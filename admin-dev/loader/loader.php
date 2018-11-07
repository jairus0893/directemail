#!/usr/bin/php
<?php
include "dbconfig.php";

while (true)
{
    include "dbping.php";
    
    $projres = mysql_query("SELECT projects.*, COUNT(hopper.phone) AS hoppercount FROM projects LEFT JOIN hopper ON 
	projects.projectid = hopper.projectid WHERE projects.active = 1 and dialmode != 'inbound' and substr(projects.lastactive,1,10) > 
	'2017-01-00' GROUP BY projectid;");
	while ($proj = mysql_fetch_array($projres))
	{
	    if ($proj['hoppercount'] < $proj['queue_min'])
	    {
	        $shortage = $proj['queue_max'];
	        $projid = $proj['projectid'];
	        $pacing = $proj['dialpace'];
	        $leads = array();
			// $dnclistres = mysql_query("SELECT id, newwashdate, dnc_listid from donotcall_list where dnc_projectid = '".$projid."' AND dnc_active = 1 AND dnc_is_deleted = 0");
			// $ct = 0;
	        // $dn = mysql_num_rows($dnclistres);
	        // $dnclist = array();
			// $date_now = date("Y-m-d");
			// $dncliststr = "";
			// while ($dnclistrow = mysql_fetch_assoc($dnclistres)) {
				// if (date("Y-m-d", $dnclistrow["newwashdate"]) >= $date_now) {
					// $dncres = mysql_query("SELECT phone from donotcall where dncid = '".$dnclistrow["id"]."'");
					// while ($dnc = mysql_fetch_assoc($dncres)) {
						// if ($ct != 0) {
			            	// $dncliststr .= ",";
			            // }
			            // $dncliststr .= "'".$dnc["phone"]."'";
			            // $dnclist[] = $dnc["phone"];
			            // $ct++;
					// }
				// } else {
// 					
				// }
			// }
			// if ($dncliststr != "") {
				// $dncliststring = 'AND phone NOT IN ('.$dncliststr.')';
			// } else {
				// $dncliststring = "";
			// }
	        $level = '200';
	        $liststr = "";
	        $recycle = $proj['recycle'];
	        if (strlen($recycle)==0)
	        {
	        	$recycle = 0;
	        }
	        $actualrecycle = $recycle * 3600;
	        $listres = mysql_query("SELECT * from lists where projects = '".$projid."' and active = 1 order by RAND()");
	        $ct = 0;
	        while ($list = mysql_fetch_array($listres))
	        {
	        	if ($ct != 0)
	        	{
	        		$liststr .= ",";
	        	}
	        	$liststr .= "'".$list['listid']."'";
	        	$ct++;
	        }
	        $filters = '';
	        $fct = 0;
	        $filres = mysql_query("SELECT * from filters where projectid = '".$projid."' and active = '1'");
	        if (mysql_num_rows($filres) != 0)
	        {
	            while ($filrow = mysql_fetch_array($filres))
	            {
	            	$filters.= " and ";
	            	$filters.= $filrow['filterdata'];
	            	$fct++;
	            }
	        }
	        $statstr = "";
	        $statres = mysql_query("SELECT statusname from statuses where category = 'callable' and active = 1 and projectid in (0, -1, $projid)");
	        $ct = 0;
	        while ($stat = mysql_fetch_array($statres))
	        {
	            if ($ct != 0)
	            {
	                $statstr .= ",";
	            }
	            $statstr .= "'".$stat['statusname']."'";
	            $ct++;
	        }
	        $nonstatstr = "";
	        $nonarr = array();
	        $nonstatres = mysql_query("SELECT statusname from statuses where category = 'final' and active = 1 AND projectid in (0, -1, $projid)");
	        $ct = 0;
	        while ($nonstat = mysql_fetch_array($nonstatres))
	        {
	            if ($ct != 0)
	            {
	                $nonstatstr .= ",";
	            }
	            
	            $nonstatstr .= "'".$nonstat['statusname']."'";
	            $nonarr[$nonstat['statusname']] = $nonstat['statusname'];
	            $ct++;
	        }
	        $ct = 0;
	        $insertleads = '';
	        $leadsloaded = '';
	        //$rawleadsquery = "SELECT * FROM leads_raw WHERE phone != '' ".$dncliststring." AND listid IN (".$liststr.") AND hopper = '0' ".$filters." AND epoch_callable < ".time()." AND dispo not IN ($nonstatstr) ORDER BY RAND() LIMIT ".$shortage;
	        $rawleadsquery = "SELECT * FROM leads_raw WHERE phone != '' AND phone NOT IN (SELECT phone FROM donotcall JOIN donotcall_list ON donotcall_list.id = donotcall.dncid WHERE dnc_projectid = '".$projid."' AND dnc_active = 1 AND dnc_is_deleted = 0) AND listid IN (".$liststr.") AND hopper = '0' ".$filters." AND epoch_callable < ".time()." AND dispo not IN ($nonstatstr) ORDER BY RAND() LIMIT ".$shortage;
	        $rawleads = mysql_query($rawleadsquery);
	        $shct = 0;
	        $rl = '';
			if (!empty($rawleads)) {
				while ($row = mysql_fetch_assoc($rawleads))
	            {
	                if ($shct > 0) {$rl .= ","; }
	            		$rl .= "'".$row['leadid']."'";
	                $leads[$row['leadid']] = $row;
	                $shct++;
	            }
			}
	        $shortage = $shortage - $shct;
	        if ($shct > 0)
	        {
	            $updaterawres = mysql_query("SELECT * from leads_done where leadid in ($rl)");
	            while ($rlrow = mysql_fetch_array($updaterawres))
	            {
	                $leads[$rlrow['leadid']] = $rlrow;
	                if (in_array($rlrow['dispo'],$nonarr))
	                {
	                    $shortage++;
	                }
	            }
	        }
	        if ($shortage > 0)
	        {
	            //$doneq = "SELECT * from leads_done where projectid = '".$projid."' ".$dncliststring." and listid in (".$liststr.") ".$filters." and hopper ='0' and dispo in ($statstr) and phone > 0 and dispo not in ($nonstatstr) and (UNIX_TIMESTAMP() - epoch_timeofcall) > ".$actualrecycle."  and epoch_callable < ".time()." ORDER BY RAND() LIMIT ".$shortage;
	            $doneq = "SELECT * from leads_done where projectid = '".$projid."' and phone NOT IN (SELECT phone FROM donotcall JOIN donotcall_list ON donotcall_list.id = donotcall.dncid WHERE dnc_projectid = '".$projid."' AND dnc_active = 1 AND dnc_is_deleted = 0) and listid in (".$liststr.") ".$filters." and hopper ='0' and dispo in ($statstr) and phone > 0 and dispo not in ($nonstatstr) and (UNIX_TIMESTAMP() - epoch_timeofcall) > ".$actualrecycle."  and epoch_callable < ".time()." ORDER BY RAND() LIMIT ".$shortage;
	            $doneleads = mysql_query($doneq);
				if (!empty($doneleads)) {
					while ($row = mysql_fetch_array($doneleads))
	                {
	                    $leads[$row['leadid']] = $row;
	                    $ct++;
	                }
				}
	            
	        }
	        $ct =0;
	        foreach ($leads as $row)
	        {
	        	if (in_array($row['dispo'],$nonarr))
	            {
	            	//echo $row['dispo']." : ".$row['leadid']." | ";
	           	}
	        	else	
	            {
	        		if ($ct > 0) {$leadsloaded .= ","; $insertleads .= ","; }
	        		$leadsloaded .= "'".$row['leadid']."'";
	         		$insertleads .= "('".$row['leadid']."','".$row['phone']."','0','".$projid."','0')";
	         		$ct++;
	        	}
	        }
	        if ($ct > 0)
	        {
	            mysql_query("INSERT into hopper(leadid,phone,called,projectid,reused) VALUES $insertleads");
	            $_insertHopper = mysql_affected_rows();
	
	            mysql_query("Update leads_raw set hopper = '1' where leadid  in ($leadsloaded)");
	            $_updateLeadsRaw = mysql_affected_rows();
	
	            mysql_query("Update leads_done set hopper = '1' where leadid  in ($leadsloaded)");
	            $_updateLeadsDone = mysql_affected_rows();
	
	
	            echo "\n\n";
	            echo date("Y-m-d h:i:s") . " CAMPAIGN: " . $proj['projectname'] . "\n";
	            echo date("Y-m-d h:i:s") . " ADDED $ct RECORD(s) into hopper for $projid list: $liststr \n";
	            echo date("Y-m-d h:i:s") . " " . $rawleadsquery . "\n";
	            echo date("Y-m-d h:i:s") . " hopper inserts    : " . $_insertHopper . "\n";
	            echo date("Y-m-d h:i:s") . " leads_raw updates : " . $_updateLeadsRaw . "\n"; 
	            echo date("Y-m-d h:i:s") . " leads_done updates: " . $_updateLeadsDone . "\n";
	
	            echo "\n\n" . date("Y-m-d h:i:s") . " Memory Usage: " .  memory_get_usage(true) . "\n";
	            echo date("Y-m-d h:i:s") . " DB Link Thread ID: " . $thread_id . "\n";
	        }
	    }
	    else
	    { 
	        // echo "\n\n NO SHORTAGE for ". $proj['projectname'];
	    }
	    $leads = '';
	}
	unset($dnclist);

    sleep(60);
}

