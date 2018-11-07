<?php
function createdropdown($options = array(),$namefield, $valuefield)
	{
		foreach ($options as $option)
			{
				$disp .= '<option value="'.$option[$valuefield].'">'.ucfirst($option[$namefield]).'</option>';
			}
		return $disp;
	}
function createinput($idname, $value ="", $type = "text", $class="")
	{
		return "<input type=\"$type\" id=\"$idname\" name=\"$idname\" value=\"$value\" class=\"$class\" />";
	}
function isbooked($c)
	{
		if ($c == 1 ) 
			{
			return "Booked";
			}
		elseif ($c == 2)
			{
			return "Locked";
			}
		else return "Free";
	}
function createdoc($type,$table)
	{
		$doctype["excel"] = "vnd.ms-excel";
		$doctype["word"] = "vnd.ms-word";
		$extension["excel"] = ".xls";
		$extension["word"] = ".doc";
		$filename = substr(md5(time()),-5);
		header("Content-type: application/".$doctype[$type]);
		header("Content-Disposition: attachment; filename=".$filename.$extension[$type]);
		header("Pragma: no-cache");
		header("Expires: 0");
		echo "<style>";
		include "cstyle.css";
		echo "</style>";
		echo $table;
		exit;
	}
function getroles($bcid)
	{
		$q = "SELECT * from roles where bcid in ('0','$bcid')";
		if ($bcid == 'all')
			{
				$q = "SELECT * from roles";
			}
		$res = mysql_query($q);
		while ($row = mysql_fetch_assoc($res))
			{
				$arr[$row['roleid']] = $row;
				$opt .= '<option value="'.$row['roleid'].'">'.$row['rolename'].'</option>';
			}
		$roles['array'] = $arr;
		$roles['options'] = $opt;
		return $roles;
	}
function newadmin($bcid,$params)
	{
		$userlogin = $params['userlog'];
		$userpass = $params['userp'];
		$role = $params['role'];
		if ($role < 1) $role = 1;
		if (checkuser($userlogin))
			{
				mysql_query("INSERT into members set userlogin = '$userlogin', userpass = '$userpass', usertype = 'admin', bcid = '$bcid', roleid = '$role'");
				return "New admin user added";
			}
		else 
			{
				return "Username already exists..";

			}
	}
function checkuser($userlogin)
	{
		$res = mysql_query("SELECT userid from members where userlogin = '$userlogin'");
		$n = mysql_num_rows($res);
		if ($n > 0 ) return false;
		else return TRUE;
	}
function getrights($roleid)
	{
		$res = mysql_query("SELECT * from roles where roleid = '$roleid'");
		$row = mysql_fetch_assoc($res);
		$rights = explode(",",$row['rights']);
		return $rights;
	}
function checkrights($right)
	{
		$rights = $_SESSION['rights'];
		if (array_search($right,$rights) === FALSE)
			{
				if (array_search("all",$rights) === FALSE)
					{
						echo '||<p style="color:#FF0000">Privilege Error! '.$right.' must included in your role.<br />Contact your administrator.</p>';
						exit;
					}
				else return TRUE;
			}
		else {
			return TRUE;
		}
	}
function getprojects($bcid)
	{
		$projres = mysql_query("SELECT * from projects where active = 1 and bcid = '$bcid' ;");
		$pp = 0;
		while ($projrow = mysql_fetch_array($projres))
			{
			$projects[$projrow['projectid']] = $projrow;
			$plist .= '<option value="'.$projrow['projectid'].'">'.$projrow['projectname'].'</option>';
			if ($pp > 0)
				{
					$plist_query .= ",";
				}
			$plist_query .= "'".$projrow['projectid']."'";
			$pp++;
			}
			
		$projectlist['list'] = $plist;
		$projectlist['pp'] = $pp;
		$projectlist['data'] = $projects;
		$projectlist['sql'] = $plist_query;
		return $projectlist;
	}
function tablegen($headers, $rows, $width = "770", $rowscript = NULL)
	{
		$table = '<table width="'.$width.'">';
		$table .= '<tr>';
		foreach ($headers as $header)
			{
				$table .= '<td class="tableheader">'.$header.'</td>';
			}
		$table .= '</tr>';
		$c = 1;
		foreach ($rows as $row)
			{
				$c++;
				if ($c % 2) $class = "tableitem";
				else $class = "tableitem_";
				$table .= '<tr>';
				foreach ($row as $item)
					{
						$table .= '<td class="'.$class.'">'.$item.'</td>';
					}
				$table .= '</tr>';
			}
		$table .= '</table>';
		return $table;
	}
