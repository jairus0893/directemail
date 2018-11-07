<?php 
        $sub = $_REQUEST['sub'];
        if ($sub == 'actionUpdateMembers') $sub = 'actionUpdateMemberdetails';
        $bluestrap_um->addTransaction('memberdetails');
        $bluestrap_um->doAction($sub);
        
	include_once 'users_model.php';
        $umbulk['setteam']='Set Team';
        $umbulk['setrole']='Set Role';
        $umbulk['deactivate']='Deactivate';
        $umbulk['activate']='Activate';
        foreach ($umbulk as $u=>$b)
        {
            $bdrop .= '<option value="'.$u.'">'.$b.'</option>';
        }
        if ($sub == 'roles' || $sub == 'users' || $sub == 'teams')
        {
            $subf= 'get'.$sub;
        $alist = um::func()->$subf();
        include $sub.'.php';
        exit;
        }
        elseif (strpos($sub, "role"))
        {
            if (checkrights('manage_roles')) um::func()->$sub();
            exit;
        }
        elseif ($sub)
        {
            um::func()->$sub();
            exit;
        }
        else {
            $alist = um::func()->getusers();
        }
//$agentres = mysql_query("SELECT members.userid, members.usertype, memberdetails.afirst, memberdetails.alast, memberdetails.team, max(agentlog.date) as 'lastlogin' from members left join memberdetails on members.userid = memberdetails.userid left join agentlog on members.userid = agentlog.userid where members.bcid = '$bcid' and members.usertype = 'user' and active = $isactiv group by members.userid order by alast");
?>
<style>

</style>
<div class="adminMenuLeft"><div class="apptitle">Manage Users</div>
    <div class="secnav">
	<ul>
		
		<li id="liUsers" class="activeMenu">
			<a href="#" class="manageCampMenu">Users</a>

		</li>
                <?php 
                if (checkrights('manage_roles'))
                {
                 ?>
                <li id="liRole" onclick="">
			<a href="#" class="manageCampMenu">Roles</a>
		</li>
                <?
                }
                ?>
		<li id="liTeams" >
			<a href="#" class="manageCampMenu">Teams</a>
		</li>
	</ul>
    </div>
</div>
<div id="userRightSide" style="float: left;width: 82%;">
	
</div>

<script>
	
	$("#userList").dataTable();
	$("#userTeams").dataTable();

	$("#liUsers").click(function(){	
		$(".activeMenu").removeClass('activeMenu');
		$(this).addClass('activeMenu');
                umaction('users');
	});

	$("#liRole").click(function(){
		$(".activeMenu").removeClass('activeMenu');
		$(this).addClass('activeMenu');
                umaction('roles');
	});

	$("#liTeams").click(function(){
		$(".activeMenu").removeClass('activeMenu');
		$(this).addClass('activeMenu');
                umaction('teams');
	});

