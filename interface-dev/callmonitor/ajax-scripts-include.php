<?php
	// BEGIN: ajax-scripts-include.php
?>
function checkdialstate()
{
    if (dialmode == 'predictive' || dialmode == 'blended' || dialmode == 'inbound' || dialmode == 'progressive')
	{
		if (typeof($jqxhr_endcallmonitor) == 'undefined')
		{
	        endcallmonitor($("#phone").val(), userid, $("#leadid").val(), $("#switchprojectid").val());   
		}
		else if ($jqxhr_endcallmonitor.readyState == 4)
		{
	        endcallmonitor($("#phone").val(), userid, $("#leadid").val(), $("#switchprojectid").val());   
		}
		else if ($jqxhr_endcallmonitor.readyState == 0)
		{
			if (astatus == 'incall' || astatus == 'dialing' || astatus == 'inboundcall' || astatus == 'oncall' || astatus == 'conference') 
			{
	        	endcallmonitor($("#phone").val(), userid, $("#leadid").val(), $("#switchprojectid").val());
	        }
	        else
	        {
	        	delete $jqxhr_endcallmonitor;
	    	}   
		}
		else
		{
			console.log("ASTATUS: "+astatus+" READYSTATE: "+$jqxhr_endcallmonitor.readyState+" ENDCALLMONITOR did not run.");
		}
	}
}
function endcallmonitor(_phone, _userid, _leadid, _projectid)
{
	$jqxhr_endcallmonitor = $.ajax({
		url: "callmonitor/asm_endcallmonitor.php?"+$.param({"phone" : _phone, "userid" : _userid, "leadid" : _leadid, "projectid" : _projectid}), 
		timeout: 60000,
        error: function(resp, localerr, httperr) {
            console.log("jqxhr_END resp: " + resp);
            console.log("jqxhr_END localerr: " + localerr);
            console.log("jqxhr_END httperr: " + httperr);

            if (httperr == 'timeout')
            {
				postajax_checkdialstate();
			}
			else if (httperr != 'abort')
			{
				endcallmonitor(_phone, _userid, _leadid, _projectid );
			}
		},
		success: function(resp)
		{
			// console.log("ID: "+resp.id+" MSG: "+resp.msg);
			
			postajax_checkdialstate();
		}

	});

}
function postajax_checkdialstate()
{
    $.ajax({
        url: 'ajax.php?act=dialstate&userid='+userid,
		error: function(resp, localerr, httperr) {
			postajax_checkdialstate();
		},
        success: function(resp){
            if (resp == 'ended')
            {
				console.log("HANGUP: ASTATUS "+astatus+" DIALMODE "+dialmode);

                disableb('hbb');
                enableb('dbb');
                if (dialmode == 'progressive')
                {
                    //enableb('cbtab');
                    enablecbclick();
                }
                if (dialmode == 'predictive' || dialmode == 'inbound')
                {
					console.log("Render: NEXT BUTTON");
					setTimeout(function(){enableb('nbb');}, 4000);                    
                }
                astatus = 'hanged';
            }
            else
            {
                window.astatus = resp;
            }
            if (astatus == 'incall' || astatus == 'dialing' || astatus == 'inboundcall' || astatus == 'oncall' || astatus == 'conference')
            {
                setTimeout("checkdialstate()",3000);
            }
			else
			{
				console.log("ASTATUS: "+astatus+" postajax_checkdialstate() ENDED.")
				if (astatus == 'available' || astatus == 'dialing')
				{
					postajax_checkforcalls();
				}
				else if (astatus == 'hanged')
				{
					if (Ext.getCmp('nbb').hidden)
					{
						toggledial();
					}
				}
			}
        }
    });
}

