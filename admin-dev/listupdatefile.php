<?php
$prores = mysql_query("SELECT * from projects where bcid = '$bcid' AND active = 1 ORDER BY projectname");
while ($prorow = mysql_fetch_array($prores))
	{
		$prolist .= '<option value="'.$prorow['projectid'].'">'.$prorow['projectname'].'</option>';
	}

	$selproject = '<select name="projects" id="listproj"><option></option>'.$prolist.'</select>';
?>
                 <div class="entryform" style="width:300px; height:250px" title="New List">
                <form name="dispoupdateuploadcsv" id="dispoupdateuploadcsv" >
                <?=$iswin;?>
                <title>Upload Disposition Update List</title>
                <div id="respmessage"></div>
                
                <div><label>Campaign:</label>
                <?=$selproject;?></div>
                <input type="hidden" name="act" value="dispoupdateupload" />
                <input type="hidden" name="MAX_FILE_SIZE" value="1000000000" id="MAX_FILE_SIZE"/>
                <br>
                <div><label>CSV File:</label>
                <p>Must be formatted with two columns, with the first column containing the set of phone numbers to update
                and the second column having the corresponding disposition.
                </p>
                </div> 
                <div>
               <input id="MAX_FILE_SIZE" name="csvfile" type="file" style="float:left;width:100%" /></div>
                <div class="clear"></div>
                <div id="progress">
                    <div id="pbar"></div>
                </div>
                
                </form><div class="buttons" style="position:relative">
				<input type="button" value="Next" onclick="dispoupdateupload()"></div>
                </div>