function buyfeature($bcid,$feat)
	{
		$res = mysql_query("SELECT * from bc_features_details where feature = '$feat'");
		$feature = mysql_fetch_assoc($res);
		if ($feature['type'] == 'option')
			{
				$exp = getexpiry($feature['interval']);
				$cost = $feature['cost'];
				$wallet = getwallet($bcid);
				if ($cost > $wallet['loadedcredits'])
					{
						return "Insufficient Credits";
					}
				else {
					$newcredit = $wallet['loadedcredits'] - $cost;
					mysql_query("update bc_wallet set loadedcredits = '$newcredit' where bcid = '$bcid'");
					mysql_query("insert into bc_purchases set feature = '$feat', bcid = '$bcid', cost = '$cost', epoch = '".time()."'");
					mysql_query("update bc_features set $feat = 1 where bcid = '$bcid'");
					$fres = mysql_query("SELECT * from bc_features_exp where bcid = '$bcid' and feature = '$feat'");
					if (mysql_num_rows($fres) > 0)
					{
					$fexp = mysql_fetch_assoc($fres);
					if ($fexp['expdate'] < time())
						{
							mysql_query("update bc_features_exp set feature = '$feat', expdate = '".$exp['epoch']."' where bcid = '$bcid'");
						}
					else 
						{
							$nexp = $fexp['expdate'] + $exp['add'];
							mysql_query("update bc_features_exp set feature = '$feat', expdate = '".$nexp."' where bcid = '$bcid'");
						}
					}
					else mysql_query("insert into bc_features_exp set feature = '$feat', bcid = '$bcid', expdate = '".$exp['epoch']."'");
					return "done";
				}
			}
		else {
			mysql_query("update bc_features set inbound = 0,outbound = 0, blended = 0 where bcid = '$bcid'");
			mysql_query("update bc_features set $feat = 1 where bcid = '$bcid'");
			return done;
		}
	}
function getexpiry($interval)
	{
		$dur['monthly'] = 2592000;
		$dur['weekly'] = 604800;
		$dur['yearly'] = 31536000;
		$expire['epoch'] = time() + $dur[$interval];
		$expire['add'] = $dur[$interval];
		return $expire;
	}
function featurecheckexp($bcid,$feature)
	{
		$res = mysql_query("SELECT * from bc_features_exp where bcid = '$bcid' and feature = '$feature'");
		$r = mysql_fetch_array($res);
		if (mysql_num_rows($res) == 0)
			{
				return true;
			}
		elseif ($r['expdate'] > time())
			{
				
				return true;
			}
		else {
			mysql_query("updated bc_features set $feature = 0");
			return false;
		}
	}
function getdm($bcid)
	{
		$rate = getrates($bcid);
		$res = mysql_query("SELECT * from bc_features where bcid = '$bcid'");
		$r = mysql_fetch_assoc($res);
		$f = array_keys($r);
		foreach ($f as $fld)
			{
				if ($fld == 'inbound' || $fld == 'outbound' || $fld == 'blended')
					{
						if ($r[$fld] == 1) 
							{
							$ret['dm'] = $fld;
							$ret['rate'] = $rate[$fld];
							$ret['rateid'] = $rate['rateid'];
							}
					}
			}
			
		return $ret;
	}
function featurecheck($bcid,$feature)
	{
		$res = mysql_query("SELECT * from bc_features where bcid = '$bcid'");
		$r = mysql_fetch_array($res);
		if (mysql_num_rows($res) == 0)
			{
				return true;
			}
		elseif ($r[$feature] == 1)
			{
				if (featurecheckexp($bcid,$feature))
					{
						return true;
					}
				else return false;
			}
		else return false;
	}