function umaction(action)
{
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub='+action,
        type:'GET',
        success: function(resp)
        {
            $("#userRightSide").html(resp);
            var utable = $(".datatabs").dataTable({
                'iDisplayLength':20,
                'aaSorting':[],
                "aLengthMenu": [[10, 20, 50, -1], [10, 20, 50, "All"]]
            });
            $(".jbut").button();
            if (action == 'users')
                {
                    $(".dataTables_filter input").hide();
                    $(".dataTables_filter").append("<select id=\"selectUserStatus\"><option value=\"Active\">Active</option><option value=\"Inactive\">Inactive</option><option value=\"\">All</option></select>");
                     $(".dataTables_length").html(' ');
                    $(".dataTables_length").prepend('<select id="umbulk" onchange="umbulk()"><option></option><?php echo $bdrop;?></select>');
                    
                    $(".dataTables_filter").append("&nbsp;&nbsp;&nbsp;Role:<select id=\"selectUserRole\"><option value=\"\">All</option><?=addslashes(getroledrop());?></select>");
                    utable.fnFilter( 'Active', 5, false, false, false, false );
                    utable.fnSort( [ [1,'asc'] ] );
                    $("#selectUserRole").change(function(){
                           var selectVal = $("#selectUserRole>option:selected").html();
                           utable.fnFilter( selectVal, 4, false, false, false, false );
                    });
                    $("#selectUserStatus").change(function(){
                            var selectVal = $(this).val();

                        utable.fnFilter( selectVal, 5, false, false, false, false );
                    });
                }
        }
    })
}
function um_edituser(userid)
{
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=getagent&userid='+userid,
        type:'GET',
        success: function(resp)
        {
            $("#userRightSide").html(resp);
            $(".datatabs").dataTable();
            $(".jbut").button();
        }
    })
}
function umbulk()
{
     var action = $("#umbulk").val();
     if (action != 'deactivate' && action != 'activate')
     {
     $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=bulkform&action='+action,
        success: function(resp){
            dresponsehandler(resp);
        }
    });
     }
     else bulkaction();
}
function bulkaction()
{
     $("#dialogcontainer").dialog("close");
    var ids = '';
    var action = $("#umbulk").val();
    if (action == 'setteam')
        {
            var teamid = $("#teamid").val();
            var params = 'teamid='+teamid;
        }
    if (action == 'setrole')
        {
            var roleid = $("#roleid").val();
            var params = 'roleid='+roleid;
        }
    if (action == 'deactivate')
        {
            var params = '';
        }
    if (action == 'activate')
        {
            var params = '';
        }
    var ct = 0;
    $(".userids").each(function(){
    
    if ($(this).prop('checked'))
    {
        if (ct > 0) ids+= ","; 
        ids+= $(this).val();
        ct++;
    }
    
    });
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=bulkaction&action='+action+'&ids='+ids+"&"+params,
        success: function(resp){
            alert(resp);
            umaction('users');
        }
    });
}
function togglecheckall()
{
    if ($("#checkall").prop('checked')) $(".userids").prop('checked',true);
    else $(".userids").prop('checked',false);
}
function um_update(userid,fld)
{
    var val =$("#um"+fld).val();
    var dat = fld+'='+val;
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=umupdate&userid='+userid,
        type:'POST',
        data: dat,
        success: function(resp)
        {
            
        }
    })
}
function um_updateteam(teamid)
{
    
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=updateteam&teamid='+teamid,
        type:'POST',
        success: function(resp)
        {
             $("#dialogcontainer").dialog("close");
            $("#dialogcontainer").html(resp);
            $(".jbut").button();
            $("#dialogcontainer").dialog({
                title: ''
            });
        }
    })
}
function um_newteam()
{
    
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=newteam',
        type:'POST',
        success: function(resp)
        {
             $("#dialogcontainer").dialog("close");
            $("#dialogcontainer").html(resp);
            $(".jbut").button();
            $("#dialogcontainer").dialog({
                title: '',
                height:150
            });
        }
    })
}
function um_docreateteam(teamid)
{
    var val = $("#updateteamname").val();
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=docreateteam&val='+val,
        type:'POST',
        success: function(resp)
        {
            $("#dialogcontainer").dialog("close");
             umaction('teams');
        }
    })
}
function um_deleteteam(teamid)
{
    $.ajax({
        url:'admin.php?act=removeteam&tid='+teamid,
        type:'POST',
        success: function(resp)
        {
             umaction('teams');
        }
    })
}
function um_doupdateteam(teamid)
{
    var val = $("#updateteamname").val();
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=doupdateteam&teamid='+teamid+'&val='+val,
        type:'POST',
        success: function(resp)
        {
            $("#dialogcontainer").dialog("close");
             umaction('teams');
        }
    })
}
function um_teammembers(teamid)
{
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=teammembers&teamid='+teamid,
        success: function(resp){
            $("#dialogcontainer").dialog("close");
            $("#dialogcontainer").html(resp);
            $("#dialogcontainer").dialog({
                title: ''
            });
        }
    });
}
function um_teammembers_cb(teamid)
{
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=teammembers&teamid='+teamid,
        success: function(resp){
            $("#dialogcontainer").html(resp);
        }
    });
}
function um_teamremuser(iid,tid)
	{
	var ddata = 'act=remuser&team='+tid+'&user='+iid;
	$.ajax({
            url: 'admin.php',
            type: 'POST',
            data: ddata,
            success: function(){
                um_teammembers_cb(tid)
            }
        })
	}
 function um_teamadduser(tid)
	{
	var iid = $("#teamuserform").val();
        var ddata = 'act=updateteamuser&team='+tid+'&user='+iid;
	$.ajax({
            url: 'admin.php',
            type: 'POST',
            data: ddata,
            success: function(){
                um_teammembers_cb(tid)
            }
        })
	}
