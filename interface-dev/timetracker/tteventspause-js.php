<script>
var validNavigation = false;

function timeoutIdle(_idle_max_seconds)
{
	$( "#ttrackerpopupcontainer" ).find('b').html("<br>(Resume or You will be logged out because of idling too long!)");

    $( "#ttrackerpopupcontainer" ).dialog({
		title: "AGENT IS IDLE",
		resizable: false,
		width: 270,
		position: { my: "center", at: "center", of: window },
		modal: true,
		draggable: false,
		closeOnEscape: false,
		dialogClass: "no-close",
		buttons: {
	        "Resume": function() {
	          	$( this ).dialog( "close" );
	          	// $( this ).dialog( "destroy" );
				// $('#tttimeoutPauseTimer').countdown('pause');
				$('#tttimeoutPauseTimer').countdown('destroy');
				_tttimeoutPauseTimer=null;
	          	clicked=0;
	        }
      	}
//		      	,
//				focus: function() {
//					setTimeout( function() {_tttimeoutPauseTimer = $('#tttimeoutPauseTimer').countdown( {until: _timeoutPauseValue+'S', onExpiry: timeoutPauseLogout, significant: 3} );}, 2000 );
//				}

    });

 	$('#tttimeoutPauseTimer').countdown( {until: (_idle_max_seconds-1)+'S', onExpiry: timeoutIdleLogout, significant: 3} );
}

function timeoutIdleLogout()
{
	// alert("You have Idled Too Long. Goodbye!");

	// $('#tttimepauseTimer').countdown('pause'); 
	$('#tttimeoutPauseTimer').countdown('destroy');
	// _tttimeoutPauseTimer=null; 
	$( "#ttrackerpopupcontainer" ).dialog( "close" ); 
	// $( "#ttrackerpopupcontainer" ).dialog( "destroy" ); 

	$.cookie('idling_agent', 'logout', {path: '/login'});
}

function timeoutIdleLogoutCancel()
{
	// alert("You have Idled Too Long. Goodbye!");

	// $('#tttimepauseTimer').countdown('pause'); 
	$('#tttimeoutPauseTimer').countdown('destroy');
	// _tttimeoutPauseTimer=null; 
	$( "#ttrackerpopupcontainer" ).dialog( "close" ); 
	// $( "#ttrackerpopupcontainer" ).dialog( "destroy" ); 
}

function timeoutPause()
{
    $.ajax({
        url: "timetracker/ajax/ttEventsOptDB.php?act=get&config=timeoutPause&project_id="+<?=$bcid?>,
        success: function(resp)
        {
            _timeoutPauseValue = resp.timeoutPause;

			// $( "#ttrackerpopupcontainer" ).html(" <div id='tttimeoutPauseTimer' style='width: 240px; height: 45px;'></div>"+"<br><p align='center'><b>" +<?php echo "'".$_COOKIE['alast'].", ".$_COOKIE['afirst']."'"; ?>+ "<br>" +"(Pause)" +"</b></p> " );
		    $( "#ttrackerpopupcontainer" ).dialog({
				title: "AGENT ON PAUSE",
				resizable: false,
				width: 270,
				position: { my: "center", at: "center", of: window },
				modal: true,
				draggable: false,
				closeOnEscape: false,
				dialogClass: "no-close",
				buttons: {
			        "Close": function() {
			          	$( this ).dialog( "close" );
			          	// $( this ).dialog( "destroy" );
						// $('#tttimeoutPauseTimer').countdown('pause');
						$('#tttimeoutPauseTimer').countdown('destroy');
						_tttimeoutPauseTimer=null;
			          	clicked=0;
			          	// toggledial();
			        }
		      	}
//		      	,
//				focus: function() {
//					setTimeout( function() {_tttimeoutPauseTimer = $('#tttimeoutPauseTimer').countdown( {until: _timeoutPauseValue+'S', onExpiry: timeoutPauseLogout, significant: 3} );}, 2000 );
//				}

		    });

		    // if ( typeof(_tttimeoutPauseTimer) == 'undefined' )
			// $( document.getElementById('ttrackerpopupcontainer') ).ready(function(){_tttimeoutPauseTimer = $('#tttimeoutPauseTimer').countdown( {until: _timeoutPauseValue+'S', onExpiry: timeoutPauseLogout, significant: 3} );})
		 	// _tttimeoutPauseTimer = $('#tttimeoutPauseTimer').countdown( {until: _timeoutPauseValue+'S', onExpiry: timeoutPauseLogout, significant: 3} );
		 	$('#tttimeoutPauseTimer').countdown( {until: _timeoutPauseValue+'S', onExpiry: timeoutPauseLogout, significant: 3} );
		 	// else
		 	//	_tttimeoutPauseTimer.countdown( 'option', {'until': _timeoutPauseValue+'S'} );
        }
    });
}

function timeoutPauseLogout()
{
	// alert("You have been logged out!"); 
	// $('#tttimepauseTimer').countdown('pause'); 
	$('#tttimeoutPauseTimer').countdown('destroy');
	// _tttimeoutPauseTimer=null; 
	$( "#ttrackerpopupcontainer" ).dialog( "close" ); 
	// $( "#ttrackerpopupcontainer" ).dialog( "destroy" ); 

    $.ajax({
        url: "timetracker/tteventspulldown-ajax.php?act=ttstartbreak&project_id="+projid+"&user_id="+userid+"&ttevents_id="+1000,
        success: function(resp)
        {
        	tteventslog_id = resp;
			$.cookie('tteventslog_id', tteventslog_id, {path: '/login'});
		  	

			validNavigation = true;
		  	console.log("timeoutPauseLogout() validNavigation: "+validNavigation);

			exitdial();
        }
    });

}
</script>
