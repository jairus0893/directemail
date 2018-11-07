<script type="text/javascript">
$(document).ready(
	function()
	{
		if ( !($.cookie('tteventslog_id') == undefined) )
		{
			tteventsselected($.cookie('tteventslog_id'));
		}

		if ( !($.cookie('idling_agent') == undefined) )
		{
			idling_agent_logout($.cookie('tteventslog_id'));
		}
	}
);

function idling_agent_logout()
{
	$.removeCookie('idling_agent', {path: '/login'});

	$( "#ttrackerpopupcontainer" ).html("<p>You have been idling for too long.</p><p>Contact your Administrator if you have questions.</p>");

    $( "#ttrackerpopupcontainer" ).dialog({
		title: "AGENT LOGGED OUT!",
		resizable: false,
		width: 350,
		position: { my: "center", at: "center", of: window },
		modal: true,
		draggable: false,
		closeOnEscape: false,
		dialogClass: "no-close",
		buttons: {
	        "Ok": function() {
	          	$( this ).dialog( "close" );
	          	// $( this ).dialog( "destroy" );
				// $('#tttimeoutPauseTimer').countdown('pause');
				// $('#tttimeoutPauseTimer').countdown('destroy');
				_tttimeoutPauseTimer=null;
	          	clicked=0;
	        }
      	}
//		      	,
//				focus: function() {
//					setTimeout( function() {_tttimeoutPauseTimer = $('#tttimeoutPauseTimer').countdown( {until: _timeoutPauseValue+'S', onExpiry: timeoutPauseLogout, significant: 3} );}, 2000 );
//				}

    });

 	// $('#tttimeoutPauseTimer').countdown( {until: (_idle_max_seconds-1)+'S', onExpiry: idling_agent_logout_close, significant: 3} );
}

function idling_agent_logout_close()
{
	// alert("You have Idled Too Long. Goodbye!");

	// $('#tttimepauseTimer').countdown('pause'); 
	$('#tttimeoutPauseTimer').countdown('destroy');
	// _tttimeoutPauseTimer=null; 
	$( "#ttrackerpopupcontainer" ).dialog( "close" ); 
	// $( "#ttrackerpopupcontainer" ).dialog( "destroy" ); 
}

function tteventsselected(_tteventslog_id)
{
	// alert(item.id.replace("ttevents_id",""));

    $.ajax({
        url: "timetracker/tteventspulldown-ajax.php?act=tteventsloggetid&tteventslog_id="+_tteventslog_id,
        success: function(resp)
        {        	
			$.removeCookie('tteventslog_id', {path: '/login'});

			$( "#ttrackerpopupcontainer" ).html(" <div id='ttbreakTimer' style='width: 240px; height: 45px;'></div>"+"<br><p align='center'><b>" +resp.agent_name +"<br>" +"("+resp.break+")" +"</b></p> " );
		    $( "#ttrackerpopupcontainer" ).dialog({
				title: "AGENT ON BREAK",
				resizable: false,
				width: 270,
				position: { my: "center", at: "center", of: window },
				modal: true,
				draggable: false,
				closeOnEscape: false,
				dialogClass: "no-close",
				buttons: {
		        "End Break": function() {
    				$.ajax({
						url: "timetracker/tteventspulldown-ajax.php?act=ttendbreak&tteventslog_id="+_tteventslog_id,
						success: function(resp)
						{
							// alert(resp);
						}
    				});

					$('#ttbreakTimer').countdown('destroy');
		          	$( this ).dialog( "close" );
		        }
		      }
		    });
		 $('#ttbreakTimer').countdown({since: -resp.ts_offset+'S' });
        },
    	dateType: 'json'
    });
}
</script>