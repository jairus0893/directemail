<?php
/**
 * Original by Abaam germones
 * Heavily modified by obrifs@gmail.com 
 * mods: converted into a class instead
 * renamed functions to follow convention so it can be called using dynamic requests (i.e. using variables to call method)
*/
class um {
static function func()
{
    $n = new um();
    return $n;
}
function activate()
{
    $uid = $_REQUEST['userid'];
    mysql_query("update members set active = 1 where userid = '$uid'");
}
function deactivate()
{
    $uid = $_REQUEST['userid'];
    mysql_query("update members set active = 0 where userid = '$uid'");

}
function bulkaction(){
    $action = $_REQUEST['action'];
    $idstring = $_REQUEST['ids'];
    $ids = explode(",",$idstring);
    if ($action == 'setteam')
    {
        $teamid = $_REQUEST['teamid'];
        foreach ($ids as $id)
        {  
            if (checkagentrole($id) == 1)
            {
            addusertoteam($id, $teamid);
            }
        }
    }
    if ($action == 'setrole')
    {
  
        $roleid = $_REQUEST['roleid'];
        foreach ($ids as $id)
        {
            $level = getlevel($id);
            if (checklevel($level))
            {
                
                setuserrole($id, $roleid);
            }
        }
    }
    if ($action == 'deactivate')
    {
        foreach ($ids as $id)
        {
            $level = getlevel($id);
            if (checklevel($level))
            {
                deactivateuser($id);
            }
        }
    }
    if ($action == 'activate')
    {
        foreach ($ids as $id)
        {
            $level = getlevel($id);
            if (checklevel($level))
            {
                activateuser($id);
            }
        }
    }
    echo "Bulk Action Completed";
}
function bulkform(){
    global $bcid;
    $action = $_REQUEST['action'];
    include "forms/bulk$action.php";
    exit;
}
function umupdate(){
    foreach ($_POST as $fld=>$val)
    {
        mysql_query("update members set $fld = '$val' where userid = '".$_REQUEST['userid']."'");
        $afct = mysql_affected_rows();
        if ($afct < 1)
        {
        mysql_query("update memberdetails set $fld = '$val' where userid = '".$_REQUEST['userid']."'");
        $afct = mysql_affected_rows();
        }
        if ($afct < 1 && $fld != 'userpass')
        {
            $res = mysql_query("SELECT * from memberdetails where userid = '".$_REQUEST['userid']."'");
            $ct = mysql_num_rows($res);
            if ($ct < 1)
            {
                mysql_query("INSERT into memberdetails set $fld = '$val', userid = '".$_REQUEST['userid']."'");
            }
        }
    }
    exit;
}
function getroles(){
        global $bcid;
	$query = "SELECT * FROM roles where bcid in (0,$bcid) order by bcid,level ASC";
	$result = mysql_query($query);

	//echeck if query is ok or not
	if($result != false){
		$data = array();
		while ($row = mysql_fetch_array($result)){
			$data[]= $row;
		}
	}
	return $data;
}
function getusers(){
        global $bcid;
        $teams = getdatatable('teams','teamid');
	$query = "SELECT roles.agent_portal, roles.rolename as role_name, members.active, members.userid,members.userlogin, members.roleid, members.usertype, memberdetails.afirst, memberdetails.alast, memberdetails.team, members.lastlogin
		      from members 
		      left join memberdetails on members.userid = memberdetails.userid 
		      left join agentlog on members.userid = agentlog.userid
		      Left join roles on roles.roleid = members.roleid
                      where members.bcid = '$bcid' and usertype = 'user' and isdeleted = 0
		      group by members.userid order by alast";
	$result = mysql_query($query);

	//echeck if query is ok or not
	if($result != false){
		$data = array();
		while ($row = mysql_fetch_assoc($result)){
                        $teamsr = json_decode($row['teams'],true);
                        $teamslist = array();
                        foreach($teamsr as $tr)
                        {
                            $teamslist[] = $teams[$tr];
                        }
                        $row['teams'] = implode(", ",$teamslist);
			$data[]= $row;
		}
	}
        else return $query;
	return $data;
}
function getagent(){
    $agentid = $_REQUEST['userid'];
	$agentres = mysql_query("SELECT members.*,memberdetails.* from members left join memberdetails on members.userid = memberdetails.userid where members.userid ='$agentid';");
	
$row = mysql_fetch_array($agentres);
		$first = $row['afirst'];
		$last = $row['alast'];
		$first_fild = 'afirst';
		$last_fild = 'alast';
		$company_fild = 'company';
		$company = $row['company'];
		$type = 'memberdetails';
		$title = 'Agent Details';
		$uid = $agentid;
                
                include "agent.php";
 }
function getteams(){
        global $bcid;
	$query = "SELECT * FROM teams where bcid = '$bcid'";
	$result = mysql_query($query);

	//echeck if query is ok or not
	if($result != false){
		$data = array();
		while ($row = mysql_fetch_array($result)){
			$data[]= $row;
		}
	}

	return $data;
}
function updateuserrole()
        {
            $u = $_REQUEST['userid'];
            $r = $_REQUEST['roleid'];
            mysql_query("update members set roleid = '$r' where userid = '$u'");
        }
function deleterole()
        {
            global $bcid;
            $roleid = $_REQUEST['roleid'];
            mysql_query("DELETE from roles where roleid = '$roleid' and bcid = '$bcid'");
            mysql_query("update members set roleid = 4 where roleid = '$roleid' and bcid = '$bcid'");
            exit;
        }
function addrole()
        {
            global $bcid;
            foreach ($_POST as $key=>$val)
            {
                $qs[] = "$key = '$val'";
            }
            $qstring = implode(", ",$qs);
            mysql_query("insert into roles set bcid = '$bcid', $qstring");
        }
function updaterole()
        {   
            global $bcid;
            $roleid = $_REQUEST['roleid'];
            $res = mysql_query("SELECT * from roles where roleid = '$roleid'");
            $row = mysql_fetch_assoc($res);
            foreach ($row as $key=>$val)
            {
                if ($key != 'roleid' && $key != 'roledescription' && $key != 'bcid')
                {
                    if ($_POST[$key]) $qs[] = "$key = '".$_POST[$key]."'";
                    else $qs[] = "$key = '0'";
                }
            }
            $qstring = implode(", ",$qs);
            mysql_query("update roles set $qstring where roleid = '$roleid' and bcid = '$bcid'");
        }
function editrole()
        {
                $roleid = $_REQUEST['roleid'];
                global $bcid;
                global $sub;
                $roleres = mysql_query("SELECT * from roles where roleid = '$roleid' and bcid = '$bcid'");
                $rol = mysql_fetch_assoc($roleres);
                foreach ($rol as $k=>$v)
                {
                    if ($v == 0) 
                    {
                        $nosel[$k] = 'selected="selected"';
                        $yesel[$k] = "";
                    }
                    if ($v == 1) 
                    {
                        $yesel[$k] = 'selected="selected"';
                        $nosel[$k] = "";
                    }
                }
             include "newrole.php";
}
function newrole()
        {
            include "newrole.php";
        }
public function doupdateteam()
{
    global $bcid;
    $teamid = $_REQUEST['teamid'];
    $val= $_REQUEST['val'];
    mysql_query("UPDATE teams set teamname = '$val' where teamid = $teamid and bcid = '$bcid'");
    exit;
}
public function updateteam()
{
    global $bcid;
    $teamid = $_REQUEST['teamid'];
    $res = mysql_query("SELECT * from teams where teamid = $teamid and bcid = $bcid");
    $team = mysql_fetch_assoc($res);
    $teamname = $team['teamname'];
    include "forms/updateteam.php";
    exit;
}
public function newteam()
{
    global $bcid;
    include "forms/newteam.php";
    exit;
}
public function docreateteam()
{
    global $bcid;
    $val= $_REQUEST['val'];
    mysql_query("insert into teams set teamname = '$val', bcid = '$bcid'");
    exit;
}
public function teammembers()
{
    global $bcid;
    $teamid = $_REQUEST['teamid'];
    $res = mysql_query("SELECT * from teams where teamid = $teamid and bcid = $bcid");
    $team = mysql_fetch_assoc($res);
    $members = members::getmembersbyteamid($teamid);
    foreach ($members as $member)
    {
        $rows[$member['userid']][1] = $member['alast'].", ".$member['afirst'] . " - (" . $member['userlogin'] . ")" ;
       $rows[$member['userid']][2] = '<img width="12" height="12" onclick="um_teamremuser(\''.$member['userid'].'\',\''.$teamid.'\')" src="icons/delete.gif" style="cursor:pointer" title="remove">';       
    }
    $headers[] = 'Agent';
    $headers[] = ' ';
    $allmembers = members::getalldetails();
    $rolesres = mysql_query("SELECT * from roles");
    while ($row = mysql_fetch_assoc($rolesres))
    {
        $roles[$row['roleid']] = $row;
    }
    $userselect = '<select id="teamuserform" onchange="um_teamadduser(\''.$teamid.'\')"><option></option>';
    foreach ($allmembers as $userid=>$user)
    {
        if (!$members[$userid] && $roles[$user['roleid']]['agent_portal'] == 1)
        {
            $userselect .= '<option value="'.$userid.'">'.$user['alast'].', '.$user['afirst']. ' - (' . $user['userlogin'] . ')' .'</option>';
        }
    }
    $userselect .= '</select>';
    echo "<div>Members of team <b>".$team['teamname']."</b>";
    echo tablegen($headers,$rows,"100%"); 
    echo "<br>";
    echo 'Add to Team:'; 
    echo $userselect;
    echo '</div>';
    exit;
}
public function teamcampaigns()
{
    global $bcid;
    $teamid = $_REQUEST['teamid'];
    $res = mysql_query("SELECT * from teams where teamid = $teamid and bcid = $bcid");
    $team = mysql_fetch_assoc($res);
    $projectname = projects::projectnames($bcid);
    $projects = explode(";",$team['projects']);
    foreach ($projects as $project)
    {
        if ($project > 0)
        {
        $rows[$project][1] = $projectname[$project];
        $rows[$project][2] = '<img width="12" height="12" onclick="um_removeprojectfromteam(\''.$project.'\',\''.$teamid.'\')" src="icons/delete.gif" style="cursor:pointer" title="remove">';
        $plist[] = $project;
        }
    }
       if (count($plist) > 0)
       {
       $peres = mysql_query("SELECT * from projects where bcid = '$bcid' and projectid not in (".implode(",",$plist).") AND active=1 ORDER BY projectname");
       }
       else {
           $peres = mysql_query("SELECT * from projects where bcid = '$bcid' AND active=1 ORDER BY projectname");
           
       }
	$campselect = "<select onchange=\"um_addprojecttoteam('$teamid')\" id=\"teamprojectfrom\"><option></option>";
	while ($trow = mysql_fetch_assoc($peres))
		{
		$campselect .='<option value="'.$trow['projectid'].'">';
		$campselect .= $trow['projectname'];
		$campselect .= "</option>";
		}
	$campselect .= "</select>";
    
    $headers[] = 'Campaign';
    $headers[] = ' ';
    echo "Campaigns for <b>".$team['teamname']."</b>";
    echo tablegen($headers,$rows,"100%"); 
    echo "<br>";
    echo 'Add to Project:'; 
    echo $campselect;
    
    exit;
}
public function showteams()
{
    global $bcid;
   
    $u = $_REQUEST['userid'];
     
	$tres = mysql_query("SELECT * from memberdetails where userid = '$u'");
        $tea = mysql_query("SELECT * from teams where bcid = '$bcid'");
        $validteams = array();
        while ($t = mysql_fetch_assoc($tea))
        {
            $validteams[] = $t['teamid'];
            $teamnames[$t['teamid']] = $t['teamname'];
        }
	$row = mysql_fetch_array($tres);
	$uteams = json_decode($row['team'],true);
	foreach ($uteams as $uteam)
		{
                        if (in_array($uteam,$validteams)) $ateam[$uteam] = $uteam;
		}
        $ct = 0;
	foreach ($ateam as $team)
		{
			$rows[$ct][1]= $teamnames[$team];
			$rows[$ct][2]= '<img width="12" height="12" onclick="um_remuser(\''.$u.'\',\''.$team.'\')" src="icons/delete.gif" style="cursor:pointer" title="remove">';
			$ct++;
                        
		}
                if (count($ateam) > 0)
                {
                $teres = mysql_query("SELECT * from teams where bcid = '$bcid' and teamid not in (".implode(",",$ateam).")");
                }
                else $teres = mysql_query("SELECT * from teams where bcid = '$bcid'");
	$teamselect = "<select onchange=\"um_addtoteam('$u')\" id=\"teamuserform\"><option></option>";
	while ($trow = mysql_fetch_array($teres))
		{
		$teamselect .='<option value="'.$trow['teamid'].'">';
		$teamselect .= $trow['teamname'];
		$teamselect .= "</option>";
		}
	$teamselect .= "</select>";
    
    $headers[] = 'Team';
    $headers[] = ' ';
    echo "Teams for ".$row['afirst']." ".$row['alast'];
    echo tablegen($headers,$rows,"100%"); 
    echo "<br>";
    echo 'Select Team to Add:'; 
    echo $teamselect;
    exit;
    
}
}
?>