<?php
$ch = '<input type="button" onclick="getcallhistory(\''.$uid.'\')" value="View last 100 Calls"/>';
$ch .= '<input type="button" onclick="getapp(\'managents\')" value="Back"/>';
$activetoggle = $row['active'] == 1 ? '<input type="button" onclick="um_deactivate(\''.$row['userid'].'\')" value="Deactivate"/>' : '<input type="button" onclick="um_activate(\''.$row['userid'].'\')" value="Activate" />';
?>
<div class="apptitle">Agent Profile</div>
                <div class="secnav" id="agentprof">
                    <input type="button" onclick="deleteuser('<?=$agentid;?>')" value="Delete User" /><?=$activetoggle;?><?=$ch;?>
                </div>
<h3>Agent Details</h3>
<table width="100%" style="background-color:#FFFFFF;" id="agentproftable">
    <thead>
<tr><td class="tableheader" style="padding-left:3px">Option</td><td class="tableheader">Value</td></tr></thead>
    <tbody>
<tr class="tableitem_"><td style="padding-left:3px">FirstName</td><td class="dataleft"><input type="text" id="umafirst" value="<?=$first;?>" onblur="um_update('<?=$uid;?>','afirst')" /></td></tr>
<tr class="tableitem"><td style="padding-left:3px">Lastname</td><td class="dataleft"><input type="text" id="umalast" value="<?=$last;?>" onblur="um_update('<?=$uid;?>','alast')" /></td></tr>
<tr class="tableitem_"><td style="padding:5px;padding-left:3px">Username</td><td style="padding:5px;"><?=$row['userlogin'];?></td></tr>
<tr class="tableitem"><td style="padding:5px;padding-left:3px">Password</td><td style="padding:5px"><?=$row['userpass'];?></td></tr>
<?php if (checkrights('manage_roles'))
{
    $rd = getroledrop($row['roleid']);
    ?>
<tr class="tableitem_"><td style="padding-left:3px">Role</td><td class="dataleft">
        <select name="roleid" id="roleid" onchange="updateuserrole('<?=$agentid;?>')"><?=$rd;?></select>
    </td></tr>
<?}?>
<tr class="tableitem"><td style="color:#DD0000;padding-left: 3px;">Change Password</td><td class="dataleft"><input type="text" id=newpass><button onclick="passreset('<?=$agentid;?>')">Change</button></td></tr>
    </tbody>
</table><div id="agentdisplay"></div>