<?php
$lsearch = $_GET['listsearch'];

$listquery = "SELECT * from lists where bcid = '$bcid' AND is_deleted = 0";
$clients = new clients($bcid);

$listquery .= " order by `active` DESC, listid ASC";
$listres = mysql_query($listquery);
while ($listrow = mysql_fetch_array($listres))
	{
	$ind = $listrow['lid'];
	$lists[$ind] = $listrow;
	}
$projres = mysql_query("SELECT * from projects");
while ($projrow = mysql_fetch_array($projres))
	{
		$projects[$projrow['projectid']] = $projrow;
	}
$ld .= '<thead><tr>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= 'List ID';
	$ld .= '</th>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= 'Description';
	$ld .= '</th>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= 'Date Created';
	$ld .= '</th>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= 'is Wash';
	$ld .= '</th>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= '<div class="bctooltipsic">
				<span>Wash Expiration Date</span>
				<span class="bctooltiptextsic">&#128712&nbsp;Expiration date of the list.</span>
			</div>';
	$ld .= '</th>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= 'Campaign';
	$ld .= '</th>';
    $ld .= '<th class="tableheadercenter">';
	$ld .= 'Client';
	$ld .= '</th>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= 'Status';
	$ld .= '</th>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= 'is Active';
	$ld .= '</th><th class="tableheadercenter"></th><th class="tableheadercenter">act</th>';
	$ld .= '</tr></thead>';
    $ld .= '<tbody>';
	foreach($lists as $list) {
		if ($list['active'] == 1) {
			$active = 'YES';
			$status = 'Active';
			$color = ' style="color:#0000FF" ';
		}
		if ($list['active'] == 0) {
			$active = 'NO';
			$status = 'Inactive';
			$color = ' style="color:#666666" '; 
		}
		$date_now = date("Y-m-d");
		if (!empty($list["washdate"])) {
			if (date("Y-m-d", $list["washdate"]) >= $date_now) {
				$dot = "";
			} else {
				$dot = '<div class="bctooltipsic">
							<span style="color: red">&#11044;</span>
							<span class="bctooltiptextsic">&#128712&nbsp;This list expired.</span>
						</div>';
			}
		} else {
			$dot = "";
		}
	$ld .= '<tr class="li-'.$list['lid'].'">';
	$ld .= '<td class="datas" '.$color.'>'.$dot.'&nbsp;';
	$ld .= $list['listid'];
	$ld .= '</td>';
	$ld .= '<td class="datas">';
	$ld .= $list['listdescription'];
	$ld .= '</td>';
	$ld .= '<td class="datas">';
	$ld .= $list['datecreated'];
	$ld .= '</td>';
	$iswash = $list['iswash'] == 1 ? 'Yes' : 'No';
	$ld .= '<td class="datas">';
	$ld .= $iswash;
	$ld .= '</td>';
	$ld .= '<td class="datas">';
	if ($list["washdate"] == NULL) {
		$datewashdate = "";
	} else {
		$datewashdate = date("Y-m-d",$list['washdate']);
	}
	$ld .= $datewashdate;
	$ld .= '</td>';
	$ld .= '<td class="datas"><div id="projects'.$list['lid'].'">';
	$ld .= '<span title="'.$projects[$list['projects']]['projectname'].'">'.substr($projects[$list['projects']]['projectname'],0,20).'</span>';
	$ld .= '</div></td>';
	$ld .= '<td class="datas"><span title="'.$clients->getclientname($projects[$list['projects']]["clientid"]).'">'.substr($clients->getclientname($projects[$list['projects']]["clientid"]),0,20).'</span></td>';
	$ld .= '<td class="datas">'.$status.'</td>';
	$ld .= '<td class="datas"><select name="active" id="active'.$list['lid'].'" onchange=togglelist(\''.$list['lid'].'\')>';
	$nosel = $list['active'] == 0 ? "Selected":"";
	$ld .= '<option value="1">Yes</option><option value="0" '.$nosel.'>No</option>';
	$ld .= '</select></td><td class="datas"><a href="#" onclick="listhistory(\''.$list['lid'].'\')">History</a>';
	//$ld .= ' | <a href="#" onclick="dialogwindowml(\'exportcampaignlists\',\''.$list['lid'].'\')">Export</a> | <a href="#" onclick="dialogwindowml(\'editlists\',\''.$list['lid'].'\')">Edit</a> | <a href="#" onclick="setListDeleted('.$list['lid'].'); return false;">Delete</a></td>';
	$ld .= ' | <a href="#" onclick="dialogwindowml(\'exportcampaignlists\',\''.$list['lid'].'\')">Export</a> | <a href="#" onclick="confirmeditlistdialog(\''.$list['lid'].'\')">Edit</a> | <a href="#" onclick="setListDeleted('.$list['lid'].'); return false;">Delete</a></td>';
	$ld .= '<td class="datas">'.$active.'</td>';
	$led .= '</tr>';
	}
    $ld .= '</tbody>'
?>
<style>
.bctooltipsic {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black;
}

.bctooltipsic .bctooltiptextsic {
	font-size: 0.9em;
    visibility: hidden;
    width: 170px;
    background-color: #3366cc;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px 0;
    position: absolute;
    z-index: 1;
    top: 125%;
    left: 50%;
    margin-left: -60px;
    opacity: 0;
    transition: opacity 1s;
}
.bctooltipsic:hover .bctooltiptextsic {
    visibility: visible;
    opacity: 1;
}
.ecloading {
    display: none;
    opacity: 0.8;
    position: absolute;
    left: 0;
    top: 0;
    z-index: 999;
    height: 100%;
    width: 100%;
}
.ecloading img {
	position: absolute;
	top: 0; 
	bottom: 0; 
	left:0; 
	right: 0; 
	margin: auto;
}
</style>
<div id="ACampaignsLeftNavigation">
    <div class="apptitle">Manage Lists</div>
