<?php 
    // BEGIN ajax-scripts-include.php
?>
function xferdial_change()
{
	$confparties.val( $("#phone").val() );
	$xferto.prop('selectedIndex',0);
	$xferto_agent.html("<option>Any Agent</option>");
}
function xfer_agents()
{
	if ($xferto.val() == 'None')
	{
		alert("Please select inbound campaign to transfer to first!");
		$xferto_agent.html("<option>Any Agent</option>");
	}
	else
	{
		$.ajax({
			url: "conference/xfer_agents.php?"+$.param({"userid" : userid, "leadid" : $("#leadid").val(), "projectid" : $('#xferto option:selected').attr('label')}), 
			success: function(resp)
			{
				// console.log(resp.callmanid + ' : ' + resp.msg);
				if (resp.callmanid >= 0)
				{
					$xferto_agent.html(resp.msg);
				}
				else
				{
					$xferto_agent.html("<option value='None'>No Agents Logged On</option>");
					alert("No agents logged on for inbound campaign!");
				}
			}
		});
	}
	$xferdial.attr('value', 'None');
}
function lead_connected()
{
	if ($("#phone").val() == "" || $("#phone").val() == 'anonymous')
	{

	}
	else
	{
		if (Ext.getCmp('hbb').disabled)
		{
		}
		else 
		{
			if ($confparties.find("option[value="+$("#phone").val()+"]").html() == null)
			{
				confparties_update($("#phone").val());
			}
		}
	}
}
function altphone_connected()
{
	if ($("#altphone").val() == "")
	{

	}
	else
	{
		if ($("#althang").css('display') != 'none')
		{
			if ($confparties.find("option[value="+$("#altphone").val()+"]").html() == null)
			{
				confparties_update($("#altphone").val());
			}
		}
	}
}
function xferto_options()
{
	$.ajax({
		url: "conference/xferto_options.php?"+$.param({"userid" : userid, "leadid" : $("#leadid").val(), "projectid" : $("#switchprojectid").val()}), 
		success: function(resp)
		{
			// console.log(resp.callmanid + ' : ' + resp.msg);
			if (resp.callmanid >= 0)
			{
				$xferto.append(resp.msg);
			}
			else
			{
				alert("No inbound campaigns found to transfer to!");
			}
		}
	});

}
function confparties_hangup(_phone)
{
	$confparties.find("option[value="+_phone+"]").remove();

	if ($confparties.val() == null)
	{
		$confparties.html("<option>None</option>");
	}	
}
function confparties_change()
{
	$("#confcontact").attr('value', $confparties.val());
}
function confparties_update(_phone)
{
	$("#confcontact").attr('value', _phone);

	if ($confparties.val() == 'None') 
	{
		$confparties.html("<option>"+_phone+"</option>");
	}
	else
	{
		$confparties.prepend("<option SELECTED>"+_phone+"</option>");
	}
}

