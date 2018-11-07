<?php
$dispores = mysql_query("SELECT * from statuses where active = 1 and projectid = '".$session->projectid."' or projectid = '0' order by sort,statusname ASC");
?>
<div id="bulkdispose" style="display:none">
                <table width="100%" style="font-size:0.8em">
                   <tr><td class="title">Disposition:</td><td><select class="box" name="bulkdisposition" id="bulkdisposition" onFocus="focusbox(this);" onBlur="outfocus(this);" onChange="this.options[this.selectedIndex].onclick()">
                    <option selected> </option>
                    	<?
						while ($disp = mysql_fetch_array($dispores))
							{
							if ($disp['statustype'] == 'dateandtime')
								{
											echo "<option onclick=\"bulkcreatedateinput()\">";
											echo $disp['statusname'];
											echo "</option>";

								}
							elseif ($disp['statustype'] == 'booking')
										{
											//echo "<option onclick=\"doslots()\">";
											//echo $disp['statusname'];
											//echo "</option>";
										}
							elseif ($disp['statustype'] == 'link')
								{
								//echo "<option onclick=\"showupdatepage('".$disp['statusid']."')\">";
								//echo $disp['statusname'];
								//echo "</option>";
								}
							elseif ($disp['statustype'] == 'update')
								{
								//echo "<option onclick=\"showupdatepage()\">";
								//echo $disp['statusname'];
								//echo "</option>";
								}	
							else{
								echo "<option onclick=\"bulkcleardateinput();\">";
								echo $disp['statusname'];
								echo "</option>";
							}
							}
						?>
                    </select></td></tr>
                <tr id="bulkdatetd" style="display:none; text-align:left;" class="title"><td class="title">Date:</td><td><input type="text" id="bulkcalendar" name="bulkcalendar" class="datetimepicker" /></td></tr>
                <tr><td></td><td><input type="button" class="jbut" value="Done" onclick="dobulkdispose()" /></td></tr>
                </table>               
</div>
<script>

</script>