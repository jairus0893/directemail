<?php
	// scripts-include.php
	// BEGIN
?>
function agentspy(_exten,_mode)
{
    if (adminext == 'none') {
        alert('Set your extension first, and also logon to your extension via the softphone.');
        setadminext();        
    }
    else
    {
		$.ajax({
			url: "agentspy/connect.php?"+$.param({"admin_exten" : adminext, "agent_exten" : _exten, "spymode" : _mode}), 
			success: function(resp)
			{
				alert('To end agent whisper session, just hangup your extension.');
			}
		});        
    }
}
function barge(exten)
{
    Ext.Msg.alert("info",exten)
}
function endbarge()
{
    $.ajax({
                    url: "admin.php?act=endbarge&origin="+adminext,
                    success: function(){
                        $("#formloader").dialog("destroy");
                    }
		});
}
function bargethis(ext)
{
    if (adminext == 'none') {
        alert('Set your extension first.');
        //setadminext();
        
    }
    else {
        $.ajax({
                    url: "admin.php?act=barge&origin="+adminext+"&target="+ext,
                    success: function(resp){
                        $("#formloader").dialog("destroy");
                        $("#formloader").html(resp);
                            $("#formloader").dialog({
                                modal: true,
                                title: "Barge",
                                close: endbarge
                            });
                            $(".jbut").button();
                    }
		});
    }
}
<?php
	// scripts-include.php 
	// END
?>