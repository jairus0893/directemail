<?php
include_once "../dbconnect.php";
$prores = mysql_query("SELECT * from projects where bcid = '$bcid' AND active = 1 ORDER BY projectname");
while ($prorow = mysql_fetch_array($prores))
	{
		$prolist .= '<option value="'.$prorow['projectid'].'">'.$prorow['projectname'].'</option>';
	}

	$selproject = '<select name="projects" id="listproj"><option></option>'.$prolist.'</select>';
	$target = 'action="leadsloader.php" target="mapper"  onSubmit="window.open(\'\',\'mapper\');"';
?>
				<script>
					$(".dateinput").datepicker({ dateFormat: 'yy-mm-dd' });
					$('#divwashdate').hide(); 
					$('#iswashyes').click(function() {
						$('#divwashdate').show();
					});
					$('#iswashno').click(function() {
						$('#divwashdate').hide();
						$('#washdate').val('');
					});
				</script>
				<style>
		    		#loading {
					    z-index:11; 
					    position:absolute;
					    top:0px;
					    left:0px;
					    text-align:center;
					}
		    	</style>
                 <div class="entryform" style="resize: both; width:300px; height:350px" title="New List">
                <div id="loading" style="display:none">    
			        <img style="margin-top:130px;" src="loading_big.gif" alt="loading" />             
			    </div>
			    <form name="uploadcsv" id="uploadcsv" >
                <?=$iswin;?>
                <title>Upload List</title>
                <div id="respmessage"></div>
                
                <div><label>Campaign:</label>
                <?=$selproject;?></div>
                <div><label>List ID: </label><input type="text" id="listid" name="listid" onblur="validate(this,'lists')"/></div>
                <div style="text-align:right">Please ensure that the List ID is unique and that it only contains alphanumeric characters</div>
                <div><label>Type of Data:</label> <select name="leadtype" id="leadtype"><option value="b">Business</option><option value="i">Residential</option><option value="m">Mixed</option></select></div>
                
                <div><label>Description: </label><textarea name="listdescription" id="listdescription"></textarea></div><div class="clear"></div>
                <div><label>Duplicate Check:</label> 
                <div class="dupcheck">
                    <input name="dupcheck" type="radio" value="nocheck"/> No duplicate check<br>
                    <input name="dupcheck" type="radio" value="listonly"/> Within this List Only<br>
                    <input name="dupcheck" type="radio" value="project" checked="yes"/> Lists in this Campaign<br>
                    <input name="dupcheck" type="radio" value="all"/> All Lists in the System</div></div>
                <div class="clear"></div>
                <div><label>Is Wash?</label> 
	                <div class="dupcheck">
	                	<input name="iswashcheck" id="iswashno" type="radio" value="0" checked="yes"/> No
	                	<br/>
	                    <input name="iswashcheck" id="iswashyes" type="radio" value="1"/> Yes
	            	</div>
                </div>
                <div class="clear"></div>
                <div id="divwashdate"><label>Wash Expiration Date: </label><input type="text" id="washdate" name="washdate" class="dateinput"/>
                <br/><i><p style="font-size:xx-small;">&#128712&nbsp;This will serve as the expiration date of the list.</p></i>
                </div>
				<div class="clear"></div>
                 <input type="hidden" name="act" value="listupload" />
                <input type="hidden" name="MAX_FILE_SIZE" value="1000000000" id="MAX_FILE_SIZE"/>
                <br>
                <div><label>CSV File:</label>(Leave Blank for blank lists)</div> 
                <div>
               <input id="MAX_FILE_SIZE" name="csvfile" type="file" style="float:left;width:100%" /></div>
                <div class="clear"></div>
                <div id="progress">
                    <div id="pbar"></div>
                </div>
                
                </form>
                <i><p style="color:red; font-size: 1em;">&#128712&nbsp;By clicking <b>NEXT</b>, you <b>CANNOT</b> edit the <b>WASH DATE</b>. Please ensure that you have the right date.</p></i>
                <div class="clear"></div>
                <div class="buttons" style="position:relative">
				<input type="button" value="Next" onclick="validuploads3ForList()"></div>
				</div>
<?php
    include_once("nospecialchars.php");
?>
