<div class="adminMenuLeft"><div class="apptitle">Time Tracker</div>
    <div class="secnav">
    </div>
</div>
<div id="adminRightSide" style="float: left;width: 82%;">
    <div id="delete-confirm" title="Delete Custom Event?" style="display:none">
      <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><font>This item will be permanently deleted and cannot be recovered. Are you sure?</font></p>
    </div>
    <div id="ttUsersTimeTracker">
        <p>
        <h3>Timeout Events</h3>
        <table id="ttEventsTimeout">
            <thead>
              <tr><th>Event</th><th>Seconds</th><th>Dialing Mode</th></tr>
            </thead>
            <tbody>
            <tr>
                <td>Pause</td>
                <td>
                  <lablel for="tteventspause"> 
                  <input id="tteventspause" size="5" maxlength="5" onChange="tteventsopt_update(0,'timeoutPause',this.value)">
                  </lablel>(Allowable Pause Time before agent is automatically logged out.)
                </td>
                <td>Inbound & Predictive Only</td>
            </tr>
            <tr>
                <td>Minimum Idle</td>
                <td>
                  <lablel for="tteventsidle">
                  <input id="tteventsidle" size="5" maxlength="5" onChange="tteventsopt_update(0,'timeoutIdle',this.value)">
                  </lablel>(Inactivity before agent is considered idling.)
                </td>
                <td>Inbound, Predictive & Progressive</td>
            </tr>
            <tr>
                <td>Maximum Idle</td>
                <td>
                  <lablel for="tteventsidlemax">
                  <input id="tteventsidlemax" size="5" maxlength="5" onChange="tteventsopt_update(0,'timeoutIdlemax',this.value)">
                  </lablel>(Idle time before agent is automatically logged out.)
                </td>
                <td>Inbound, Predictive & Progressive</td>
            </tr>
            </tbody>
        </table>
        </p>        

        <p><br></p>
        <p><br></p>

        <p align="right">
            <input id="newcustomevent" type="button" value="New Custom Event" onclick="dialogwindow('newttrackerevent')">
        </p>
        <h3>Custom Events</h3>
        <table id="ttEventsCustom">
            <thead>
              <tr><th>Ids</th><th>Event</th><th>Description</th></tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </p>
        
        <br/>
        <br/>
        <p>
        <h3>Default Events</h3>
        <table id="ttEventsDefault">
            <thead>
              <tr><th>Event</th><th>Description</th></tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </p>

        <br/>
        <br/>
        <p align="right">
            <input id="enablecampaignsall" type="button" value="Enable In All Campaigns" onclick="ttCampaignsSetAll('enable')">
            <input id="disablecampaignsall" type="button" value="Disable In All Campaigns" onclick="ttCampaignsSetAll('disable')">
        </p>
        <p>
        <h3>Campaigns</h3>
        <table id="ttEventsOptCampaigns">
            <thead>
              <tr><th>Id</th><th>Campaign</th><th>Time Tracker Setting</th></tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </p>
    </div>
	
