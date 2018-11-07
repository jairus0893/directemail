<script>
function extbulkdeleteextensions(bc) {
    var action = $("#bulkaction").val();
    var ct = 0;
    var bid = new Array();
    $("[name=bulkaction]").each(function() {
        if (this.checked) {
            bid[ct] = $(this).val();
            ct++;
        }
    });
    var cts = 0;
    if (action != '') {
	    $.ajax({
	        url: 'super.php?extbulkdeleteextensions',
	        type: 'POST',
	        data: {
	            "bcids": bid
	        },
	        success: function(){
	            loadtabextensions('extensions',bid);
	        }
	    });
    }
}
function extbulkdeleteproviders(bc) {
    var action = $("#bulkaction").val();
    var ct = 0;
    var bid = new Array();
    $("[name=bulkaction]").each(function() {
        if (this.checked) {
            bid[ct] = $(this).val();
            ct++;
        }
    });
    var cts = 0;
    if (action != '') {
	    $.ajax({
	        url: 'super.php?extbulkdeleteproviders',
	        type: 'POST',
	        data: {
	            "bcids": bid
	        },
	        success: function(){
	        	loadtabproviders('extensions',bid);
	        }
	    });
    }
}
function loadtabextensions(tab,bcid) {
    if (tab == 'extensions') {
	    $.ajax({
	        url: "super.php?act=extensions&sub=1&bcid="+bcid,
	        success: function(resp){
	        	//$("#tabextensions").html(resp);
	            //var dt = $("#tabextensions table").dataTable();
	            var currentclient = $("#editclientdrop").val();
	            //dt.fnFilter(currentclient,3);
	            $(".dataTables_length").html('<select id="bulkaction" onchange="extbulkdeleteextensions()"><option value="">Bulk Action</option><option value="delete">Delete</option></select>');
	            $(".dataTables_info").html(" ");
	            $(".jbut").button();
	            window.location.href = 'super.php?act=extensions';
	        }
		});
    }
    if (tab == 'users') {
	    $.ajax({
		    url: "super.php?act=users&bcid="+bcid,
		    success: function(resp){
		            $("#tabusers").html(resp);
		            var dt = $("#tabusers table").dataTable();
		            var currentclient = $("#editclientdrop").val();
		            dt.fnFilter(currentclient,5);
		            dt.fnSetColumnVis(5,false);
		            $(".dataTables_length").html('<select id="bulkaction" onchange="userbulkaction()"><option value="">Bulk Action</option><option value="enable">Activate</option><option value="disable">Deactivate</option></select>');
		            $(".dataTables_info").html(" ");
		            $(".jbut").button();
		    }
	    });
    }
}
function loadtabproviders(tab,bcid) {
    if (tab == 'extensions') {
	    $.ajax({
	        url: "super.php?act=extensions&sub=1&bcid="+bcid,
	        success: function(resp){
	        	window.location.href = 'super.php?act=providers';
	        }
		});
    }
    if (tab == 'users') {
	    $.ajax({
		    url: "super.php?act=users&bcid="+bcid,
		    success: function(resp){
		            $("#tabusers").html(resp);
		            var dt = $("#tabusers table").dataTable();
		            var currentclient = $("#editclientdrop").val();
		            dt.fnFilter(currentclient,5);
		            dt.fnSetColumnVis(5,false);
		            $(".dataTables_length").html('<select id="bulkaction" onchange="userbulkaction()"><option value="">Bulk Action</option><option value="enable">Activate</option><option value="disable">Deactivate</option></select>');
		            $(".dataTables_info").html(" ");
		            $(".jbut").button();
		    }
	    });
    }
}
function showbulkaddextensiontable() {
    var bcid = $('select[name="bcid"]').val();
    var username = $('input[name="username"]').val();
    var secret = $('input[name="secret"]').val();
    var classification = $('select[name="classification"]').val();
    if( bcid != "" && username != "" && secret != "" && classification != "" ) {
    	$('#addextensionbutton').hide();
		$('#addextensiowithrange').hide();
    	$("#bulkaddextensiondivtable").show();
    	bulkaddextension = "<tr><td>"+bcid+"</td><td>"+username+"</td><td>"+secret+"</td><td>"+classification+"</td><td><a onclick='deleteRow(this)'>Remove</a></td></tr>";
    	$("#bulkaddextensiontable").find("tbody").append(bulkaddextension);
    	$('select[name="bcid"]').val("");
	    $('input[name="username"]').val("");
	    $('input[name="secret"]').val("");
	    $('select[name="classification"]').val("");
    } else {
    	$("#alertfillupfields").html("Please fill up all the fields.");
    	$("#alertfillupfields").dialog({
            minWidth: 50,
            minHeight: 50,
            title: 'Warning!'
        });
    }
}
function showbulkaddextensionwithrange(){
    var bcid = $('select[name="bcid"]').val();
    var usernamefrom = $('input[name="usernamerangefrom"]').val();
    var usernameto = $('input[name="usernamerangeto"]').val();
    var secret = $('input[name="secret"]').val();
    var classification = $('select[name="classification"]').val();
    if( bcid != "" && usernamefrom != "" && usernameto != "" && secret != "" && classification != "" ) {
    	if ( usernameto > usernamefrom ) {
    		$('#addextensionbutton').attr("disabled", "disabled");
			$('#addextensiontable').attr("disabled", "disabled");
			$.ajax({
		        url: 'superadmin/super-include.php?checkextensionrange',
		        type: 'POST',
		        data: {
		            "usernamefrom": usernamefrom, "usernameto": usernameto
		        },
		        success:function(result) {
		        	if ( result != "" ) {
		        		$("#alertfillupfields").html("Username : "+result+" exist(s). Nothing save. Please try again.");
				    	$("#alertfillupfields").dialog({
				            minWidth: 50,
				            minHeight: 50,
				            title: 'Error!'
				        });
		        	} else { 
		        		$.ajax({
					        url: 'super.php?saverangeext',
					        type: 'POST',
					        data: {
					            "bcid": bcid, "usernamefrom": usernamefrom, "usernameto": usernameto, "secret": secret, "classification": classification
					        },
					        success: function(resp) {
					        	$("#alertfillupfields").html("Extensions created successfully.");
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
    	} else if ( usernameto < usernamefrom ) {
    		$("#alertfillupfields").html("Invalid range. Please try again.");
	    	$("#alertfillupfields").dialog({
	            minWidth: 50,
	            minHeight: 50,
	            title: 'Error!'
	        });
    	} else if ( usernameto = usernamefrom ) {
    		$("#alertfillupfields").html("Invalid range. Please try again.");
	    	$("#alertfillupfields").dialog({
	            minWidth: 50,
	            minHeight: 50,
	            title: 'Error!'
	        });
    	}
    } else {
    	$("#alertfillupfields").html("Please fill up all the fields.");
    	$("#alertfillupfields").dialog({
            minWidth: 50,
            minHeight: 50,
            title: 'Warning!'
        });
    }
}
function savebulkext() {
    var TableData;
	TableData = storeTblValues()
	TableData = JSON.stringify(TableData);
	$.ajax({
        url: 'superadmin/super-include.php?checkbulkext',
        type: 'POST',
        data: {
            "data": TableData
        },
        success:function(result) {
        	if ( result != "" ) {
        		$("#alertfillupfields").html("Username : "+result+" exist(s). Nothing save. Please try again.");
		    	$("#alertfillupfields").dialog({
		            minWidth: 50,
		            minHeight: 50,
		            title: 'Error!'
		        });
        	} else { 
        		$.ajax({
			        url: 'super.php?savebulkext',
			        type: 'POST',
			        data: {
			            "data": TableData
			        },
			        success: function(resp) {
			        	$("#alertfillupfields").html("Extensions created successfully.");
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
}
function storeTblValues() {
    var TableData = new Array();
	$('#bulkaddextensiontable tr').each(function(row, tr){
	    TableData[row]={
	        "bcid" : $(tr).find('td:eq(0)').text(),
	        "username" :$(tr).find('td:eq(1)').text(),
	        "secret" : $(tr).find('td:eq(2)').text(),
	        "classification" : $(tr).find('td:eq(3)').text()
	    }
	});
    TableData.shift();  // first row will be empty - so remove
    return TableData;
}
function deleteRow(row) {
	if ($("#bulkaddextensiontable tbody tr").length != 1) {
	    var i=row.parentNode.parentNode.rowIndex;
    	document.getElementById('bulkaddextensiontable').deleteRow(i);
	} else {
		$("#bulkaddextensiondivtable").hide();
		$("#bulkaddextensiontable tbody tr:last").remove();
	}
}
</script>