<div class="secnav"></div>
<div style="clear:both"></div>
<div><ul>
<li id="managelist" class="activeMenu" onclick="listMenu('managelist')">
<a class="manageListMenu" href="#">List Overview</a></li>
<li id="manageexclusion" onclick="listMenu('manageexclusion')"><a class="manageListMenu" href="#">Manage Exclusion Lists</a></li>
<li id="dispoupdate" onclick="listMenu('dispoupdate')"><a class="manageListMenu" href="#">Disposition Update</a></li>
<li id="managedonotcall" onclick="listMenu('managedonotcall')"><a class="manageListMenu" href="#">Wash List Update</a></li>

</div>
</div>
<div class="ecloading">    
    <img src="loading_big.gif" alt="loading" />             
</div>
<div id="managelistresult">
    <a href="#" class="jbut" onclick="dialogwindow('newlist')">Upload New List</a>
    <a href="#" class="jbut" onclick="dialogwindow('exportcampaignlists')">Export Campaign Lists</a>
	<!-- <a href="#" class="jbut" onclick="confirmeditcampaignliststdialog('<?php //echo $bcid; ?>')">Edit Campaign Lists</a> -->
	<a href="#" class="jbut" onclick="dialogwindow('editcampaignlists')">Edit Campaign List</a>
<table width="100%" id="mangelistTable">


<?=$ld;?>
</table>
</div>
<script>
	var mangelistTable = $("#mangelistTable").dataTable({
		"aLengthMenu": [ 20, 50, 100, 150],
		 'iDisplayLength': 20,
                 "aaSorting": [[ 2, "desc" ]],
                 "aoColumns": [ null, null, null, null, null, null, null, null, null, null,{ "bVisible":    false }]
	});
	$("#mangelistTable_filter input").show();
	$("#mangelistTable_filter").append("&nbsp;&nbsp;<select id=\"mangelistTableStatus\"><option value=\"\">All</option><option value=\"Active\" selected=\"selected\">Active</option><option value=\"Inactive\">Inactive</option></select>");
	$("#mangelistTableStatus").change(function(){
		var selectVal = $(this).val();
		
	    mangelistTable.fnFilter( selectVal, 7, false, false, false, false );
	});
         mangelistTable.fnFilter( 'Active', 7, false, false, false, false );

	function confirmeditlistdialog(lid) {
    	$('<div></div>').appendTo('body')
        	.html('<div><h2><p style="font-size: 1.4em">This editing tool will be loading your list into the browser.<br/>Depending on the size of your list, <span style="color:red">it may cause your browser to crash</span>.<br/><br/>Are you sure you want to continue?</p></h2></div>')
        	.dialog({
	            modal: true, 
	            title: 'WARNING', 
	            zIndex: 10000, 
	            autoOpen: true,
	            width: 'auto', resizable: false,
	            buttons: {
	                "Confirm": function () {
	                	$(this).dialog("close");
			            editlists(lid);
			        },
			        "Cancel": function () {                                                                 
			            $(this).dialog("close");
			        }
		        },
		        close: function (event, ui) {
		        	$(this).remove();
		    	}
    		});
	}
	function editlists(lid) {
		$(".ecloading").show();
	    $.ajax({
	        url:"editcampaignlists/editlists.php?act=editlists&sub=defaulteditlists&lid="+lid,
	        type: 'GET',
	        success: function(resp){
	        	$("#formloader").dialog("destroy");
	            $("#formloader").html(resp);
	            $("#formloader").dialog({
	                title: "EDIT LIST",
	                width: 1200,
	                height: 600
	            });
	            $(".jbut").button();
	            $(".ecloading").hide();
	        }
	     });
	}
	function confirmeditcampaignliststdialog(bcid) {
    	$('<div></div>').appendTo('body')
        	.html('<div><h2><p style="font-size: 1.4em">This editing tool will be loading your list into the browser.<br/>Depending on the size of your list, <span style="color:red">it may cause your browser to crash</span>.<br/><br/>Are you sure you want to continue?</p></h2></div>')
        	.dialog({
	            modal: true, 
	            title: 'WARNING', 
	            zIndex: 10000, 
	            autoOpen: true,
	            width: 'auto', resizable: false,
	            buttons: {
	                "Confirm": function () {
	                	$(this).dialog("close");
			            editcampaignlists(bcid);
			        },
			        "Cancel": function () {                                                                 
			            $(this).dialog("close");
			        }
		        },
		        close: function (event, ui) {
		        	$(this).remove();
		    	}
    		});
	}
	function editcampaignlists(bcid) {
	    $.ajax({
	        url:"editcampaignlists/editcampaignlists.php?act=selectcampaignlists&act1=editcampaignlists&sub=defaulteditcampaignlists&bcid="+bcid,
	        type: 'GET',
	        success: function(resp){
	        	$("#formloader").dialog("destroy");
	            $("#formloader").html(resp);
	            $("#formloader").dialog({
	                title: "EDIT CAMPAIGN LISTS",
	                width: 1200,
	                height: 600
	            });
	            $(".jbut").button();
	        }
	     });
	}
</script>