<?php
include_once "../../dbconnect.php";
include_once "../../phpfunctions.php";
$prores = mysql_query("SELECT * from projects where bcid = '$bcid' AND active = 1 ORDER BY projectname");
while ($prorow = mysql_fetch_array($prores)) {
	$prolist .= '<option value="'.$prorow['projectid'].'">'.$prorow['projectname'].'</option>';
}
?>
<script>
	function confirmeditlistdialog() {
		var pid = $("#projectideditlist").val();
		var lid = $("#listids").val();
		if (pid != "" && lid != "") {
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
		} else {
			alert("Please fill the required fields!")
		}
	}
	function campaignlistchange() {
		var pel = $("#projectideditlist").val();
		$.ajax({
		url: "editcampaignlists/phpfunctions-include.php?act=updatecampaignlist&projectid="+pel,
		success: function(data){
 			$("#listids").html(data);
		}
		});
	}
</script>
<div id="expcamplists" class="entryform" style="width:250px; height:100px" title="Export Campaign Lists">
	<title>Edit Campaign List</title>
	<div>
		<label>*Campaign:</label>
		<select name="projectideditlist" id="projectideditlist" onchange="campaignlistchange();">
			<option value="<?=$campaignlistidrow["projectid"];?>" selected="selected"><?=ucfirst($campaignlistidrow["projectname"]);?></option>
			<?=$prolist;?>
		</select>
	</div>
	<div>
		<label>*Lists:</label>
		<select name="listids" id="listids">
			<?=$listidlist;?>
		</select>
	</div>
	<br/><br/><br/>
	<input type="submit" value="Edit" onclick="confirmeditlistdialog()">
</div>
<?php
    include_once("nospecialchars.php");
?>