function dopurchase($packageid, $num, $bcid)
	{
		$bc = getclientdetails($bcid);
		$wallet = getwallet($bcid);
		$packres = mysql_query("SELECT * from bc_packages where packageid = '$packageid'");
		$pack = mysql_fetch_array($packres);
		$cost = $pack['packagecost'] * $num;
		if ($cost > $wallet['loadedcredits'])
			{
				$n = "insufficient";
			}
		else {
			$lc = $wallet['loadedcredits'] - $cost;
			$tot = $pack['qty'] * $num;
			switch ($pack['packagetype']) {
				case 'credits': mysql_query("update bc_clients set credits = credits + ".$tot." where bcid = '$bcid'");break;
				case 'mobile credits': mysql_query("update bc_clients set credits_mobile = credits_mobile + ".$tot." where bcid = '$bcid'");break;
			}
			mysql_query("update bc_wallet set loadedcredits = '$lc' where bcid = '$bcid'");
			$n = 'done';
		}
		return $n;
	}
function getwallet($bcid)
	{
		$res = mysql_query("SELECT * from bc_wallet where bcid = '$bcid'");
		$r = mysql_fetch_array($res);
		return $r;
	}
function savetable($table,$arr)
	{
		$fields = array_keys($arr);
		foreach ($arr as $r)
			{
				$updatedrows = 0;
				if ($r['changed'] == 1)
					{
						
						$ct = 0;
						$q = "Update $table set ";
						foreach($fields as $field)
							{
							   if ($field != "changed" && $ct != 0)
							   {
								if ($ct > 1) $q .= ",";
								$q .= " $field = '".$r[$field]."'";
							   }
								$ct++;
							}
						$q.= " where ".$fields[0]." = '".$r[$fields[0]]."'";
						mysql_query($q);
						$updatedrows++;
					}
			}
	}
function getdatatable($table,$id)
	{
		$r = mysql_query("SELECT * from $table");
		$m = array();
		while ($row = mysql_fetch_array($r))
			{
				$m[$row[$id]] = $row;
			}
		return $m;
	}
function buypackage($packageid)
	{
		$r = mysql_query("SELECT * from bc_packages where packageid = '$packageid'");
		$row = mysql_fetch_array($r);
		$t = '<hr><table>';
		$ct = 0;
				$t .= '<tr>';
				$t .= '<td style="margin-bottom:50px"><img src="../images/'.$row['packagetype'].'.jpg" /></td>';
				$t .= '<td><b>'.$row['packagename'].'</b><br>'.$row['qty'].' '.ucfirst($row['type']).'<br>';
				$t .= 'Cost: '.$row['packagecost'].' x Qty: <input type="text" id="packnum" value="1" onchange="computetcc(\''.$row['packagecost'].'\')"> = <span id="totalcreditcost">'.$row['packagecost'].'</span>';
				$t .= '<br><a href="#" onclick="dopurchase(\''.$packageid.'\')">Confirm</a> <br><br></td>';
				if ($ct % 2 == 0 && $ct != 0) $t .= '</tr>';
				
				$ct++;
		$t .= '</table><br>';
		return $t;
	}
function getpackages($type)
	{
		$r = mysql_query("SELECT * from bc_packages where packagetype = '$type' and active = 1");
		if ($type == 'all') $r = mysql_query("SELECT * from bc_packages");
		$t = '<hr><table>';
		$ct = 0;
		while ($row = mysql_fetch_array($r))
			{
				if ($ct % 2 == 0 || $ct == 0) $t .= '<tr>';
				$t .= '<td style="margin-bottom:50px"><img src="../images/'.$row['packagetype'].'.jpg" /></td>';
				$t .= '<td><b>'.$row['packagename'].'</b><p>'.$row['packagedescription'].'</p>'.$row['qty'].' '.ucfirst($row['type']).'<br>';
				$t .= 'Cost: '.$row['packagecost'].'<br><a href="#" onclick="buypackage(\''.$row['packageid'].'\')">Buy</a><br><br></td>';
				if ($ct % 2 == 0 && $ct != 0) $t .= '</tr>';
				$ct++;
			}
		$t .= '</table><br>';
		return $t;
	}
	
function bcfeatures($bcid)
	{
		
	}
function getbclist()
	{
		$r = mysql_query("SELECT * from bc_clients");
		$m = array();
		while ($row = mysql_fetch_array($r))
			{
				$m[$row['bcid']] = $row;
			}
		return $m;
	}
function getclientdetails($bcid)
	{
		$r = mysql_query("SELECT * from bc_clients where bcid = '$bcid'");
		$row = mysql_fetch_array($r);
		return $row;
	}