function confcall()
{
	// if ($("#althang").css('display') != 'none' || !Ext.getCmp('hbb').disabled)
	if (!Ext.getCmp('hbb').disabled)
	{
		if (typeof $conferencecontainer == 'undefined')
		{
		    $conferencecontainer = $("#conferencecontainer");
		    $conferencecontainer.append("<center><h2>CONNECTED PARTIES</h2></center><br/>");
		    $conferencecontainer.append("<center><select id='confparties' onChange='confparties_change()'><option>None</option></select></center><br/>");
		    $conferencecontainer.append("<center><h2>ADD PHONE NUMBER</h2></center><br/>");
		    $conferencecontainer.append("<center><input id='confcontact' type='text' /></center></br>");
		    $conferencecontainer.append("<center><h2>TRANSFER PARTY TO <b>AGENT</b></h2></center><br/>");
		    $conferencecontainer.append("<center><select id='xferto' onChange='xfer_agents()'><option value='None'>Inbound Campaign</option></select>&nbsp;&nbsp;<select id='xferto_agent'><option>Any Agent</option></select></center><br/>");
		    $conferencecontainer.append("<center><h2>TRANSFER PARTY TO <b>PHONE NUMBER</b></h2></center><br/>");
		    $conferencecontainer.append("<center><input id='xferdial' onChange='xferdial_change()' type='text' name='outsideline' value='None'/></br><pre>(Country Code + Phone Number)</pre></center><br/>");
		    $confparties = $("#confparties");
		    $xferto = $("#xferto");
		    $xferto_agent = $("#xferto_agent");
			$xferdial = $("#xferdial");
		    xferto_options();
		}
		xferdial_change();
		$xferdial.attr('value', 'None');


		// altphone_connected();
		lead_connected();

	    $conferencecontainer.dialog({
			title: "Conference",
			width: "380",
			modal: true,
			draggable: false,
			closeOnEscape: false,
			dialogClass: "no-close",
			resizable: false,
			buttons: {
				"Dial" : function() {
					if ($("#confcontact").val() == '')
					{
						alert("Nothing to dial!");
					}
					else if ($("#leadid").val() == '')
					{
						alert("No lead active!");
					}
					else if ($confparties.find("option[value="+$("#confcontact").val()+"]").html() != null)
					{
						alert("Phone number already in conference!");
					}
					else
					{
						callmonitor($("#confcontact").val(), userid, $("#leadid").val(), $("#switchprojectid").val());

						// alert("Dialing... "+$("#confcontact").val()+" !");
						$.ajax({
							url: "conference/dial.php?"+$.param({"phone" : $("#confcontact").val(), "userid" : userid, "leadid" : $("#leadid").val(), "projectid" : $("#switchprojectid").val()}), 
							success: function(resp)
							{
								confparties_update($("#confcontact").val());
							}

						});
					}				
					// $conferencecontainer.html('');
				},

				"Hangup" : function () 
				{
					if ($confparties.val() == $("#phone").val() || $confparties.val() == $("#altphone").val())
					{
						alert("Prospect's MAIN PHONE NUMBER can only be hangup from the MAIN TAB.");
					}
					else
					{
						if ($confparties.val() == 'None')
						{
							alert('Nothing to hangup!');
						}
						else
						{
							$.ajax({
								url: "conference/hangup.php?"+$.param({"phone" : $confparties.val(), "userid" : userid, "leadid" : $("#leadid").val(), "projectid" : $("#switchprojectid").val()}), 
								success: function(resp)
								{
									confparties_hangup($confparties.val());
								}

							});
						}
					}
				},

				"Transfer" : function()
				{
					if ($confparties.val() == 'None')
					{
						alert('No party to transfer!');
					}
					else if ( $xferto.val() == 'None' && $xferdial.val() == 'None' )
					{
						alert('Nothing to transfer party to!');
					}
					else
					{

						if ($xferdial.val() == 'None')
						{
							$.ajax({
								url: "conference/transfer.php?"+$.param({"phone" : $confparties.val(), "xferto" : $xferto.val(), "xferto_agent" : $xferto_agent.val(), "userid" : userid, "leadid" : $("#leadid").val(), "projectid" : $("#switchprojectid").val()}), 
								success: function(resp)
								{
									confparties_hangup($confparties.val());
								}

							});
						}
						else if ($xferto.val() == 'None')
						{
							$.ajax({
								url: "conference/transfer.php?"+$.param({"phone" : $confparties.val(), "xferto" : $xferdial.val(), "xferto_agent" : $xferto_agent.val(), "userid" : userid, "leadid" : $("#leadid").val(), "projectid" : $("#switchprojectid").val()}), 
								success: function(resp)
								{
									confparties_hangup($confparties.val());
								}

							});
						}
						postajax_checkdialstate();
					}
				},

				"Close" : function () {
					$( this ).dialog("close");
				}

			}
		});
	}
	else
	{
		alert("Conference is only available when prospect's MAIN PHONE NUMBER IS CONNECTED.");
	}
}

<?php 
    // END ajax-scripts-include.php
?>