function um_teamcampaigns(teamid)
{
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=teamcampaigns&teamid='+teamid,
        success: function(resp){
            $("#dialogcontainer").dialog("close");
            $("#dialogcontainer").html(resp);
            $("#dialogcontainer").dialog({
                title: ''
            });
        }
    });
}
function um_teamcampaigns_cb(teamid)
{
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=teamcampaigns&teamid='+teamid,
        success: function(resp){
            $("#dialogcontainer").html(resp);
        }
    });
}
function um_removeprojectfromteam(proj,tid)
{
    $.ajax({
            url: "admin.php"+"?act=removeprojectfromteam&teamid="+tid+"&project="+proj,
            success: function(){
                um_teamcampaigns_cb(tid)
            }
        });
}
function um_addprojecttoteam(tid)
{
   var proj = $("#teamprojectfrom").val();
        $.ajax({
            url:"admin.php"+"?act=addprojecttoteam&teamid="+tid+"&project="+proj,
            success:function(){
                um_teamcampaigns_cb(tid)
                
            }
        });
}
function um_showteams(userid)
{
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=showteams&userid='+userid,
        success: function(resp){
            $("#dialogcontainer").dialog("close");
            $("#dialogcontainer").html(resp);
            $("#dialogcontainer").dialog({
                title: ''
            });
        }
    });
}
function um_showhistory(userid)
{
    $.ajax({
        url:'admin.php?act=getcallhistory&agentid='+userid,
        success: function(resp){
            $("#dialogcontainer").dialog("destroy");
            $("#dialogcontainer").html(resp);
            $("#dialogcontainer").dialog({
                title: '',
                minWidth: 800,
                maxHeight:500
            });
            var edate = $(".tolocaldate");
                edate.each(function(){
                    var v = $(this).html();
                    var d = epochtoutc(v);
                    var dt = d.toLocaleString();
                    $(this).html(dt);

                });
        }
    });
}
function um_showteams_cb(userid)
{
     $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=showteams&userid='+userid,
        success: function(resp){
            $("#dialogcontainer").html(resp);     
        }
    });
}
function um_remuser(iid,tid)
	{
	var ddata = 'act=remuser&team='+tid+'&user='+iid;
	$.ajax({
            url: 'admin.php',
            type: 'POST',
            data: ddata,
            success: function(){
                um_showteams_cb(iid)
            }
        })
	}
 function um_addtoteam(iid)
	{
	var tid = $("#teamuserform").val();
        var ddata = 'act=updateteamuser&team='+tid+'&user='+iid;
	$.ajax({
            url: 'admin.php',
            type: 'POST',
            data: ddata,
            success: function(){
                um_showteams_cb(iid)
            }
        })
	}
function um_activate(userid)
{

    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=activate&userid='+userid,
        success: function(resp)
        {
            umaction('users');
        }
    })
}
function um_deactivate(userid)
{
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=deactivate&userid='+userid,
        success: function(resp)
        {
            umaction('users');
        }
    })
}
function createnewrole()
{
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=newrole',
        type:'GET',
        success: function(resp){
            dresponsehandler(resp);
            rolecall();
        }
    })
}
function editrole(roleid)
{
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=editrole&roleid='+roleid,
        type:'GET',
        success: dresponsehandler
    })
}
function deleterole(roleid)
{
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=deleterole&roleid='+roleid,
        type:'GET',
         success: function(){
            $("#dialogcontainer").dialog("close");
            umaction('roles');
        }
    })
}
function addnewrole()
{
    var cr = $("#newrole").serialize();
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=addrole',
        type:'POST',
        data: cr,
        success: function(){
            $("#dialogcontainer").dialog("close");
            umaction('roles');
        }
    })
}
function updaterole(roleid)
{
    var cr = $("#newrole").serialize();
    $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=updaterole&roleid='+roleid,
        type:'POST',
        data: cr,
        success: function(){
            $("#dialogcontainer").dialog("close");
            umaction('roles');
        }
    })
}
function rolecall()
{
    var ap = $("#admin_portal").val();
    if (ap != 1)
        {
            $('.rc option[value=1]').prop('selected', false);
            $('.rc option[value=0]').prop('selected', true);
            
            $(".rc").prop("disabled",true);
            
        }
    else {
        $(".rc").prop("disabled",false);
    }
}
function updateuserrole(userid)
{
   var rid = $("#roleid").val();
   $.ajax({
        url:'admin.php?act=getapp&app=managents&sub=updateuserrole&userid='+userid+'&roleid='+rid,
        type:'GET',
        success: function(){
            //um_edituser(userid)
        }
    })
}
umaction('users');

</script>