running = 0;
function checkforcalls()
{
    if (dialmode == 'predictive' || dialmode == 'blended' || dialmode == 'inbound')
	{
		if (typeof($jqxhr_incallmonitor) == 'undefined')
		{
			astatus = 'checking';
            hideb('start');
            disableb('nlbutton');
            disableb('dbb');
            showb('pause');
	        incallmonitor($("#phone").val(), userid, $("#leadid").val(), $("#switchprojectid").val());   
		}
		else if ($jqxhr_incallmonitor.readyState == 4)
		{
			if (astatus == 'paused')
			{
	        	delete $jqxhr_incallmonitor;
        	}
        	else
        	{
				astatus = 'checking';
	            hideb('start');
	            disableb('nlbutton');
	            disableb('dbb');
	            showb('pause');
		        incallmonitor($("#phone").val(), userid, $("#leadid").val(), $("#switchprojectid").val());
		    }
		} 
		else if ($jqxhr_incallmonitor.readyState == 0)
		{
			if (astatus == 'checking') 
			{
	        	incallmonitor($("#phone").val(), userid, $("#leadid").val(), $("#switchprojectid").val());
	            hideb('start');
	            disableb('nlbutton');
	            disableb('dbb');
	            showb('pause');
	        }
	        else
	        {
	        	delete $jqxhr_incallmonitor;
	    	}   
		}
		else if ($jqxhr_incallmonitor.readyState == 1)
		{
			if (astatus == 'checking') 
			{

			}
			else
			{
				postajax_checkforcalls();
			}
		}
		else
		{
			console.log("ASTATUS: "+astatus+" READYSTATE: "+$jqxhr_incallmonitor.readyState+" INCALLMONITOR did not run.");
		}
	}
}
function incallmonitor(_phone, _userid, _leadid, _projectid)
{
	$jqxhr_incallmonitor = $.ajax({
		url: "callmonitor/asm_incallmonitor.php?"+$.param({"phone" : _phone, "userid" : _userid, "leadid" : _leadid, "projectid" : _projectid}), 
		timeout: 60000,
        error: function(resp, localerr, httperr) {
            console.log("jqxhr_IN resp: " + resp);
            console.log("jqxhr_IN localerr: " + localerr);
            console.log("jqxhr_IN httperr: " + httperr);

            if (httperr == 'timeout')
            {
				postajax_checkforcalls();
			}
			else if (httperr != 'abort')
			{
				incallmonitor(_phone, _userid, _leadid, _projectid );
			}
		},
		success: function(resp)
		{
			// console.log("ID: "+resp.id+" MSG: "+resp.msg);
			
			postajax_checkforcalls();
		}

	});

}
function postajax_checkforcalls()
{
    if (dialmode == 'predictive' || dialmode == 'blended' || dialmode == 'inbound')
	{
		if (running == 0)
		{
    		if (clicked ==1)
    		{
    			running = 1;
    			astatus = 'checking';
    			clearfields();
    			checking = 0;
    			clearfields();
    			var sId = '<?=$userid;?>';
    			showb('pause');
    			
                $.ajax({
                    url: urlcheck + escape(sId),
        			error: function(resp, localerr, httperr) {
						postajax_checkforcalls();
					},
                    success: handleHttpResponse
                });
    		}
    		else 
    		{
    			checking = 1;
    			setTimeout('checkforcalls();',500);
    		}
		}
	}
}
function callmonitor_update(_calltitle, _callmsg)
{
	if (typeof $callcontainer == 'undefined')
	{
		// $callcontainer = $("#callcontainer");
		return;
	}

    $callcontainer.html("<br/><H3>"+_callmsg+"</H3><br/>");

    $callcontainer.dialog({
		title: _calltitle,
		width: "380",
		modal: true,
		draggable: false,
		closeOnEscape: false,
		dialogClass: "no-close",
		resizable: false,
		buttons: {
			"Close" : function () {
				$( this ).dialog("close");
			}
		}
	});	
}
function callmonitor(_phone, _userid, _leadid, _projectid)
{
	$.ajax({
		url: "callmonitor/asm_callmonitor.php?"+$.param({"phone" : _phone, "userid" : _userid, "leadid" : _leadid, "projectid" : _projectid}), 
		timeout: 15000,
		error: function()
		{

		},
		success: function(resp)
		{
			// console.log("ID: "+resp.id+" MSG: "+resp.msg);
			callmonitor_update("Manual Dial", resp.msg);
		}

	});
}

<?php
	// END: ajax-scripts-include.php
?>