<?php 
    // BEGIN ajax-scripts-include.php
?>
function answerinboundcall(callid)
{
    // $("#dialogcontainer").dialog("close");
    $.ajax({
        url: 'ajax.php?act=answerinboundcall&callid='+callid,
        success: function(resp){
            if (resp != 'Pickup Failed')
            {
                inbound_uselead(resp);
                checkdialstate();
                // alert("Call Answered");
            }
            else
            {
                // alert(resp);
                alert("Call was put back to queue.");
                $("#dialogcontainer").dialog("close");
            }
        },
        error: function(resp, localerr, httperr){
            console.log("resp: " + resp);
            console.log("localerr: " + localerr);
            console.log("httperr: " + httperr);
        }
    });
}
function ChatUpdate(m,s) {
        $("#Chatnotify").block({  
            message: '<div class="ui-state-notify ui-corner-all" style="padding: 0 .7em; height: 35px;background:#c6ffb3;border-color:#39e600;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong></strong>'+m+'</p></div>', 
            fadeIn: 700, 
            fadeOut: 700, 
            timeout: 0, 
            showOverlay: false, 
            centerY: false, 
            css: { 
                background: 'transparent',
                position: 'absolute',
                fontSize: '1em',
                top: '20px', 
                left: '', 
                right: '630px', 
                border: 'none', 
                padding: '5px', 
                width: '200px',
                height: '34px',
                '-webkit-border-radius': '10px', 
                '-moz-border-radius': '10px', 
                color: '#fff',
                cursor: 'pointer'
                
            } 
        }); 
        $('.ui-state-notify').click(function(){
            $.unblockUI();
            var win = document.getElementById('ifrm').contentWindow.loadRooms(s);
            //var showRoom = document.getElementById('ifrm').contentWindow.OpenJoinChat();
            });
    }
function newinboundcallmanual()
{
    var ld = document.getElementById('leadid').value;
    var dp = document.getElementById('disposition').selectedIndex;
    var phone = document.getElementById('phone').value;

    if (ld > 0 && dp == 0 && astatus != 'cbview' && phone != 'anonymous')
            {
            dispose(newinboundcallmanual);
            }
    $.ajax({
        url: 'ajax.php?act=newinboundcallmanual',
        success: function(resp){
            $("#dialogcontainer").html(resp);
            $("#dialogcontainer").dialog({
                title: 'New Inbound Call',
                width: 400,
                maxHeight: 400,
                modal: true,
                closeOnEscape: false,
                open: function(event, ui) { $(".ui-dialog-titlebar-close", ui.dialog || ui).hide(); }
                
            });
        },
        error: function(resp, localerr, httperr){
            console.log("resp: " + resp);
            console.log("localerr: " + localerr);
            console.log("httperr: " + httperr);
        }
    });
}

function notification(message,tab,onclickcallback)
{
    $("#notify").block({ 
        message: '<div class="ui-state-notify ui-corner-all" style="padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><strong>&nbsp;</strong>'+message+'</p></div>', 
                fadeIn: 700, 
        fadeOut: 700, 
        timeout: 0, 
        showOverlay: false, 
        centerY: false, 
        css: { 
                background: 'transparent',
                position: 'absolute',
                fontSize: '1em',
                top: '20px', 
                left: '', 
                right: '440px', 
                border: 'none', 
                padding: '5px', 
                width: '200px',
                '-webkit-border-radius': '10px', 
                '-moz-border-radius': '10px', 
                color: '#fff',
                                cursor: 'pointer'

        } 
        }); 
        $('.ui-state-notify').click(function(){
        $("#notify").unblock();
        Ext.getCmp('maintabpanel').activate(tab);
        onclickcallback();
        }); 
}
function random(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}
function poll_agentqueuecheck()
{
    $.ajax({
        url: "inboundqueue/agent.php?act=campaignqueues&userid="+userid,
        success: function(resp)
        {
            $switchproj = $("#switchprojectid");

            if (typeof resp.length === "undefined")
            {
                for (var campaign in resp)
                {
                    // console.log("CAMPAIGN: "+campaign+","+"QUEUE: "+resp[campaign]);
                    $projoption = $switchproj.find("option[value="+campaign+"]");
                    $projoption.text("IN QUEUE: "+ resp[campaign] +' '+$projoption.attr('label'));
                    console.log("CAMPAIGN: "+campaign);
                }
            }
            else
            {
                $switchproj.find('option').each(
                    function()
                    {
                        $(this).text( $(this).attr('label') );
                    }
                );
            }

            console.log("RESP (typeof): "+typeof(resp));
            console.log("RESP: "+resp.length);

            $switchproj.selectmenu();
        }
    })
    setTimeout("agentqueuecheck()",random(1,3)*1000);
}
function agentqueuecheck() {
    $.ajax({
        url: "inboundqueue/agent.php?act=queuecheck&userid="+userid,
        success: function(resp)
        {
            notification(resp.queuecalls+" Incoming Calls In Queue! ", 0, poll_agentqueuecheck);
        }
    });
}

<?php 
    // END ajax-scripts-include.php
?>
