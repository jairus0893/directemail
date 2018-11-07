<style>
	#tabs .btab {
	    float:left;
	    border:#008080 solid 1px;
	    padding: 10px;
	    width: 80px;
	}
	.active {
	    float:left;
	     border-top:#000 solid 1px;
	     border-right:#000 solid 1px;
	     border-left:#000 solid 1px;
	    border-bottom:#FFF solid 1px;
	    background-color: #FFF;
	    padding: 5px;
	    width: 100px;
	    z-index: 1;
	    position:relative;
	    
	}
	.inactive {
	    float:left;
	    border-top:#008080 solid 1px;
	     border-right:#008080 solid 1px;
	     border-left:#008080 solid 1px;
	    border-bottom:#008080 solid 1px;
	    background-color: #008080;
	    padding: 5px;
	    width: 100px;
	    cursor:pointer;
	}
	
	#tabs #boundary {
	    width: 100%;
	    float:left;
	    border: #008080 solid 1px;
	    position:relative;
	    top: -2px;
	    z-index: 0;
	}
	#eclparent {
	  display: flex;
	}
	#eclnarrow {
	  width: 800px;
	}
	#eclwide {
	  flex: 1;
	}
	#gparent {
	  display: flex;
	}
	#gnarrow {
		width: 600px;
		border: 1px solid black;
		padding: 10px;
	}
	#gnarrow1 {
		width: 300px;
		border: 1px solid black;
		padding-left: 100px;
	}
	#gwide {
		width: 350px;
		border: 1px solid black;
		padding: 10px;
	}
	#gcparent {
	  display: flex;
	}
	#gcnarrow {
		width: 350px;
		border: 1px solid black;
		padding: 10px;
	}
	#gcnarrow1 {
		width: 300px;
		border: 1px solid black;
		padding-left: 100px;
	}
	#gcwide {
		width: 600px;
		border: 1px solid black;
		padding: 10px;
	}
	textarea .newvalue {
		resize: both;
	}
	.ecloading {
	    display: none;
	    opacity: 0.8;
	    position: absolute;
	    left: 0px;
	    top: 0px;
	    z-index: 100;
	    height: 100%;
	    width: 100%;
	    overflow: hidden;
	}
	.ecloading img {
		position: absolute;
		top: 0; 
		bottom: 0; 
		left:0; 
		right: 0; 
		margin: auto;
	}