</div>
<script>
function ttDBCreateNewCustomEvent(tisid)
{
    var _ttFORMNewCustomEventTextbox = document.getElementById('ttFORMNewCustomEventTextbox').value;
    var _ttFORMNewCustomEventDescTextbox = document.getElementById('ttFORMNewCustomEventDescTextbox').value;

    $ottEventsCustom.fnAddData([0,_ttFORMNewCustomEventTextbox,_ttFORMNewCustomEventDescTextbox]);

    // $ottEventsCustom.$("tr:last-child").find('td:eq(0)').prepend('<a href="#"><span class="ui-icon ui-icon-trash" style="float: left" title="Delete"></span></a>&nbsp;&nbsp;');
    // $ottEventsCustom.$("tr:last-child").find('td:eq(0)').find('a').on( 'click', function() { 
    //                alert( $(this).parent().parent().attr('id') );

    // $ottEventsCustom.$("tr:last-child").find('td:eq(0)').find('a').on( 'click', function() {
    //});

    // alert("New Custom Event: " + _ttFORMNewCustomEventTextbox + "<br/>New Custom Event Descriptions: " + _ttFORMNewCustomEventDescTextbox + "<br/>");

/**
    var params = '?act=ttDBCreateNewCustomEvent&curUSERNAME='+escape(username)+'&dbBreak='+escape(_ttFORMNewCustomEventTextbox)+'&dbBreakDesc='+escape(_ttFORMNewCustomEventDescTextbox);
    senddata(params, function(data){
            jQuery("#respmessage").html(data);
            jQuery(".entryform form")[0].reset();
    });
**/
    $.ajax({
        url: "timetracker_management/ajax/ttEventsDB.php?act=newcustomevent&"+$.param({ "user_id": <?=$_SESSION['uid']?>, "break": _ttFORMNewCustomEventTextbox, "breakdesc": _ttFORMNewCustomEventDescTextbox, "bc_id": <?=$bcid?> }),
        success: function(resp)
        {
            $ottEventsCustom.dataTable().fnDestroy();
            $ottEventsCustom.attr('style',"width: 100%");
            $ottEventsCustom.dataTable( {
                "sAjaxSource": "timetracker_management/ajax/ttEventsCustom.php?curBCID=<?=$bcid?>",
                "aoColumnDefs": [{"sClass": "customEventIds", "bVisible" : false, "aTargets" : [0] }],
                "fnInitComplete": function() {
                    $('.customevent').find('td:eq(0)').prepend('<a href="#"><span class="ui-icon ui-icon-trash" style="float: left" title="Delete"></span></a>&nbsp;&nbsp;');
                    $('.customevent').find('a').on( 'click', function() { 
                        // alert( $(this).parent().parent().attr('id') );
                        var customevent_row = $(this).parent().parent();
                        $( "#delete-confirm" ).dialog({
                            resizable: false,
                            height:140,
                            modal: true,
                            buttons: {
                                "Delete": function() {
                                    customevent_row.toggle();
                                    $.ajax({
                                        url: "timetracker_management/ajax/ttEventsDB.php?act=deletecustomevent&"+$.param({ "user_id": <?=$_SESSION['uid']?>, "bc_id": <?=$bcid?>, "ttevents_id": customevent_row.attr('id') }),
                                        success: function(resp)
                                        {
                                        }
                                    })                                    
                                    $( this ).dialog( "close" );
                                },
                                Cancel: function() {
                                    $( this ).dialog( "close" );
                                }
                            }
                        });

                    });
                }
            //$ottEventsCustom.dataTable( {
            });

        //success: function(resp)
        }
    });

}
</script>
<script>
$(document).ready(function() {

    $.extend( $.fn.dataTable.defaults, {
        "bPaginate": false,
        "bLengthChange": false,
        "bFilter": true,
        "bSort": false,
        "bInfo": false,
        "bFiltered":false,
        "bProcessing": true,
        "aaSorting": [[ 0, "asc" ]]
    } );

    $ottEventsTimeout = $('#ttEventsTimeout').dataTable();

    $ottEventsCustom = $('#ttEventsCustom').dataTable( {
        "sAjaxSource": "timetracker_management/ajax/ttEventsCustom.php?curBCID=<?=$bcid?>",
        "aoColumnDefs": [{"sClass": "customEventIds", "bVisible" : false, "aTargets" : [0] }],
        "fnInitComplete": function() {
            $('.customevent').find('td:eq(0)').prepend('<a href="#"><span class="ui-icon ui-icon-trash" style="float: left" title="Delete"></span></a>&nbsp;&nbsp;');
            $('.customevent').find('a').on( 'click', function() { 
                // alert( $(this).parent().parent().attr('id') );
                var customevent_row = $(this).parent().parent();

                $( "#delete-confirm" ).dialog({
                    resizable: false,
                    height:140,
                    modal: true,
                    buttons: {
                        "Delete": function() {
                            customevent_row.toggle();
                            $.ajax({
                                url: "timetracker_management/ajax/ttEventsDB.php?act=deletecustomevent&"+$.param({ "user_id": <?=$_SESSION['uid']?>, "bc_id": <?=$bcid?>, "ttevents_id": customevent_row.attr('id') }),
                                success: function(resp)
                                {
                                }
                            })                                    
                            $( this ).dialog( "close" );
                        },
                        Cancel: function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });

                // $(this).parent().parent().toggle();
            //$('#customeventclick').on( 'click', function() {
            });

        //"fnInitComplete": function() {
        }

    //$ottEventsCustom = $('#ttEventsCustom').dataTable( {
    } );

    $ottEventsDefault = $('#ttEventsDefault').dataTable( {
        "sAjaxSource": "timetracker_management/ajax/ttEventsDefault.php"
    } );

    $ottEventsOptCampaigns = $('#ttEventsOptCampaigns').dataTable( {
        "sAjaxSource": "timetracker_management/ajax/ttEventsOptShowCampaigns-dt.php?_bcid=<?=$bcid?>",
        "aoColumnDefs": [{"sClass": "campaignIds", "bVisible" : false, "aTargets" : [0] }],
        "bPaginate": true,
        "bLengthChange": true,
        "bFilter": true,
        "bInfo": true,
        "bFiltered":true,
        "aLengthMenu": [50, 100, 150, 200],
        "iDisplayLength": 150,
        "fnInitComplete" : function () {
                $('#newcustomevent').button();
                $('#enablecampaignsall').button();
                $('#disablecampaignsall').button();
                // $ottEventsOptCampaigns.find('select').selectmenu();
        }
    } );

    $(".dataTables_filter").attr("style","display: none");
    $("#ttEventsOptCampaigns_filter").toggle();
    
    $.ajax({
        url: "timetracker_management/ajax/ttEventsOptDB.php?act=get&config=timeoutPause&project_id=<?=$bcid?>",
        success: function(resp)
        {
            $('#tteventspause').val(resp.timeoutPause);
        }
    });

    $.ajax({
        url: "timetracker_management/ajax/ttEventsOptDB.php?act=get&config=timeoutIdle&project_id=<?=$bcid?>",
        success: function(resp)
        {
            $('#tteventsidle').val(resp.timeoutIdle);
        }
    });

    $.ajax({
        url: "timetracker_management/ajax/ttEventsOptDB.php?act=get&config=timeoutIdleMax&project_id=<?=$bcid?>",
        success: function(resp)
        {
            $('#tteventsidlemax').val(resp.timeoutIdleMax);
        }
    });

//$(document).ready(function() {
} );
function ttCampaignsSetAll(_setall)
{
    if (_setall == 'enable')
        $ottEventsOptCampaigns.find('select').val(1).change();
    else
        $ottEventsOptCampaigns.find('select').val(0).change();

    $ottEventsOptCampaigns.dataTable().fnDestroy();
    $ottEventsOptCampaigns.width("100%");
    $ottEventsOptCampaigns.dataTable( {
        "sAjaxSource": "timetracker_management/ajax/ttEventsOptShowCampaigns-dt.php?_bcid=<?=$bcid?>",
        "aoColumnDefs": [{"sClass": "campaignIds", "bVisible" : false, "aTargets" : [0] }],
        "bPaginate": true,
        "bLengthChange": true,
        "bFilter": true,
        "bInfo": true,
        "bFiltered":true,
        "aLengthMenu": [50, 100, 150, 200],
        "iDisplayLength": 150,
        "fnInitComplete" : function () {
                // $ottEventsOptCampaigns.find('select').selectmenu();
        }
    } );
}
function tteventsopt_update(_project_id,_config,_value)
{
    // alert("New "+_config+": "+_value);
    $.ajax({
        url: "timetracker_management/ajax/ttEventsOptDB.php?act=update&"+$.param({"project_id":<?=$bcid?>, "config":_config, "value":_value, "user_id": <?=$_SESSION['uid']?>}),
        success: function(resp)
        {
            // alert("new_record: "+resp.new_record);
        }
    });
}
</script>