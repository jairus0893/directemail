            <br/>
            <?
			$projectid = $_REQUEST['projid'];
			$userid = $session->userid;
            // FOR UIOPT TABLE - If enable or not
			$getQueuePreviewIsEnabled = mysql_query("SELECT * FROM uiopt WHERE project_id = '$projectid' AND config LIKE 'QueuePreviewEnabled' ORDER BY ts DESC LIMIT 1");
	    	while ($getrowenabled = mysql_fetch_assoc($getQueuePreviewIsEnabled)) {
				$getdataenabled[] = $getrowenabled;
			}
			
			if ( $getdataenabled[0]["value"] == "Y" ) {
            ?>
			<script>
				$( document ).ready(function() {
					$('#hopper_datatable').dataTable({
						"sPaginationType": "full_numbers",
				    	"aaSorting":[[5, "asc"]],
				    	"bInfo" : false
				    });
				});

				function qptdial(led) {	
					astatus = 'prevlead';
					if (cbable) {
				        if (astatus == 'dialingcb') {
							alert("Hangup the Call first!");
						}
				        var thi = document.getElementById('disposition').selectedIndex;
						var sId = document.getElementById('leadid').value;
				        if (thi == 0 && sId !=0 && astatus != 'cbview') {
							var t =  Ext.ComponentMgr.get("maintabpanel");
				            t.activate(0);
				            dispose(cbdial,led);
						} else {
							if (astatus == 'paused' || astatus =='hanged' || astatus == 'preview' || astatus == 'cbview' || astatus == 'newlead' || astatus == 'prevlead') {
								var uId = '<?=$userid;?>';astatus = 'prevlead';
								submitter("getsearchdetails&user="+uId+"&leadid="+led, function(resp) {
				                    enableb('dbb');
				                    showb('nbb');
				                    try {
				                    	hideb('start');
				                    } catch (e) {
				
				                    }
				                    checkingnew = 21;
				                    Ext.getCmp('maintabpanel').activate(0);
				                    populate(resp);
								});
							}
						}
					}
				}
				
				function deleteRow(row) {
					var listid = $("#listid").val();
					var disposition = $("#disposition").val();
					
					if ( listid != "" && disposition == "" ) {
						
				    } else if ( listid == "" && disposition == "" ) {
				   		var i=row.parentNode.parentNode.rowIndex;
					    document.getElementById('hopper_datatable').deleteRow(i);
				    } else if ( listid != "" && disposition != "" ) {
				   		var i=row.parentNode.parentNode.rowIndex;
					    document.getElementById('hopper_datatable').deleteRow(i);
				    }
				}
				function checkhopper(leadid, projectid) {	
					var listid = $("#listid").val();
					var disposition = $("#disposition").val();
					$("#leadidtoapplybookingcalendar").val(leadid);
					if ( listid != "" && disposition == "" ) {
						alert("Please provide a disposition before selecting another lead.");
					} else if ( listid == "" && disposition == "" ) {
						$.ajax({
				            url: "ajax.php?act=checkhopper&leadid="+leadid+"&projectid="+projectid,
				            success: function(data){
								if ( data == "1" ) {
									$('#alertforcalled').html("Lead not available for call. Please select another lead.");
									$("#alertforcalled").dialog({width:200,height:200});
								} else if ( data == "0" ) {
									qptdial(leadid);
								}
				            }
				        });
					} else if ( listid != "" && disposition != "" ) {
						var calendar = $("#calendar").val();
						var slotdatecalendar = $("#slotdatecalendar").val();
						var leadid = $("#leadidtoapplybookingcalendar").val();
						var slotid = $("#slotidfrombookingcalendar").val();
						var dispo = $("#disposition").val();
						$.ajax({
							url: "bookingrecurringslot/appbook-include.php?act=updatetakenslot&calendar="+calendar+"&slotdatecalendar="+slotdatecalendar+"&leadid="+leadid+"&slotid="+slotid+"&dispo="+dispo,
							success: function(data){
								$.ajax({
						            url: 'ajax.php?act=checkhopper&leadid='+leadid+"&projectid="+projectid,
						            success: function(data){
										if ( data == "1" ) {
											$('#alertforcalled').html("Lead not available for call. Please select another lead.");
											$("#alertforcalled").dialog({width:200,height:200});
										} else if ( data == "0" ) {
											qptdial(leadid);
										}
						            }
						        });
						    }
					    });
					}
				}
			</script>
			<div id="alertforcalled" style="display:none; width:200px;height:200px;">		
			</div>
            <div style="clear:both"></div>
			<div id="hopper_accordion">
				<span style="color:#e17009"><h4>Queue Preview</h4></span>
	        	<br/>
				<table cellpadding="0" cellspacing="0" border="0" class="dataTable" id="hopper_datatable" style="font-size:0.8em">
				    <thead>
				        <tr>
				            <?
					        	$projectid = $_REQUEST['projid'];
								$eyebeam = $_REQUEST['eyebeam'];
								$userid = $session->userid;
								
								$getQueuePreviewCustomizeView = mysql_query("SELECT * FROM uiopt WHERE project_id = '$projectid' AND config LIKE 'QueuePreviewFields' ORDER BY ts DESC LIMIT 1");
								$getQueuePreviewIsEnabled = mysql_query("SELECT * FROM uiopt WHERE project_id = '$projectid' AND config LIKE 'QueuePreviewEnabled' ORDER BY ts DESC LIMIT 1");
					    		
					    		$getdata = array();
        
								while ($getrow = mysql_fetch_assoc($getQueuePreviewCustomizeView)) {
									$getdata = $getrow;
									
								}
								
								$jsonintoarray = json_decode($getdata["value"]);
								
								while ($getrowenabled = mysql_fetch_assoc($getQueuePreviewIsEnabled)) {
									$getdataenabled[] = $getrowenabled;
								}
								
								if ( $getdataenabled[0]["value"] == "Y" ) {
									foreach ($jsonintoarray as $getresponse) {
										$responseheader = $getresponse[0];
										echo "<th>$responseheader</th>";
									}
								} else if ( $getdataenabled[0]["value"] == "N" ) {
									echo "<th>Name</th>
											<th>Title</th>
											<th>Phone</th>
											<th>Mobile</th>
											<th>Alt Phone</th>
											<th>Suburb</th>
											<th>State</th>
											<th>Disposition</th>";
								}
								
							?>
				            <th></th>
				        </tr>
				    </thead>
				    <tbody>
				    	<?
				    		$projectid = $_REQUEST['projid'];
							$userid = $session->userid;
							
				        	// FOR UIOPT TABLE	
				        	$uiopt_data = mysql_query("SELECT * FROM uiopt WHERE project_id = '$projectid' AND config LIKE 'QueuePreviewFields' ORDER BY ts DESC LIMIT 1");
							while ($uioptdatadetails = mysql_fetch_array($uiopt_data)) {
								$uiopt = array_unique($uioptdatadetails);
							}
							$uioptvalue = json_decode($uiopt[4]); //decoding the json text
							foreach ( $uioptvalue as $uioptkey => $uioptval ) {
								$countkeys[] = $uioptkey; //will be use to count the $uioptkey
								$keys .= "leads_raw.".$uioptkey.", ";
							}
							$keys = rtrim($keys, ", "); //will be use in hopper table (select query)
							
							// FOR UIOPT TABLE - If enable or not
							$getQueuePreviewIsEnabled = mysql_query("SELECT * FROM uiopt WHERE project_id = '$projectid' AND config LIKE 'QueuePreviewEnabled' ORDER BY ts DESC LIMIT 1");
					    	while ($getrowenabled = mysql_fetch_assoc($getQueuePreviewIsEnabled)) {
								$getdataenabled[] = $getrowenabled;
							}
							
							//FOR HOPPER TABLE - If enabled
							$hopper_data = mysql_query("SELECT $keys, hopper.leadid FROM hopper
													JOIN leads_raw ON leads_raw.leadid = hopper.leadid 
													WHERE hopper.projectid = '$projectid' and hopper.called = 0
													ORDER BY rand()");
							while ($hopper = mysql_fetch_array($hopper_data)) {
								$gethopperdetails[] = array_unique($hopper);
							}
							
							//FOR HOPPER TABLE - If not enabled
							$hopper_not_enabled_data = mysql_query("SELECT leads_raw.cname, leads_raw.title, leads_raw.phone, leads_raw.mobile, leads_raw.altphone, leads_raw.suburb, leads_raw.state, leads_raw.dispo, hopper.leadid 
													FROM hopper
													JOIN leads_raw ON leads_raw.leadid = hopper.leadid 
													WHERE hopper.projectid = '$projectid' and hopper.called = 0
													ORDER BY rand()");
							while ($hopper_not_enabled = mysql_fetch_array($hopper_not_enabled_data)) {
								$gethoppernotenableddetails[] = array_unique($hopper_not_enabled);
							}
							
							if ( $getdataenabled[0]["value"] == "Y" ) {	
								foreach ($gethopperdetails as $gethopperdetail) {
									
									echo "<tr>";
									
									for ($i=0; $i < count($countkeys); $i++) {
										if ( $gethopperdetail[$i] == NULL ) {
											echo "<td></td>";
										} else {
											echo "<td>$gethopperdetail[$i]</td>";
											
										}
									}
									$lastinarray = end($gethopperdetail); //get the last value in last array for button
									echo "<td><a href=\"#accordion\" onclick=\"deleteRow(this)\"><input type=\"button\" onClick=\"checkhopper('$lastinarray','$projectid')\" class=\"jbutton row_remove\" value=\"Select\"/></a></td>";

									echo "</tr>";
								}
							} else if ( $getdataenabled[0]["value"] == "N" ) {
								
								foreach ($gethoppernotenableddetails as $gethoppernotenableddetail) {
									echo "<tr>";
									
									for ($i=0; $i < 8; $i++) {
										if ( $gethoppernotenableddetail[$i] == NULL ) {
											echo "<td></td>";
										} else {
											echo "<td>$gethoppernotenableddetail[$i]</td>";
											
										}
									}
									$lastinarray = end($gethoppernotenableddetail); //get the last value in last array for button
									echo "<td><a href=\"#accordion\" onclick=\"deleteRow(this)\"><input type=\"button\" onClick=\"checkhopper('$lastinarray', '$projectid')\" class=\"jbutton row_remove\" value=\"Select\"/></a></td>";
								
									echo "</tr>";
								}
							}
						?>
				        
				    </tbody>
				</table>
			</div>
			<?
			} else {
				
			}
			?>
            <br/>
<!--        </div>-->
