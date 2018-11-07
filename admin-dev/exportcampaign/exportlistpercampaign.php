<?php
include_once "../../dbconnect.php";
include_once "../../phpfunctions.php";
if (!empty($_REQUEST['lid'])) {
	$campaignlistidres = mysql_query("SELECT lists.lid, lists.listid, projects.projectid, projects.projectname FROM lists JOIN projects ON projects.projectid = lists.projects WHERE lists.lid = ".$lid."");
	$campaignlistidrow = mysql_fetch_assoc($campaignlistidres);
	$listidlist .= '<option value="'.$lid.'" selected="selected">'.$campaignlistidrow['listid'].'</option>';
	$listidlist .= '<option value="all">All</option>';
	$listres = mysql_query("SELECT * FROM lists WHERE projects = ".$campaignlistidrow["projectid"]." AND active = 1 ORDER BY listid");
	while ($listrow = mysql_fetch_array($listres)) {
		$listidlist .= '<option value="'.$listrow['lid'].'">'.$listrow['listid'].'</option>';
	}
}
$prores = mysql_query("SELECT * from projects where bcid = '$bcid' AND active = 1 ORDER BY projectname");
while ($prorow = mysql_fetch_array($prores)) {
	$prolist .= '<option value="'.$prorow['projectid'].'">'.$prorow['projectname'].'</option>';
}
?>
<script>
	function campaignlistchange() {
		var pel = $("#projectidexportlist").val();
		$.ajax({
		url: "exportcampaign/phpfunctions-include.php?act=updatecampaignlist&projectid="+pel,
		success: function(data){
 			$("#listids").html(data);
		}
		});
	}
</script>
<div id="expcamplists" class="entryform" style="width:250px; height:100px" title="Export Campaign Lists">
	<form action="exportcampaign/exportlistpercampaign-include.php" name="exportprojectlist" id="exportprojectlist" method="post">
		<title>Export Campaign Lists</title>
		<div>
			<label>Campaign:</label>
			<select name="projectidexportlist" id="projectidexportlist" onchange="campaignlistchange();">
				<option value="<?=$campaignlistidrow["projectid"];?>" selected="selected"><?=ucfirst($campaignlistidrow["projectname"]);?></option>
				<?=$prolist;?>
			</select>
		</div>
		<div>
			<label>Lists:</label>
			<select name="listids[]" id="listids" multiple="multiple">
				<?=$listidlist;?>
			</select>
		</div>
		<br/><br/><br/>
		<input type="submit" value="Export">
	</form>
</div>
<?php
    include_once("nospecialchars.php");
?>