</style>
<script>
	$(document).ready(function() {
		/* Create an array with the values of all the input boxes in a column */
		$.fn.dataTableExt.afnSortData['dom-text'] = function  ( oSettings, iColumn )
		{
			var aData = [];
			$( 'td:eq('+iColumn+') input', oSettings.oApi._fnGetTrNodes(oSettings) ).each( function () {
				aData.push( this.value );
			} );
			return aData;
		}
		
		/* Create an array with the values of all the select options in a column */
		$.fn.dataTableExt.afnSortData['dom-select'] = function  ( oSettings, iColumn )
		{
			var aData = [];
			$( 'td:eq('+iColumn+') select', oSettings.oApi._fnGetTrNodes(oSettings) ).each( function () {
				aData.push( $(this).val() );
			} );
			return aData;
		}
		
		/* Create an array with the values of all the checkboxes in a column */
		$.fn.dataTableExt.afnSortData['dom-checkbox'] = function  ( oSettings, iColumn )
		{
			var aData = [];
			$( 'td:eq('+iColumn+') input', oSettings.oApi._fnGetTrNodes(oSettings) ).each( function () {
				aData.push( this.checked==true ? "1" : "0" );
			} );
			return aData;
		}
		$('.editcampaignliststable').dataTable({
			responsive: false,
	        autoWidth: false,
	        pagingType: "full",
	        jQueryUI: true,
			"aLengthMenu": [ 10, 15, 20, 50],
			"iDisplayLength": 10,
			"aaSorting": [[ 1, "desc" ]]
        });
        $(".editcampaignmultipleliststable").dataTable({
			responsive: false,
	        autoWidth: false,
	        pagingType: "full",
	        jQueryUI: true,
			"aLengthMenu": [ 10, 15, 20, 50],
			"iDisplayLength": 10,
			"aaSorting": [[ 0, "asc" ]]
        });
        $("#addnewrow").click(function () {
		    $("#globaledittableFilters").each(function () {
		        var td = '<tr>';
		        jQuery.each($('tr:last td', this), function () {
		            td += '<td class="dataleft">' + $(this).html() + '</td>';
		        });
		        td += '</tr>';
		        var td1 = td.replace("add.gif", "delete.gif");
		        var tds = td1.replace('id="addnewrow"', 'class="deleteeditrow"');
		        if ($('tbody', this).length > 0) {
		            $('tbody', this).append(tds);
		        } else {
		            $(this).append(tds);
		        }
		    });
		});
        $("#addnewroweditcampaign").click(function () {
		    $("#globaleditcampaigntableFilters").each(function () {
		        var td = '<tr>';
		        jQuery.each($('tr:last td', this), function () {
		            td += '<td class="dataleft">' + $(this).html() + '</td>';
		        });
		        td += '</tr>';
		        var td1 = td.replace("add.gif", "delete.gif");
		        var tds = td1.replace('id="addnewroweditcampaign"', 'class="deleteeditrow"');
		        if ($('tbody', this).length > 0) {
		            $('tbody', this).append(tds);
		        } else {
		            $(this).append(tds);
		        }
		    });
		});
		$(document).on("click",'.deleteeditrow',function(){
			$(this).closest('tr').remove(); 
		});
		$('#searcheditlistsbtn').click(function() {   
	        if( $('#applyeditlistfields').prop('disabled')) {
	        	$('#applyeditlistfields').prop('disabled',false);
	        }
	    });   
	    $('#searcheditcampaignlistsbtn').click(function() {   
	        if( $('#applyeditcampaignlistfields').prop('disabled')) {
	        	$('#applyeditcampaignlistfields').prop('disabled',false);
	        }
	    });
		$('#searcheditingbtn').click(function() {
			$('#searcheditingdiv').show();
			$('#columneditingdiv').hide();
			$("#submiteditlistsbtn").show();
			$("#columnsubmiteditlistsbtn").hide();
			$("#leadsglobaleditresult").show();
		});
		$('#columneditingbtn').click(function() {
			$('#searcheditingdiv').hide();
			$('#columneditingdiv').show();
			$("#submiteditlistsbtn").hide();
			$("#columnsubmiteditlistsbtn").show();
			$("#leadsglobaleditresult").hide();
		});
		$('#globalsearcheditingbtn').click(function() {
			$('#globalsearcheditingdiv').show();
			$('#globalcolumneditingdiv').hide();
			$("#submiteditcampaignlistsbtn").show();
			$("#columnsubmiteditcampaignlistsbtn").hide();
			$("#leadsglobaleditcampaignresult").show();
		});
		$('#globalcolumneditingbtn').click(function() {
			$('#globalsearcheditingdiv').hide();
			$('#globalcolumneditingdiv').show();
			$("#submiteditcampaignlistsbtn").hide();
			$("#columnsubmiteditcampaignlistsbtn").show();
			$("#leadsglobaleditcampaignresult").hide();
		});
	});
	function confirmdialog(message, lid) {
    	$('<div></div>').appendTo('body')
        	.html('<div><h6><p style="font-size: 1.4em">'+message+'</p></h6></div>')
        	.dialog({
	            modal: true, 
	            title: 'WARNING', 
	            zIndex: 10000, 
	            autoOpen: true,
	            width: 'auto', resizable: false,
	            buttons: {
	                "Confirm": function () {
	                	$(this).dialog("close");
			            submiteditlists(lid);
			        },
			        "Cancel": function () {                                                                 
			            $(this).dialog("close");
			        }
		        },
		        close: function (event, ui) {
		        	$(this).remove();
		    	}
    		});
	}
	function columnconfirmdialog(message, lid) {
    	$('<div></div>').appendTo('body')
        	.html('<div><h6><p style="font-size: 1.4em">'+message+'</p></h6></div>')
        	.dialog({
	            modal: true, 
	            title: 'WARNING', 
	            zIndex: 10000, 
	            autoOpen: true,
	            width: 'auto', resizable: false,
	            buttons: {
	                "Confirm": function () {
	                	$(this).dialog("close");
			            columnsubmiteditlists(lid);
			        },
			        "Cancel": function () {                                                                 
			            $(this).dialog("close");
			        }
		        },
		        close: function (event, ui) {
		        	$(this).remove();
		    	}
    		});
	}
	function globalconfirmdialog(message) {
    	$('<div></div>').appendTo('body')
        	.html('<div><h6><p style="font-size: 1.4em">'+message+'?</p></h6></div>')
        	.dialog({
	            modal: true, 
	            title: 'WARNING', 
	            zIndex: 10000, 
	            autoOpen: true,
	            width: 'auto', resizable: false,
	            buttons: {
	                "Confirm": function () {
	                	$(this).dialog("close");
			            submitteditcampaignlists();
			        },
			        "Cancel": function () {                                                                 
			            $(this).dialog("close");
			        }
		        },
		        close: function (event, ui) {
		        	$(this).remove();
		    	}
    		});
	}
	function columnglobalconfirmdialog(message) {
    	$('<div></div>').appendTo('body')
        	.html('<div><h6><p style="font-size: 1.4em">'+message+'?</p></h6></div>')
        	.dialog({
	            modal: true, 
	            title: 'WARNING', 
	            zIndex: 10000, 
	            autoOpen: true,
	            width: 'auto', resizable: false,
	            buttons: {
	                "Confirm": function () {
	                	$(this).dialog("close");
			            columnsubmitteditcampaignlists();
			        },
			        "Cancel": function () {                                                                 
			            $(this).dialog("close");
			        }
		        },
		        close: function (event, ui) {
		        	$(this).remove();
		    	}
    		});
	}
	function listsviewtab(tab,i)
	{
	    $("#tabcont div.tabc").each(function(){
	        $(this).hide();
	        
	    });
	    $("#tabs div.active").each(function(){
	        $(this).attr("class","inactive");
	        
	    });
	    $("#"+tab+"tab").attr("class","active");
	    listsloadtab(tab,i);
	    $("#tab"+tab).show();
	}
	function listsloadtab(tab,i)
	{
	    if (tab == 'inline'){
	    	$(".ecloading").show();
		    $.ajax({
		    url: "editcampaignlists/editlists.php?act=editlists&sub=inlineedit&lid="+i,
		    success: function(resp){
		    		$(".ecloading").hide();
		            $("#tabinline").html(resp);
		            $(".jbut").button();
	            }
		    });
	    
	    }
	    if (tab == 'global'){
	    	$(".ecloading").show();
		    $.ajax({
		    url: "editcampaignlists/editlists.php?act=editlists&sub=globaledit&lid="+i,
		    success: function(resp){
		    		$(".ecloading").hide();
		            $("#tabglobal").html(resp);
		            $(".jbut").button();
	            }
		    });
	    }
	    if (tab == 'inlinecampaign'){
	    	$(".ecloading").show();
		    $.ajax({
		    url: "editcampaignlists/editcampaignlists.php?act=selectcampaignlists&act1=editcampaignlists&sub=inlineeditcampaign&bcid="+i,
		    success: function(resp){
		    		$(".ecloading").hide();
		            $("#tabinlinecampaign").html(resp);
		            $(".jbut").button();
		            selectcampaignlists();
	            }
		    });
	    
	    }
	    if (tab == 'globalcampaign'){
	    	$(".ecloading").show();
		    $.ajax({
		    url: "editcampaignlists/editcampaignlists.php?act=selectcampaignlists&act1=editcampaignlists&sub=globaleditcampaign&bcid="+i,
		    success: function(resp){
		    		$(".ecloading").hide();
		            $("#tabglobalcampaign").html(resp);
		            $(".jbut").button();
	            }
		    });
	    }
	}
	function triggerchange() {
	    tchanged = true;
	}
	var tchanged = false;
	function editlead(it, leadid, field) {
	    if (tchanged == true) {
	        var field = field;
	        var value = it.value;
	        if (field.indexOf("*") >= 0) {
	        	var cdata = '{';
		        var i = 0;
		        $(".editcampaignliststable input[class=editlistsleadcf_"+leadid+"]").each(function(){
		            if (i > 0) {
	                    cdata +=',';
	                }
		            var nm = $(this).attr("name");
		            var vl = $(this).val();
		            cdata +='"'+nm+'":"'+vl+'"';
		            i++;
		        });
		        cdata += '}';
		        $.ajax({
		            url: "editcampaignlists/editlists.php?act=editleadcf&cdata="+cdata+"&leadid="+leadid,
		            type: 'POST',
		            global: false
		        });
	        } else {
	        	$.ajax({
		            url: "editcampaignlists/editlists.php?act=editlead&field="+field+"&value="+value+"&leadid="+leadid,
		            type: 'POST',
		            global: false
		        });
	        }
	        tchanged = false;
	    }
	}
	function campaignlistchange() {
		var pel = $("#projectideditlist").val();
		$.ajax({
		url: "editcampaignlists/phpfunctions-include.php?act=updatecampaignlist&projectid="+pel,
		success: function(data){
 			$("#editlistids").html(data);
		}
		});
	}
	function selectcampaignlists() {
		$(".ecloading").show();
		var projid = $("#projectideditlist").val();
		var listids = $("#editlistids").val();
	    $.ajax({
		    url:'editcampaignlists/editcampaignlists.php?act=selectcampaignlists&act1=editcampaignlistsinline&projid='+projid+'&listids='+listids,
		    type: 'POST',
		    success: function(resp){
		        $("#campaignlistids").val(listids);
		        $(".leadseditcampaignresult").html(resp);
		        $(".jbut").button();
		        $(".ecloading").hide();
		    }
		});
	} 
	function editcampaignlead(it, leadid, field) {
	    if (tchanged == true) {
	        var field = field;
	        var value = it.value;
	        if (field.indexOf("*") >= 0) {
	        	var cdata = '{';
		        var i = 0;
		        $(".editcampaignmultipleliststable input[class=editcampaignlistsleadcf_"+leadid+"]").each(function(){
		            if (i > 0) {
	                    cdata +=',';
	                }
		            var nm = $(this).attr("name");
		            var vl = $(this).val();
		            cdata +='"'+nm+'":"'+vl+'"';
		            i++;
		        });
		        cdata += '}';
		        $.ajax({
		            url: "editcampaignlists/editcampaignlists.php?act=editcampaignlistsleadcf&cdata="+cdata+"&leadid="+leadid,
		            type: 'POST',
		            global: false
		        });
	        } else {
	        	$.ajax({
		            url: "editcampaignlists/editcampaignlists.php?act=editlead&field="+field+"&value="+value+"&leadid="+leadid,
		            type: 'POST',
		            global: false
		        });
	        }
	        tchanged = false;
	    }
	}
	// SEARCH
	function searcheditlists(lid) {
		$(".ecloading").show();
		var TableData;
		TableData = editListStoreTblValues();
		TableData = JSON.stringify(TableData);
		$.ajax({
		    url:'editcampaignlists/editlists.php?act=editlists',
		    type: 'POST',
		    data: {
		    	"act1": "columnsearch",
	            "data": TableData,
	            "lid" : lid
	        },
		    success: function(resp){
		    	$("#leadsglobaleditresult").html(resp);
		        $(".jbut").button();
		        $(".ecloading").hide();
		        $("#columnsubmiteditlistsbtn").hide();
		    }
		});
	}
	function columnsearcheditlists(lid) {
		$(".ecloading").show();
		$.ajax({
		    url:'editcampaignlists/editlists.php?act=editlists',
		    type: 'POST',
		    data: {
		    	"act1": "columnedit",
	            "lid" : lid
	        },
		    success: function(resp){
		    	$("#leadsglobaleditresult").html(resp);
		        $(".jbut").button();
		        $(".ecloading").hide();
		        $("#submiteditlistsbtn").hide();
		        $("#columnsubmiteditlistsbtn").show();
		    }
		});
	}
	// SEARCH
	function editListStoreTblValues() {
	    var TableData = new Array();
	    $('#globaledittableFilters tr').each(function(row, tr){
	        TableData[row] = {
	            "fields" : $(tr).find('td:eq(0) select[name="searcheditlistfields"]').val(),
	            "operand" :$(tr).find('td:eq(1) select[name="operand"]').val(),
	            "value" : $(tr).find('td:eq(2) input[name="vaedit"]').val()
	        }
	    });
	    return TableData;
	}
	// SEARCH
	function changeoperationeditlist() {
		var op = $("#applyeditlistfields").val();
		if (op == "") {
			$("#swapvalueeditlist").hide();
			$("#newvalueeditlist").hide();
			$("#replacevalueeditlist").hide();
		} else if (op == "swap" || op == "move" || op == "copy") {
			if (op == "swap") {
				$(".expnotif2").text("(Swap values of two column)");
			} else if (op == "move") {
				$(".expnotif2").text("(Move values to another column)");
			} else if (op == "copy") {
				$(".expnotif2").text("(Copy values to another column)");
			}
			$("#swapvalueeditlist").show();
			$("#newvalueeditlist").hide();
			$("#replacevalueeditlist").hide();
		} else if (op == "replaceall") {
			$(".expnotif1").text("(Replace with new value)");
			$("#newvalueeditlist").show();
			$("#swapvalueeditlist").hide();
			$("#replacevalueeditlist").hide();
		} else if (op == "selectivereplace") {
			$(".expnotif1").text("(Replace the selective string with new value)");
			$("#newvalueeditlist").show();
			$("#swapvalueeditlist").hide();
			$("#replacevalueeditlist").show();
		} else {
			if (op == "append") {
				$(".expnotif1").text("(Add value at the end of text)");
			} else if (op == "prepend") {
				$(".expnotif1").text("(Add at the beginning of text)");
			}
			$("#newvalueeditlist").show();
			$("#swapvalueeditlist").hide();
			$("#replacevalueeditlist").hide();
		}
	}
	function changecolumnoperationeditlist() {
		var op = $("#columnapplyeditlistfields").val();
		if (op == "") {
			$("#swapvalueeditlist").hide();
			$("#newvalueeditlist").hide();
			$("#replacevalueeditlist").hide();
		} else if (op == "swap" || op == "move" || op == "copy") {
			if (op == "swap") {
				$(".expnotif4").text("(Swap values of two column)");
			} else if (op == "move") {
				$(".expnotif4").text("(Move values to another column)");
			} else if (op == "copy") {
				$(".expnotif4").text("(Copy values to another column)");
			}
			$("#columnswapvalueeditlist").show();
			$("#columnnewvalueeditlist").hide();
			$("#columnreplacevalueeditlist").hide();
			$("#columnsubmiteditlistsbtn").show();
		} else if (op == "replaceall") {
			$(".expnotif3").text("(Replace text with new value)");
			$("#columnnewvalueeditlist").show();
			$("#columnswapvalueeditlist").hide();
			$("#columnreplacevalueeditlist").hide();
			$("#columnsubmiteditlistsbtn").show();
		} else if (op == "selectivereplace") {
			$(".expnotif3").text("(Replace the selective string with new value)");
			$("#columnnewvalueeditlist").show();
			$("#columnswapvalueeditlist").hide();
			$("#columnreplacevalueeditlist").show();
			$("#columnsubmiteditlistsbtn").show();
		} else {
			if (op == "append") {
				$(".expnotif3").text("(Add value at the end of text)");
			} else if (op == "prepend") {
				$(".expnotif3").text("(Add at the beginning of text)");
			}
			$("#columnnewvalueeditlist").show();
			$("#columnswapvalueeditlist").hide();
			$("#columnreplacevalueeditlist").hide();
			$("#columnsubmiteditlistsbtn").show();
		}
	}
	// SEARCH
	function confirmeditlists(lid) {
		var TableData;
		TableData = editListStoreTblValues();
		TableData = JSON.stringify(TableData);
		var leadids = $('input[name="editlistsleadid[]"').map(function(){
			return this.value
		}).get()
		var op = $("#applyeditlistfields").val();
		if (leadids == "") {
			alert("Search first before committing.");
		} else if (op == "") {
			alert("Please select the operation.");
		} else if (op == "swap" || op == "move" || op == "copy") {
			var from = $("#fromeditlistfields").val();
			var to = $("#toeditlistfields").val();
			if (from != "" && to != "") {
				confirmdialog("Are you sure you want to commit the changes?", lid);
			} else {
				alert("Please fill the fields.");
			}
		} else {
			var replacevalue = $("#replacevalue").val();
			var applytovalue = $("#applytovalue").val();
			if (applytovalue != "") {
				confirmdialog("Are you sure you want to commit the changes?", lid);
			} else {
				alert("Please fill the fields.");
			}
		}
	}
	function columnconfirmeditlists(lid) {
		var op = $("#columnapplyeditlistfields").val();
		if (op == "") {
			alert("Please select the operation.");
		} else if (op == "swap" || op == "move" || op == "copy") {
			var from = $("#columnfromeditlistfields").val();
			var to = $("#columntoeditlistfields").val();
			if (from != "" && to != "") {
				columnconfirmdialog("Changes will reflect in all records.<br/>Are you sure you want to commit the changes?", lid);
			} else {
				alert("Please fill the fields.");
			}
		} else {
			var replacevalue = $("#columnreplacevalue").val();
			var applytovalue = $("#columnapplytovalue").val();
			if (applytovalue != "") {
				columnconfirmdialog("Changes will reflect in all records.<br/>Are you sure you want to commit the changes?", lid);
			} else {
				alert("Please fill the fields.");
			}
		}
	}
	// SEARCH
	function submiteditlists(lid) {
		$(".ecloading").show();
		var TableData;
		TableData = editListStoreTblValues();
		TableData = JSON.stringify(TableData);
		var op = $("#applyeditlistfields").val();
		if (op == "swap" || op == "move" || op == "copy") {
			var from = $("#fromeditlistfields").val();
			var to = $("#toeditlistfields").val();
			$.ajax({
			    url:'editcampaignlists/editlists.php?act=submiteditlists',
			    type: 'POST',
			    data: {
			    	"lid": lid,
			    	"data": TableData,
			    	"operation": op,
		            "from": from,
		            "to" : to
		        },
			    success: function(resp){
			    	$(".ecloading").hide();
			    	searcheditlists(lid);
			        alert(resp);
			    }
			});
		} else {
			var replacevalue = $("#replacevalue").val();
			var newvalue = $("#newvalue").val();
			var applytovalue = $("#applytovalue").val();
			$.ajax({
			    url:'editcampaignlists/editlists.php?act=submiteditlists',
			    type: 'POST',
			    data: {
			    	"lid": lid,
			    	"data": TableData,
			    	"operation": op,
			    	"replacevalue": replacevalue,
		            "newvalue": newvalue,
		            "applytovalue" : applytovalue
		        },
			    success: function(resp){
			    	$(".ecloading").hide();
			    	searcheditlists(lid);
			        alert(resp);
			    }
			});
		}
	}
	function columnsubmiteditlists(lid) {
		$(".ecloading").show();
		var op = $("#columnapplyeditlistfields").val();
		if (op == "swap" || op == "move" || op == "copy") {
			var from = $("#columnfromeditlistfields").val();
			var to = $("#columntoeditlistfields").val();
			$.ajax({
			    url:'editcampaignlists/editlists.php?act=columnsubmiteditlists',
			    type: 'POST',
			    data: {
			    	"lid": lid,
			    	"operation": op,
		            "from": from,
		            "to" : to
		        },
			    success: function(resp){
			    	$(".ecloading").hide();
			    	columnsearcheditlists(lid);
			        alert(resp);
			    }
			});
		} else {
			var replacevalue = $("#columnreplacevalue").val();
			var newvalue = $("#columnnewvalue").val();
			var applytovalue = $("#columnapplytovalue").val();
			$.ajax({
			    url:'editcampaignlists/editlists.php?act=columnsubmiteditlists',
			    type: 'POST',
			    data: {
			    	"lid": lid,
			    	"operation": op,
			    	"replacevalue": replacevalue,
		            "newvalue": newvalue,
		            "applytovalue" : applytovalue
		        },
			    success: function(resp){
			    	$(".ecloading").hide();
			    	columnsearcheditlists(lid);
			        alert(resp);
			    }
			});
		}
	}
	// BULK
	function searcheditcampaignlists() {
		$(".ecloading").show();
		var lid = $("#campaignlistids").val();
		var projid = $("#projectideditlist").val();
		var TableData;
		TableData = editCampaignListStoreTblValues();
		TableData = JSON.stringify(TableData);
		$.ajax({
		    url:'editcampaignlists/editcampaignlists.php?act=selectcampaignlists&act1=editcampaignlistsinline',
		    type: 'POST',
		    data: {
		    	"act1": "columnsearch",
	            "data": TableData,
	            "listids" : lid,
	            "projid" : projid
	        },
		    success: function(resp){
		    	$("#leadsglobaleditcampaignresult").html(resp);
		        $(".jbut").button();
		        $(".ecloading").hide();
		        $("#submiteditcampaignlistsbtn").show();
		    }
		});
	}
	function columnsearcheditcampaignlists() {
		$(".ecloading").show();
		var lid = $("#campaignlistids").val();
		var projid = $("#projectideditlist").val();
		$.ajax({
		    url:'editcampaignlists/editcampaignlists.php?act=selectcampaignlists&act1=editcampaignlistsinline',
		    type: 'POST',
		    data: {
		    	"act1": "columnsearch",
	            "listids" : lid,
	            "projid" : projid
	        },
		    success: function(resp){
		    	$("#leadsglobaleditcampaignresult").html(resp);
		        $(".jbut").button();
		        $(".ecloading").hide();
		        $("#submiteditcampaignlistsbtn").hide();
		        $("#columnsubmiteditcampaignlistsbtn").hide();
		    }
		});
	}
	// BULK
	function editCampaignListStoreTblValues() {
	    var TableData = new Array();
	    $('#globaleditcampaigntableFilters tr').each(function(row, tr){
	        TableData[row] = {
	            "fields" : $(tr).find('td:eq(0) select[name="searcheditcampaignlistfields"]').val(),
	            "operand" :$(tr).find('td:eq(1) select[name="operandcampaign"]').val(),
	            "value" : $(tr).find('td:eq(2) input[name="vaeditcampaign"]').val()
	        }
	    });
	    return TableData;
	}
	// BULK
	function changeoperationeditcampaignlist() {
		var op = $("#applyeditcampaignlistfields").val();
		if (op == "") {
			$("#swapvalueeditcampaignlist").hide();
			$("#newvalueeditcampaignlist").hide();
			$("#replacevalueeditcampaignlist").hide();
		} else if (op == "swap" || op == "move" || op == "copy") {
			if (op == "swap") {
				$(".expnotif4").text("(Swap values of two column)");
			} else if (op == "move") {
				$(".expnotif4").text("(Move values to another column)");
			} else if (op == "copy") {
				$(".expnotif4").text("(Copy values to another column)");
			}
			$("#swapvalueeditcampaignlist").show();
			$("#newvalueeditcampaignlist").hide();
			$("#replacevalueeditcampaignlist").hide();
		} else if (op == "replaceall") {
			$(".expnotif3").text("(Replace text with new value)");
			$("#newvalueeditcampaignlist").show();
			$("#swapvalueeditcampaignlist").hide();
			$("#replacevalueeditcampaignlist").hide();
		} else if (op == "selectivereplace") {
			$(".expnotif3").text("(Replace the selective string with new value)");
			$("#newvalueeditcampaignlist").show();
			$("#swapvalueeditcampaignlist").hide();
			$("#replacevalueeditcampaignlist").show();
		} else {
			if (op == "append") {
				$(".expnotif3").text("(Add at the end)");
			} else if (op == "prepend") {
				$(".expnotif3").text("(Add at the beginning)");
			}
			$("#newvalueeditcampaignlist").show();
			$("#swapvalueeditcampaignlist").hide();
			$("#replacevalueeditcampaignlist").hide();
		}
	}
	function columnchangeoperationeditcampaignlist() {
		var op = $("#columnapplyeditcampaignlistfields").val();
		if (op == "") {
			$("#columnswapvalueeditcampaignlist").hide();
			$("#columnnewvalueeditcampaignlist").hide();
			$("#columnreplacevalueeditcampaignlist").hide();
		} else if (op == "swap" || op == "move" || op == "copy") {
			if (op == "swap") {
				$(".expnotif6").text("(Swap values of two column)");
			} else if (op == "move") {
				$(".expnotif6").text("(Move values to another column)");
			} else if (op == "copy") {
				$(".expnotif6").text("(Copy values to another column)");
			}
			$("#columnswapvalueeditcampaignlist").show();
			$("#columnnewvalueeditcampaignlist").hide();
			$("#columnreplacevalueeditcampaignlist").hide();
			$("#columnsubmiteditcampaignlistsbtn").show();
		} else if (op == "replaceall") {
			$(".expnotif5").text("(Replace text with new value)");
			$("#columnnewvalueeditcampaignlist").show();
			$("#columnswapvalueeditcampaignlist").hide();
			$("#columnreplacevalueeditcampaignlist").hide();
			$("#columnsubmiteditcampaignlistsbtn").show();
		} else {
			if (op == "append") {
				$(".expnotif5").text("(Add value at the end of text)");
			} else if (op == "prepend") {
				$(".expnotif5").text("(Add value at the beginning of text)");
			}
			$("#columnnewvalueeditcampaignlist").show();
			$("#columnswapvalueeditcampaignlist").hide();
			$("#columnreplacevalueeditcampaignlist").hide();
		}
	}
	// BULK
	function confirmeditcampaignlists() {
		var TableData;
		TableData = editListStoreTblValues();
		TableData = JSON.stringify(TableData);
		var leadids = $('input[name="editcampaignlistsleadid[]"').map(function(){
			return this.value
		}).get()
		var op = $("#applyeditcampaignlistfields").val();
		if (op == "") {
			alert("Please select the operation.");
		} else if (op == "swap" || op == "move" || op == "copy") {
			var from = $("#fromeditcampaignlistfields").val();
			var to = $("#toeditcampaignlistfields").val();
			if (from != "" && to != "") {
				globalconfirmdialog("Are you sure you want to commit the changes?");
			} else {
				alert("Please fill the fields.");
			}
		} else {
			var replacevalue = $("#replacevaluecampaign").val();
			var newvalue = $("#newvaluecampaign").val();
			var applytovalue = $("#applytovalueglobal").val();
			if (applytovalue != "") {
				globalconfirmdialog("Are you sure you want to commit the changes?");
			} else {
				alert("Please fill the fields.");
			}
		}
	}
	function columnconfirmeditcampaignlists() {
		var op = $("#columnapplyeditcampaignlistfields").val();
		if (op == "") {
			alert("Please select the operation.");
		} else if (op == "swap" || op == "move" || op == "copy") {
			var from = $("#columnfromeditcampaignlistfields").val();
			var to = $("#columntoeditcampaignlistfields").val();
			if (from != "" && to != "") {
				columnglobalconfirmdialog("Changes will reflect in all records.<br/>Are you sure you want to commit the changes?");
			} else {
				alert("Please fill the fields.");
			}
		} else {
			var replacevalue = $("#columnreplacevaluecampaign").val();
			var newvalue = $("#columnnewvaluecampaign").val();
			var applytovalue = $("#columnapplytovalueglobal").val();
			if (applytovalue != "") {
				columnglobalconfirmdialog("Changes will reflect in all records.<br/>Are you sure you want to commit the changes?");
			} else {
				alert("Please fill the fields.");
			}
		}
	}
	// BULK
	function submitteditcampaignlists() {
		$(".ecloading").show();
		var lid = $("#campaignlistids").val();
		var projid = $("#projectideditlist").val();
		var TableData;
		TableData = editListStoreTblValues();
		TableData = JSON.stringify(TableData);
		var op = $("#applyeditcampaignlistfields").val();
		var leadids = $('input[name="editcampaignlistsleadid[]"').map(function(){
			return this.value
		}).get()
		if (op == "swap" || op == "move" || op == "copy") {
			var from = $("#fromeditcampaignlistfields").val();
			var to = $("#toeditcampaignlistfields").val();
			$.ajax({
			    url:'editcampaignlists/editcampaignlists.php?act=submitteditcampaignlists',
			    type: 'POST',
			    data: {
			    	"lid" : lid,
	    			"projid" : projid,
			    	"data": TableData,
			    	"operation": op,
		            "from": from,
		            "to" : to,
		            "leadids" : leadids
		        },
			    success: function(resp){
			    	$(".ecloading").hide();
			    	searcheditcampaignlists();
			        alert(resp);
			    }
			});
		} else {
			var replacevaluecampaign = $("#replacevaluecampaign").val();
			var newvaluecampaign = $("#newvaluecampaign").val();
			var applytovalueglobal = $("#applytovalueglobal").val();
			$.ajax({
			    url:'editcampaignlists/editcampaignlists.php?act=submitteditcampaignlists',
			    type: 'POST',
			    data: {
			    	"lid" : lid,
        			"projid" : projid,
			    	"data": TableData,
			    	"operation": op,
			    	"replacevalue": replacevaluecampaign,
		            "newvalue": newvaluecampaign,
		            "applytovalue" : applytovalueglobal,
		            "leadids" : leadids
		        },
			    success: function(resp){
			    	$(".ecloading").hide();
			    	searcheditcampaignlists();
			        alert(resp);
			    }
			});
		}
	}
	function columnsubmitteditcampaignlists() {
		$(".ecloading").show();
		var lid = $("#campaignlistids").val();
		var projid = $("#projectideditlist").val();
		var op = $("#columnapplyeditcampaignlistfields").val();
		if (op == "swap" || op == "move" || op == "copy") {
			var from = $("#columnfromeditcampaignlistfields").val();
			var to = $("#columntoeditcampaignlistfields").val();
			$.ajax({
			    url:'editcampaignlists/editcampaignlists.php?act=columnsubmitteditcampaignlists',
			    type: 'POST',
			    data: {
			    	"lid" : lid,
	    			"projid" : projid,
			    	"operation": op,
		            "from": from,
		            "to" : to
		        },
			    success: function(resp){
			    	$(".ecloading").hide();
			    	//columnsearcheditcampaignlists();
			        alert(resp);
			    }
			});
		} else {
			var replacevaluecampaign = $("#columnreplacevaluecampaign").val();
			var newvaluecampaign = $("#columnnewvaluecampaign").val();
			var applytovalueglobal = $("#columnapplytovalueglobal").val();
			$.ajax({
			    url:'editcampaignlists/editcampaignlists.php?act=columnsubmitteditcampaignlists',
			    type: 'POST',
			    data: {
			    	"lid" : lid,
        			"projid" : projid,
			    	"operation": op,
			    	"replacevalue": replacevaluecampaign,
		            "newvalue": newvaluecampaign,
		            "applytovalue" : applytovalueglobal
		        },
			    success: function(resp){
			    	$(".ecloading").hide();
			    	//columnsearcheditcampaignlists();
			        alert(resp);
			    }
			});
		}
	}
	function checkdefaultcustomfields() {
		var from = $("#fromeditlistfields").val();
		var to = $("#toeditlistfields").val();
		if (from != "" && to != "") {
			if (from.indexOf("cf|") == -1 && to.indexOf("cf|") == 0) {
				alert("Editing is currently not allowed between default and custom field. This can only work between default-default and custom-custom field. Please select another field.");
				$("#fromeditlistfields").val("");
				$("#toeditlistfields").val("");
			} else if (from.indexOf("cf|") == 0 && to.indexOf("cf|") == -1) {
				alert("Editing is currently not allowed between default and custom field. This can only work between default-default and custom-custom field. Please select another field.");
				$("#fromeditlistfields").val("");
				$("#toeditlistfields").val("");
			} else {
				// nothing
			}
		}
	}
	function columncheckdefaultcustomfields() {
		var from = $("#columnfromeditlistfields").val();
		var to = $("#columntoeditlistfields").val();
		if (from != "" && to != "") {
			if (from.indexOf("cf|") == -1 && to.indexOf("cf|") == 0) {
				alert("Editing is currently not allowed between default and custom field. This can only work between default-default and custom-custom field. Please select another field.");
				$("#columnfromeditlistfields").val("");
				$("#columntoeditlistfields").val("");
			} else if (from.indexOf("cf|") == 0 && to.indexOf("cf|") == -1) {
				alert("Editing is currently not allowed between default and custom field. This can only work between default-default and custom-custom field. Please select another field.");
				$("#columnfromeditlistfields").val("");
				$("#columntoeditlistfields").val("");
			} else {
				// nothing
			}
		}
	}
</script>