function getbcid()
	{
		$r = mysql_query("SELECT bcid from adminsessions where sessionid = '".$_REQUEST['PHPSESSID']."'");
		$row = mysql_fetch_row($r);
		return $row[0];
	}
function getmobileusage($bcid, $st, $en)
	{
		$ures = mysql_query("SELECT projectid from projects where bcid = '$bcid'");
		$uct = 0;
		while ($urow = mysql_fetch_array($ures))
			{
				if ($uct > 0) $ulist .= ",";
				$ulist .= "'".$urow['projectid']."'";
				$uct++;
			}
		$mobres = mysql_query("SELECT *,substr(FROM_unixtime(startepoch),1,10) as ddate from finalhistory where phone like '8804%' and projectid in ($ulist) and substr(FROM_unixtime(startepoch),1,10) >= '$st' and substr(FROM_unixtime(startepoch),1,10) <= '$en'");
		while ($row = mysql_fetch_array($mobres))
			{
				$dur = $row['endepoch'] - $row['startepoch'];
				$mobiles['total'] = $mobiles['total'] + $dur;
				$mobiles['daytotal'][$row['ddate']] = $mobiles['daytotal'][$row['ddate']] + $dur;
			}
		return $mobiles;
	}
function getbcusage($bcid, $st, $en)
	{
		//select users
		$ures = mysql_query("SELECT userid from members where bcid = '$bcid'");
		$uct = 0;
		while ($urow = mysql_fetch_array($ures))
			{
				if ($uct > 0) $ulist .= ",";
				$ulist .= "'".$urow['userid']."'";
				$uct++;
			}
			
		//get actionlog of users on dates
		$usage = array();
		$totalduration = 0;
		$acres = mysql_query("SELECT * from bc_logs where bcid = '$bcid' and date >= '$st' and date <= '$en' order by tlogid ASC");
		$c = 0;
		while ($row = mysql_fetch_array($acres))
			{
				$ac[$row['tlogid']] = $row;
				$c++;
			}
		$z = 0;
		foreach ($ac as $row)
			{
				$z++;
				if ($hanguser[$row['userid']] == 1)
					{
						$hanguser[$row['userid']] = 0;
						$tlogd = $hang[$row['userid']];
						$ndure = $row['login'] - $ac[$tlogd]['login'];
						$totalduration = $totalduration + $ndure;
						$duration[$row['userid']] = $duration[$row['userid']] + $ndure;
						$ddate = $ac[$tlogd]['date'];
						$dayduration[$ddate]['duration'] = $dayduration[$ddate]['duration'] + $ndure;
						$dayduration[$ddate]['date'] = $ddate;
						$detailed[$tlogd]['logout'] = $row['login'];

					}
				if ($row['login'] < $row['logout'])
				{
					$dur = $row['logout'] - $row['login'];
					$totalduration = $totalduration + $dur;
					$duration[$row['userid']] = $duration[$row['userid']] + $dur;
					$dayduration[$row['date']]['duration'] = $dayduration[$row['date']]['duration'] + $dur;
				
					$dayduration[$row['date']]['date'] = $row['date'];
					
				}
				else {
					$dur = 0;
					$hang[$row['userid']] = $row['tlogid'];
					$hanguser[$row['userid']] = 1;
				}
				$detailed[$row['tlogid']] = $row;
				
				
			}
		//compute for duration
		//return results
		$results['detailed'] = $detailed;
		$results['usagesecs'] = $totalduration;
		$h = $totalduration / 3600;
		$results['usagehours'] = number_format($h,2);
		$results['usageusers'] = $duration;
		$results['usagedays'] = $dayduration;
		return $results;
		
	}
function getmobilecost($bcid, $dura)
	{
		$rate = getrates($bcid);
		$rpm = $rate['mobrpm'];
		$rem = $dura % 60;
		$minutes = (($dura - $rem) / 60) + 1;
		$cost = $minutes * $rpm;
		$cost = number_format($cost,2);
		return $cost;
		
	}
function getusagecost($bcid, $dura)
	{
		$rate = getrates($bcid);
		$rph = $rate['rph'];
		$rem = $dura % 3600;
		$hours = (($dura - $rem) / 3600) + 1;
		$cost = $hours * $rph;
		$cost = number_format($cost,2);
		return $cost;
	}
function getrateslist()
	{
		$rres = mysql_query("SELECT * from bc_rates ");
	}
