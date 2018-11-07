<?php
    // BEGIN if ($act == 'addext')
?>
	<script>
	$( document ).ready(function() {
	    $("input[name='rangecheckbox']").change(function() {
		    if(this.checked) {
		    	$('input[name="username"]').hide();
		    	var username = $('input[name="username"]').val();
		    	$('input[name="usernamerangefrom"]').val(username);
		        $('#addextensionbutton').hide();
				$('#addextensiontable').hide();
				$('#rangefromandto').show();
				$('#addextensiowithrange').show();
		    } else {
		    	var rangefrom = $('input[name="usernamerangefrom"]').val();
		    	$('input[name="username"]').show();
		    	$('#addextensionbutton').show();
				$('#addextensiontable').show();
				$('#rangefromandto').hide();
				$('#addextensiowithrange').hide();
			    $('input[name="username"]').val(rangefrom);
		    }
		});
		$("input[name='addextensionbutton']").click(function() {
		    var bcid 		= $("select[name='bcid']").val();
		    var username 	= $("input[name='username']").val();
		    var secret 		= $("input[name='secret']").val();
		    var classification 	= $("select[name='classification']").val();
		    if(bcid != "" && username != "" && secret != "" && classification != "") {
		    	$.ajax({
			        url: 'superadmin/super-include.php?checkextension',
			        type: 'POST',
			        data: {
			            "username": username
			        },
			        success:function(result) {
			        	if ( result != "" ) {
			        		$("#alertfillupfields").html("Username exists. Please try again.");
					    	$("#alertfillupfields").dialog({
					            minWidth: 50,
					            minHeight: 50,
					            title: 'Error!'
					        });
			        	} else {
			        		$.ajax({
						        url: 'super.php?act=saveext',
						        type: 'POST',
						        data: {
						            "bcid": bcid, "username": username, "secret": secret, "classification": classification
						        },
						        success: function(resp) {
						        	$('select[name="bcid"]').val("");
								    $('input[name="username"]').val("");
								    $('input[name="secret"]').val("");
								    $('select[name="classification"]').val("");
						        	$("#alertfillupfields").html("Extension created successfully.");
							    	$("#alertfillupfields").dialog({
							            minWidth: 50,
							            minHeight: 50,
							            title: 'Success!'
							        });
							        window.location.href = 'super.php?act=extensions';
						        }
						    });
			        	}
			        }
			    });
		    } else {
		    	$("#alertfillupfields").html("Please fill up all the fields.");
		    	$("#alertfillupfields").dialog({
		            minWidth: 50,
		            minHeight: 50,
		            title: 'Warning!'
		        });
		    }

		});
	});
	</script>
	<style>
		#alertextensionsuccessful {
			display:none;
			width:250px;
			height:250px;
		}
		#alertfillupfields {
			display:none;
			width:250px;
			height:250px;
		}
	</style>
	<div id="alertextensionsuccessful">

	</div>
	<div id="alertfillupfields">

	</div>
    <form name="addextension" id="addextension">
    <table width="300">

    <tr><td>Client</td><td><select name="bcid"><option></option><?=$drop;?></select></td></tr>
    <tr><td>Username</td><td><input type="text" name="username" /><span id="rangefromandto" style="display:none"><input type="number" name="usernamerangefrom" min="0" style="width:90px"/>&nbsp;-&nbsp;<input type="number" name="usernamerangeto" min="0" style="width:90px"/></span><br/><input type="checkbox" id="rangecheckbox" name="rangecheckbox"/><label for="rangecheckbox">Enable range</label></td></tr>
    <tr><td>Password</td><td><input type="text" name="secret" /></td></tr>
    <tr><td>Classification</td>
    	<td>
    		<select name="classification" id="classification">
    			<option value="agent" selected="selected">agent</option>
    			<option value="admin">admin</option>
    		</select>
    	</td>
    </tr>
    <tr>
    	<td colspan="2">
    		<input type="button" value="Add" class="jbut" name="addextensionbutton" id="addextensionbutton"/>
    		<input type="button" onclick="showbulkaddextensionwithrange()" value="Add By Range" class="jbut" id="addextensiowithrange" style="display: none"/>
    		<input type="button" onclick="showbulkaddextensiontable()" value="Bulk Action" class="jbut" id="addextensiontable"/>
    	</td>
    </tr>

    </table>
    </form>
    <br/>
    <div id="bulkaddextensiondivtable" style="display:none">
    <table width="300" id="bulkaddextensiontable">
    <thead>
    <tr>
    	<th>Client</th>
    	<th>Username</th>
    	<th>Password</th>
    	<th>Classification</th>
    	<th>Action</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
    </table>
    <input type="button" onclick="savebulkext()" value="Bulk Add Extension" class="jbut" />
    </div><?php
    // END if ($act == 'addext')
?>
