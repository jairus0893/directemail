<?php
$listquery = "SELECT * from lists where bcid = '$bcid' AND is_deleted = 0 ORDER BY active DESC, listid ASC";
$listres = mysql_query($listquery);
while ($listrow = mysql_fetch_assoc($listres)) {
	$date_now = date("Y-m-d");
	if (!empty($listrow["washdate"])) {
		if (date("Y-m-d", $listrow["washdate"]) >= $date_now) {
			$expiredlistdrop .= "";
		} else {
			$expiredlistdrop .= '<option value="'.$listrow['lid'].'">'.$listrow['listid'].'</option>';
		}
	} else {
		$expiredlistdrop .= "";
	}
}
$sellist = '<select name="listid" id="listid"><option value="">Please select..</option>'.$expiredlistdrop.'</select>';
?>
<script>
	$(".dateinput").datepicker({ dateFormat: 'yy-mm-dd' });
	// $('#divwashdate').hide(); 
	// $('#iswashyes').click(function() {
		// $('#divwashdate').show();
	// });
	// $('#iswashno').click(function() {
		// $('#divwashdate').hide();
		// $('#washdate').val('');
	// });
</script>
<style>
	#loadinglist {
	    z-index:11; 
	    position:absolute;
	    top:0px;
	    left:0px;
	    text-align:center;
	}
</style>
<div id="loadinglist" style="display:none">    
    <img style="margin-top:130px;" src="loading_big.gif" alt="loading" />             
</div>
<div class="entryform" style="width:300px; height:250px" title="New Do Not Call List">
	<form name="dncuploadcsv" id="dncuploadcsv" >
		<?= $iswin; ?>
		<title>Upload Wash List</title>
		<div id="respmessage"></div>
		<div><label>Expired Lists:</label>
		<?=$sellist;?></div>
		<div><label>Wash List Name: </label><input type="text" id="dnclistname" name="dnclistname" /></div>
		<input type="hidden" name="act" value="dnclistupload" />
		<!-- <div><label>Is Wash?</label> 
            <div class="dupcheck">
            	<input name="iswashcheck" id="iswashno" type="radio" value="0" checked="yes"/> No
            	<br/>
                <input name="iswashcheck" id="iswashyes" type="radio" value="1"/> Yes
        	</div>
        </div> -->
        <div class="clear"></div>
        <div id="divwashdate"><label>Wash Expiration Date: </label><input type="text" id="washdate" name="washdate" class="dateinput"/>
        	<br/>
        	<i>
        		<p style="font-size:xx-small;">&#128712&nbsp;This will serve as the <b>new</b> expiration date of the list.</p>
        	</i>
        </div>
        <div>
        	<input id="MAX_FILE_SIZE" name="csvfile" type="file" style="float:left;width:100%" />
        </div>
		<div class="clear"></div>
		<div id="progress">
            <div id="pbar"></div>
        </div>
	</form>
	<div class="buttons" style="position:relative">
		<input type="button" value="Upload" onclick="dnclistupload()">
	</div>
</div>