function getrates($bcid)
	{
		$bc = getbcdetails($bcid);
		$rres = mysql_query("SELECT * from bc_rates where rateid = '".$bc['rateid']."'");
		$rate = mysql_fetch_array($rres);
		return $rate;
	}
function getagentnames()
	{
		$res = mysql_query("SELECT userid, afirst, alast from memberdetails");
		while ($row = mysql_fetch_array($res))
			{
				$results[$row['userid']] = $row['afirst']." ".$row['alast'];
			}
		return $results;
	}
function getbcdetails($bcid)
	{
		$res = mysql_query("SELECT * from bc_clients where bcid = '$bcid'");
		$row = mysql_fetch_array($res);
		return $row;
	}

function dayadd($date)
	{
		$d = strtotime($date);
		$d = $d + 86400;
		return date("Y-m-d",$d);
	}
function getprojectlist()
	{
		$pres = mysql_query("SELECT * from projects");
		while ($row = mysql_fetch_array($pres))
			{
				$projectlist[$row['projectid']] = $row;
			}
		return $projectlist;
	}
function getdispooptions($pid)
	{
		$dres = mysql_query("SELECT statusname from statuses where projectid = '0' or projectid = '$pid' ");
		while ($drow = mysql_fetch_array($dres))
			{
				$options .= '<option value="'.$drow['statusname'].'">'.$drow['statusname'].'</option>';
			}
		return $options;
	}
function graph3d_single($xarr,$yarr,$file,$xtitle, $ytitle, $maintitle,$shownames = 1)
	{
		$serv = substr($_SERVER['HTTP_HOST'],6,3);
		$file = $serv.$file;
		$xml = fopen($file,"w");
		
		$data = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
		<graph caption="'.$maintitle.'" xAxisName="'.$xtitle.'" yAxisName="'.$ytitle.'"  shownames="'.$shownames.'" decimalPrecision="0" formatNumberScale="0">';
		$c = 0;
		foreach($xarr as $x)
			{
				$data.= '<set name="'.$x.'" value="'.$yarr[$c].'" color="'.rand_colorCode().'" />';
				$c++;
			}
		$data.= "</graph>";
		fwrite($xml,$data);
		fclose($xml);
	}
function rand_colorCode(){
$r = dechex(mt_rand(0,255)); // generate the red component
$g = dechex(mt_rand(0,255)); // generate the green component
$b = dechex(mt_rand(0,255)); // generate the blue component
$rgb = $r.$g.$b;
if($r == $g && $g == $b){
$rgb = substr($rgb,0,3); // shorter version
}
return $rgb;
}
function flashgraph($type,$xmlfile,$width,$height)
	{
		$serv = substr($_SERVER['HTTP_HOST'],6,3);
		$xmlfile = $serv.$xmlfile;
		$disp = '<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="'.$width.'" height="'.$height.'" id="'.$type.'" >
         <param name="movie" value="graphgen/fcf/charts/FCF_Column3D.swf" />
         <param name="FlashVars" value="&dataURL=='.$xmlfile.'&chartWidth='.$width.'&chartHeight='.$height.'">
         <param name="quality" value="high" />
         <embed src="../graphgen/fcf/Charts/FCF_'.$type.'.swf" flashVars="&dataURL='.$xmlfile.'&chartWidth='.$width.'&chartHeight='.$height.'" quality="high" width="'.$width.'" height="'.$height.'" name="'.$type.'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
      </object>';
	  	return $disp;
	}
function getlists($projectid,$bcid)
	{
		$projects = getprojects($bcid);
		$q = "SELECT listid from lists where projects ";
		if ($projectid == 'all')
			{
				$q.= " in (".$projects['sql'].") ";
			}
		else {
				$q.= " = '$projectid' ";
		}
		$listres = mysql_query($q);
		$ct = 0;
		while ($list = mysql_fetch_assoc($listres))
			{
				$lists[] = $list;
				if ($ct > 0) $listsql .= ",";
				$listsql .= "'".$list['listid']."'";
				$listoptions .= '<option value="'.$list['listid'].'">'.$list['listid'].'</option>';
				$ct++;
			}
		$ret['sql'] = $listsql;
		$ret['arr'] = $lists;
		$ret['options'] = $listoptions;
		return $ret;
	}
?>