<?php
    $projectid = $pid ? $pid:$p;
    $ttrackeroptres = mysql_query("SELECT value FROM tteventsopt WHERE project_id=".$projectid." AND config='isEnabled' ORDER BY id desc LIMIT 1");
    $render_tteventspulldown = false;

	if (mysql_num_rows($ttrackeroptres))
	{
		$ttrackeroptrow = mysql_fetch_row($ttrackeroptres);
		// echo "alert(\"".json_encode($ttrackeroptrow)."\");";

		if ($ttrackeroptrow[0])
			$render_tteventspulldown = true;
	}
	else
		$render_tteventspulldown = false;

	if ($render_tteventspulldown)
	{
		echo "
			navbar.items.get('nottrackerEXIT').hide();
			tteventsmenu = new Ext.menu.Menu({id: 'tteventspulldown'});
		";

		$tteventsres = mysql_query("SELECT id,break,fnttEventActiveGet(id) FROM ttevents WHERE bc_id = 0 AND fnttEventActiveGet(id) >0");
		while ($row = mysql_fetch_row($tteventsres))
			echo "tteventsmenu.add({id: 'ttevents_id".$row[0]."', text: '".$row[1]."', handler: tteventsselected".($row[2]==2 ? ", hidden: true" : "")." });\n";


		$tteventsres = mysql_query("SELECT id,break,fnttEventActiveGet(id) FROM ttevents WHERE bc_id = ".$bcid." AND fnttEventActiveGet(id)>0 ");

		if (mysql_num_rows($tteventsres))
			echo "tteventsmenu.addSeparator();";

		while ($row = mysql_fetch_row($tteventsres))
			echo "tteventsmenu.add({id: 'ttevents_id".$row[0]."', text: '".$row[1]."', handler: tteventsselected".($row[2]==2 ? ", hidden: true" : "")." });\n";

		echo "
			tteventsmenu.addSeparator();
			tteventsmenu.add({text: 'Logout', handler: exittdial });
			navbar.addButton({text: 'EXIT', cls:'x-btn-text-icon', icon: 'icons/cancel.png', menu: tteventsmenu});
		";
?>
    $.ajax({
        url: "timetracker/ajax/ttEventsOptDB.php?act=get&config=timeoutIdle&project_id="+<?=$bcid?>,
        success: function(resp)
        {
			var _idle_max = 60;

		    $.ajax({
		        url: "timetracker/ajax/ttEventsOptDB.php?act=get&config=timeoutIdleMax&project_id="+<?=$bcid?>,
		        success: function(resp)
		        {
		        	_idle_max = resp.timeoutIdleMax;
		    	}
			//$.ajax({ 
			});

			var lastAway = new Date().toTimeString();
			var lastAwayId = 0;
			var idleMaxTimer = 0;

			console.log("Idler Started: "+lastAway);

			var awayCallback = function(){
				lastAway = new Date().toTimeString();
				console.log( lastAway + ": away "+ resp.timeoutIdle + ' seconds');

				timeoutIdle(_idle_max);
				
				// console.log( "exitdial(): "+typeof(exitdial)+", timeoutIdleMax: "+_idle_max);
			    $.ajax({
   					url: "timetracker/ajax/ttEventsDB.php?act=away&"+$.param({ "timeoutIdle": resp.timeoutIdle, "user_id": userid, "project_id": <?=$projectid?>, "bc_id": <?=$bcid?> }),
   					success: function(away_resp)
   					{
   						lastAwayId = away_resp.new_record
   					}	
   				});
				idleMaxTimer = setTimeout(idleMaxLogout, _idle_max * 1000);

			//var awayCallback = function(){
			};

			var awayBackCallback = function(){
				console.log(new Date().toTimeString() + ": back"+" [since]: "+lastAway+" [tteventslog_id]: "+lastAwayId);
				// console.log( "exitdial(): "+typeof(exitdial)+", timeoutIdleMax: "+_idle_max);
			    $.ajax({
   					url: "timetracker/ajax/ttEventsDB.php?act=awayBack&"+$.param({ "tteventslog_id": lastAwayId }),
   					success: function(awayBack_resp)
   					{
   						
   					}	
   				});
				clearTimeout(idleMaxTimer);
				timeoutIdleLogoutCancel();

			//var awayBackCallback = function(){
			};
			
			var idleMaxLogout = function(){
				console.log(new Date().toTimeString() + ": back"+" [since]: "+lastAway+" [tteventslog_id]: "+lastAwayId);
				console.log( "exitdial(): "+typeof(exitdial)+", timeoutIdleMax: "+_idle_max);
				console.log("idleMaxLogout() validNavigation: "+validNavigation);
				validNavigation = true;

			    $.ajax({
   					url: "timetracker/ajax/ttEventsDB.php?act=awayBack&"+$.param({ "tteventslog_id": lastAwayId }),
   					success: function(awayBack_resp)
   					{
   						
   					}	
   				});

   				exitdial();
			//var awayBackCallback = function(){
			};

			var onVisibleCallback = function(){
				console.log(new Date().toTimeString() + ": now looking at page");
			};

			var onHiddenCallback = function(){
				console.log(new Date().toTimeString() + ": not looking at page");
			};
			//this is one way of using it.
			/*
			var idle = new Idle();
			idle.onAway = awayCallback;
			idle.onAwayBack = awayBackCallback;
			idle.setAwayTimeout(2000);
			idle.start();
			*/
			//this is another way of using it
			var idle = new Idle({
				onHidden: onHiddenCallback,
				onVisible: onVisibleCallback,
				onAway: awayCallback,
				onAwayBack: awayBackCallback,
				// awayTimeout: 5000 //away with 5 seconds of inactivity
				awayTimeout: (resp.timeoutIdle * 1000)
			}).start();

        }
    });


<?php
	}
