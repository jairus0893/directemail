<?php
$getdnclist = mysql_query("SELECT donotcall_list.*, projects.projectname, lists.listid FROM donotcall_list JOIN lists ON lists.lid = donotcall_list.dnc_listid 
JOIN projects ON projects.projectid = donotcall_list.dnc_projectid  
WHERE donotcall_list.dnc_bcid  = $bcid AND donotcall_list.dnc_active = 1 AND donotcall_list.dnc_is_deleted = 0;");
while ($rowdnclist = mysql_fetch_assoc( $getdnclist )) {
	$ind = $rowdnclist['id'];
	$dnclists[$ind] = $rowdnclist;
}

$dncdisp .= '<thead><tr>';
$dncdisp .= '<th class="tableheadercenter">Campaign</th>';
$dncdisp .= '<th class="tableheadercenter">List ID</th>';
$dncdisp .= '<th class="tableheadercenter">Wash List Name</th>';
$dncdisp .= '<th class="tableheadercenter">Date Added</th>';
$dncdisp .= '<th class="tableheadercenter">Wash Expiration Date</th>';
$dncdisp .= '<th class="tableheadercenter">Records</th>';
$dncdisp .= '<th class="tableheadercenter">Uploaded By</th>';
$dncdisp .= '<th class="tableheadercenter">is Active</th>';
$dncdisp .= '<th class="tableheadercenter">Actions</th>';
$dncdisp .= '</tr></thead>';
$dncdisp .= '<tbody>';

$resmembers = mysql_query("SELECT *, memberdetails.afirst, memberdetails.alast, memberdetails.team from members left join memberdetails on members.userid = memberdetails.userid where bcid = 
".$bcid." AND alast <> '' AND afirst <> '' ORDER BY alast,afirst,userlogin");
while ($rowmembers = mysql_fetch_assoc($resmembers)) {
	$allmembers[$rowmembers['userid']] = $rowmembers;
}

foreach($dnclists as $dnclist) {
	$activetoggle = $dnclist["dnc_active"] == 1 ? '<a href="#" onclick="togglednclist(\''.$dnclist["id"].'\', \'deactivate\')">Deactivate</a>' : '<a href="#" onclick="togglednclist(\''.$dnclist["id"].'\', \'activate\')">Activate</a>';
	$status = $dnclist["dnc_active"] == 1 ? 'Active' : 'Inactive';
	$dncdisp .= '<tr>';
	$dncdisp .= '<td class="datas"'.$color.'>'.$dnclist["projectname"].'</td>';
	$dncdisp .= '<td class="datas">'.$dnclist["listid"].'</td>';
	$dncdisp .= '<td class="datas">'.$dnclist["dnc_listname"].'</td>';
	$dncdisp .= '<td class="datas">'.date("Y-m-d",$dnclist["dnc_date_created"]).'</td>';
	$pos = strpos($dnclist["dnc_listname"], "agentgenerated");
	if ($pos === false) {
		$dncdisp .= '<td class="datas">'.date("Y-m-d",$dnclist["newwashdate"]).'</td>';
	} else {
		$dncdisp .= '<td class="datas"></td>';
	}
	$dncdisp .= '<td class="datas">'.$dnclist["dnc_listcount"].'</td>';
	$dncdisp .= '<td class="datas">'.$allmembers[$dnclist['userid']]['afirst'] . ' ' . $allmembers[$dnclist['userid']]['alast'].'</td>';
	$dncdisp .= '<td class="datas">'.$status.'</td>';
	//$dncdisp .= '<td class="datas">'.$activetoggle.' | <a href="#">Export</a> | <a href="#" onclick="removednc(\''.$dnclist["id"].'\')">Remove</a></td>';
	//$dncdisp .= '<td class="datas">'.$activetoggle.' | <a href="#" onclick="removednc(\''.$dnclist["id"].'\')">Remove</a></td>';
	$dncdisp .= '<td class="datas"><a href="admin.php?act=donotcallexport&dncid='.$dnclist["id"].'">Export</a> | <a href="#" onclick="removednc(\''.$dnclist["id"].'\')">Remove</a></td>';
	$dncdisp .= '</tr>';
}
$dncdisp .= '</tbody>';
?>
<a href="#" class="jbut" onclick="dialogwindow('newdnclist')">Upload Wash List Update</a>
<table width="100%" id="managednclistTable">
<?php echo $dncdisp;?>
</table>
<!-- FOR TEST -->


<!-- END OF TEST -->
<script>
	var managednclistTable = $("#managednclistTable").dataTable({
		"aLengthMenu": [ 20, 50, 100, 150],
		'iDisplayLength': 20,
		"aaSorting": [[ 2, "desc" ]]
	});
	$("#managednclistTable_filter input").show();
	$("#managednclistTable_filter").append("&nbsp;&nbsp;<select id=\"managednclistTableStatus\"><option value=\"Active\" selected=\"selected\">Active</option><option value=\"Inactive\">Inactive</option><option value=\"\">All</option></select>");
	$("#managednclistTableStatus").change(function(){
		var selectVal = $(this).val();
	    managednclistTable.fnFilter( selectVal, 7, false, false, false, false );
	});
	managednclistTable.fnFilter( 'Active', 7, false, false, false, false );
</script>