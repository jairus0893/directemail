<style>
a {
	color:#069;
	text-decoration:underline;
}
td.tdhover {
	background-color: #00FF66;
	cursor:pointer;
}
td.tdhover_ {
	background-color: #00A3EF;
	cursor:pointer;
}

td.tdhover:hover {
	background-color:#0FC;
}
td.tdhover_:hover {
	background-color:#936;
}
td.tdhover_.ui-selecting {
    background-color:#00A3EF;
  }
td.tdhover_.ui-selected {
    background-color:#00A3EF;
  }
td.tdhover.ui-selecting {
    background-color:#0FC;
  }
td.tdhover.ui-selected {
    background-color:#0FC;
  }.container {	   overflow: auto;   }.left {		float:left;	}.right {		float:right;}​​
</style>
<script>
	function monthViewCalNavigation(mons) {
		mon = mons;
		var ci = $("#ci").val();
		$.ajax({
			url : "bookingrecurringslot/appbookback-include.php?mon=" + mon + "&ci=" + ci + "&sid=" + Math.random(),
			success : function(data) {
				$('#month_view_calender').html(data);
				$("#caltable").selectable({
					filter : 'td.selectable',
					stop : appselect
				});
			}
		});
	}

	function appclick(dt) {
		console.log('appclick');
		var ci = $("#ci").val();
		$.ajax({
			url : "bookingrecurringslot/appbook-include.php?act=slots&date=" + dt + "&ci=" + ci,
			success : function(data) {
				$('#slots').html(data);
				$("#slots").dialog({
					width : 650,
					height : 300
				});
			}
		});
	}

	function appselect() {
		console.log('appselect');
		var ci = $("#ci").val();
		var dates = '';
		var ct = 0;
		$(".ui-selected").each(function() {
			if (ct > 0)
				dates += ',';
			dates += $(this).attr('id');
			ct = ct + 1;
		});
		if (dates.length > 1) {
			if (dates.indexOf(',') > -1) {
	    		//do nothing
	    	} else {
	    		$.ajax({
					url : "bookingrecurringslot/appbook-include.php?act=slots&date=" + dates + "&ci=" + ci,
					success : function(data) {
						$('#slots').html(data);
						$("#slots").dialog({
							width : 650,
							height : 300
						});
						$(".jbut").button();
					}
				});
	    	}
		}
	}

	function createdateinput_() {
		jQuery('#datetd').show();
		jQuery('#dodisposeapplydate').show();
		$("#accordion").accordion("resize");
	}
	
	function createdateinput_1() {
		jQuery('#bookingcalendardatetd').show();
		jQuery('#dodisposeapplydate').show();
		$("#accordion").accordion("resize");
	}
	
	function applydatebookingcalendar() {
		$("#dodisposeapplydate").hide();
		var leadid = $("#leadidtoapplybookingcalendar").val();
		var slotdatecalendar = $("#slotdatecalendar").val();
		var slotid = $("#slotidfrombookingcalendar").val();
		var slotstart = $("#slotstartfrombookingcalendar").val();
		var slotend = $("#slotendfrombookingcalendar").val();
		var client_contactid = $("#client_contactid_frombookingcalendar").val();
		var clientid = $("#clientid_frombookingcalendar").val();
		var recurring = $("#recurring_frombookingcalendar").val();
		var dispo = $("#disposition").val();
		if (recurring != "") {
			$.ajax({
				url : "bookingrecurringslot/appbook-include.php?act=createtakenslot&leadid=" + leadid + "&calendar=" + slotdatecalendar + "&slotdatecalendar=" + slotdatecalendar + "&slotid=" + slotid + "&slotstart=" + slotstart + "&slotend=" + slotend + "&client_contactid=" + client_contactid + "&clientid=" + clientid + "&recurring=" + recurring,
				success : function(data) {
					$("#slotbooked").html("Slot booked.");
					$("#slotbooked").dialog({
						width : 200,
						height : 200
					});
				}
			});
		} else {
			$.ajax({
				url : "bookingrecurringslot/appbook-include.php?act=updatetakenslot&leadid=" + leadid + "&calendar=" + slotdatecalendar + "&slotdatecalendar=" + slotdatecalendar + "&slotid=" + slotid + "&dispo=" + dispo,
				success : function(data) {
					$("#slotbooked").html("Slot booked.");
					$("#slotbooked").dialog({
						width : 200,
						height : 200
					});
				}
			});
		}
	}
</script>
<div class="dialogclass" id="cslots">
	<div id="month_view_calender"></div>
</div><div id="slots" style="display:none; width:500px;height:300px;"></div><div id="slotbooked" style="display:none; width:200px;height:200px;"></div>