?>
function exittdial()
{
	console.log("tteventsselected() validNavigation: "+validNavigation);
	validNavigation = true;

	exitdial();
}
function tteventsselected(item)
{
	// alert(item.id.replace("ttevents_id",""));

	_ttevents_id = item.id.replace("ttevents_id","");
	
	console.log("tteventsselected() validNavigation: "+validNavigation);
	validNavigation = true;

    $.ajax({
        url: "timetracker/tteventspulldown-ajax.php?act=ttstartbreak&project_id="+projid+"&user_id="+userid+"&ttevents_id="+_ttevents_id,
        success: function(resp)
        {
        	tteventslog_id = resp;
			$.cookie('tteventslog_id', tteventslog_id, {path: '/login'});

			exitdial();
        }
    });

}

/**
 * This javascript file checks for the brower/browser tab action.
 * It is based on the file menstioned by Daniel Melo.
 * Reference: http://stackoverflow.com/questions/1921941/close-kill-the-session-when-the-browser-or-tab-is-closed
 */
// var validNavigation = false;
 
function wireUpEvents() {
  /**
   * For a list of events that triggers onbeforeunload on IE
   * check http://msdn.microsoft.com/en-us/library/ms536907(VS.85).aspx
   *
   * onbeforeunload for IE and chrome
   * check http://stackoverflow.com/questions/1802930/setting-onbeforeunload-on-body-element-in-chrome-and-ie-using-jquery
   */
  var dont_confirm_leave = 0; //set dont_confirm_leave to 1 when you want the user to be able to leave withou confirmation
  var leave_message = 'You sure you want to leave?'

  function goodbye(e) {
	console.log("goodbye() validNavigation: "+validNavigation);
  
    if (!validNavigation) {
    	var exitNow = false;

	    console.log("exitdial()");
	    exittdial();

		if (dont_confirm_leave!==1) {


	        if(!e) e = window.event;
	        //e.cancelBubble is supported by IE - this will kill the bubbling process.
	        e.cancelBubble = true;
	        e.returnValue = leave_message;
	        //e.stopPropagation works in Firefox.
	        if (e.stopPropagation) {
				e.stopPropagation();
				e.preventDefault();
				console.log("Logout: "+Date());
			}

	        //return works for Chrome and Safari
	        return leave_message;
      	}

    }
  }
  window.onbeforeunload=goodbye;
 
  // Attach the event keypress to exclude the F5 refresh
  $(document).bind('keypress', function(e) {
    if (e.keyCode == 116){
      validNavigation = true;
    }
  });
 
  // Attach the event click for all links in the page
  /** $("a").bind("click", function() {
	 console.log("$(a).bind(click) validNavigation: "+validNavigation);
    validNavigation = true;
  });**/
 
  // Attach the event submit for all forms in the page
  $("#campswitcher").bind("submit", function() {
	 console.log("$(form).bind(click) validNavigation: "+validNavigation);
    validNavigation = true;
  });
 
  // Attach the event click for all inputs in the page
  $("input[type=submit]").bind("click", function() {
	 console.log("$(input[type=submit]).bind(click) validNavigation: "+validNavigation);
    validNavigation = true;
  });
 
}
 
// Wire up the events as soon as the DOM tree is ready
$(document).ready(function() {
  wireUpEvents();
});
