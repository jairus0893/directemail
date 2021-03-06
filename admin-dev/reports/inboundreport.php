<?php
session_start();
date_default_timezone_set($_SESSION['timezone']);
include "../../dbconnect.php";
include "../phpfunctions.php";
$bcid = getbcid();
include "irajax.php";
if (!checkrights('reports'))
{
    echo "Permission Error.";
    exit;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<link rel="stylesheet" type="text/css" href="../../jquery/jquery-ui-custom/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="../../jquery/datatable/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="../../jquery/DataTables-1.9.4/extras/TableTools/media/css/TableTools.css">
<link rel="stylesheet" type="text/css" href="../../jquery/ptTimeselect/jquery.ptTimeSelect.css">
<script type="text/javascript" src="../../jquery/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../jquery/jquery-ui-custom/jquery-ui.js"></script>
<script type="text/javascript" src="../../jquery/datatable/js/jquery.dataTables.min.js?v2"></script>
<script type="text/javascript" src="../../jquery/DataTables-1.9.4/extras/TableTools/media/js/ZeroClipboard.js"></script>
<script type="text/javascript" src="../../jquery/DataTables-1.9.4/extras/TableTools/media/js/TableTools.js"></script>
<script type="text/javascript" src="../../jquery/ptTimeselect/jquery.ptTimeSelect.js"></script>
<link rel="stylesheet" type="text/css" href="cstyle.css" />
<style>
a.repbutton {
    border: 1px solid #16A6BD;
    color: #16A6BD;
    float: left;
    padding: 10px;
    width: 120px;
    background:#E0FFFF;
    border-radius:10px;
    text-decoration: none;
    font-size:11pt;
}
.clear {
    clear: both;
}
</style>
<script type="text/javascript" >
function ConvertTimeformat(format, str) {
    var hours = Number(str.match(/^(\d+)/)[1]);
    var minutes = Number(str.match(/:(\d+)/)[1]);
    var AMPM = str.match(/\s?([AaPp][Mm]?)$/)[1];
    var pm = ['P', 'p', 'PM', 'pM', 'pm', 'Pm'];
    var am = ['A', 'a', 'AM', 'aM', 'am', 'Am'];
    if (pm.indexOf(AMPM) >= 0 && hours < 12) hours = hours + 12;
    if (am.indexOf(AMPM) >= 0 && hours == 12) hours = hours - 12;
    var sHours = hours.toString();
    var sMinutes = minutes.toString();
    if (hours < 10) sHours = "0" + sHours;
    if (minutes < 10) sMinutes = "0" + sMinutes;
    if (format == '0000') {
        return (sHours + sMinutes);
    } else if (format == '00:00') {
        return (sHours + ":" + sMinutes);
    } else {
        return false;
    }
}
</script>
</head>

<body>
<div id="container">
<div id="header">
<img src="../images/bclogo-small.png" />
<div id="reporttitle">Inbound Report</div>
</div>
<hr />
<div id="query">
    <input type="hidden" name="act" value="dosearch" />
    <table width="929" border="0" cellspacing="0" cellpadding="5">
<!--      <tr>
        <td width="80">Client</td>
        <td width="345"><select id="clients" style="width: 205px">
        <option value="0" selected="selected">All</option>
        </select></td>
        <td width="80">&nbsp;</td>
        <td width="344">&nbsp;</td>
      </tr>-->
      <tr>
        <td width="80">Campaign</td>
        <td width="345"><select id="projects" style="width: 205px">
        <option value="0" selected="selected">All</option>
        </select></td>
        <td width="80">&nbsp;</td>
        <td width="344">&nbsp;</td>
      </tr>
<!--      <tr>
        <td width="80">Agent</td>
        <td width="345"><select id="members" style="width: 205px">
        <option value="0" selected="selected">All</option>
        </select></td>
        <td width="80">&nbsp;</td>
        <td width="344">&nbsp;</td>
      </tr>
      <tr>
        <td width="80">Team</td>
        <td width="345"><select id="teams" style="width: 205px">
        <option value="0" selected="selected">All</option>
        </select></td>
        <td width="80">&nbsp;</td>
        <td width="344">&nbsp;</td>
      </tr>-->
      <tr>
        <td>Date Start</td>
        <td><input style="width: 96px" type="text" name="start" class="dates" id="start" />
            <input style="width: 96px" type="text" name="starttime" class="timeselect" id="starttime" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>Date End</td>
        <td><input style="width: 96px"type="text" name="end" class="dates" id="end"/>
            <input style="width: 96px"type="text" name="endtime" class="timeselect" id="endtime" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <? include("timezone/inboundreport-scripts-include.php") ?>
      <tr>
        <td colspan="4" align="left"><button id="clickview" onclick="viewrep($('#timezone').val(),$('#clients').val(),$('#projects').val(),$('#members').val(),$('#teams').val(), $('#start').val() +' '+ConvertTimeformat('00:00',$('#starttime').val()), $('#end').val()+' '+ConvertTimeformat('00:00',$('#endtime').val()) )">View</button></td>
      </tr>
    </table>

</div>
<div id="reporttabs">
    <ul>
        <li><a href="#reporttabs-1">Summary</a></li>
        <li><a href="#reporttabs-2">Breakdown</a></li>
    </ul>
    <div id="reporttabs-1">
       <table id="ttreportsummary" class="dtt_display">
            <thead>
              <tr>
                <th>Date</th>
                <th>Calls Offered</th>
                <th>Calls Answered</th>
                <th>Average Handle Time (m:s)</th>
                <th>ASA (sec)</th>
                <th>SL%</th>
                <th>ABA%</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <div id="reporttabs-2">
        <table id="ttreportlog" class="dtt_display">
            <thead>
              <tr>
                <th>Campaign</th>
                <th>Phone</th>
                <th>Date</th>
                <th>Queue Time</th>
                <th>Agent</th>
                <th>Talk Time</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<script>
$(function() {
		$( ".dates" ).datepicker({ dateFormat: 'yy-mm-dd' });
	});

			
document.title = window.name;
function viewrep(_timezone, _clientid, _projectid, _memberid, _teamid, _start, _end)
{
    $('#reporttabs').tabs({active: 0});

    $ottreportsummary.dataTable().fnDestroy();
    $ottreportsummary.width("100%");
    $ottreportsummary.dataTable( {

        "sAjaxSource": "?act=loadreportsummary"+"&_timezone="+_timezone+"&_projectid="+_projectid+"&_start="+_start+"&_end="+_end,
        "fnInitComplete": function () {

            $('#reporttabs').tabs({active: 1});

            $ottreportlog.dataTable().fnDestroy();
            $ottreportlog.width("100%");
            $ottreportlog.dataTable( {
                "sAjaxSource": "?act=loadreport"+"&_timezone="+_timezone+"&_projectid="+_projectid+"&_start="+_start+"&_end="+_end,
                "fnInitComplete": function () {
                    $('#reporttabs').tabs({active: 0});
                }
            } );
        }

    } );



}
function exportrep()
{

	var proj = document.getElementById('projectid').value;
	var start = document.getElementById('start').value;
	var end = document.getElementById('end').value;
        var mo = $("#mo").val();
	var url =  "agentperformance.php?act=view&act2=excel&projectid="+proj+"&start="+start+"&end="+end+"&mo="+mo;
	window.open(url);
	
}
function exporttoclient(cid,repname)
{
    var body = $("#apdiv").html();
    var texts = encodeURI(body);
    texts = encodeURIComponent(texts);
    $.ajax({
        url: '../admin.php?act=savereport&cid='+cid+'&rname='+repname,
        type: 'POST',
        data: 'tex='+texts,
        success: function(){
            alert('Client Report Generated!');
        }
    })
}
function updatemo()
{
    var mopid = $("#projectid").val();
    $.ajax({
        url: '../admin.php?act=getstatusbypid',
        type: 'POST',
        data: 'pid='+mopid,
        success: function(resp){
            $("#mo").html(resp);
        }
    })
}
$(document).ready(

    function() {
        function getTimeZoneData() {
            var today = new Date();
            var jan = new Date(today.getFullYear(), 0, 1);
            var jul = new Date(today.getFullYear(), 6, 1);
            var dst = today.getTimezoneOffset() < Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
          
            return {
                offset: -today.getTimezoneOffset() / 60,
                dst: +dst
            };
        }
        $.ajax({
            url : "timezone/timezone_support/timezone_detect.php",
            data: getTimeZoneData(),
            type: 'POST',
            dataType: 'JSON',
            success : function(data) {
                $('#timezone').val(data);
            }
        });

        $("#start").attr('value', "<?=date( 'Y-m-d', strtotime('-1 day') )?>");
        $("#end").attr('value', "<?=date('Y-m-d')?>");

        $("#starttime").attr('value', "12:00 AM");
        $("#endtime").attr('value', "11:59 PM");

        $.extend( $.fn.dataTable.defaults, {
            "sDom": 'T<"clear"lf>rtip',
            "oTableTools": {
                "sSwfPath": "../../jquery/DataTables-1.9.4/extras/TableTools/media/swf/copy_csv_xls_pdf.swf"
            },
            "aLengthMenu": [50, 100, 150, 200],
            "bSort": false,
            "bProcessing": true,
            "aaSorting": [],

            "sPaginationType": "full_numbers",
            "iDisplayLength": 150,
            // "sScrollX": "125%",
            // "sScrollXInner": "200%",
            "bScrollCollapse": true,
            "bAutoWidth": false
        } );

        $('#clickview').button();

        $('#reporttabs').tabs({active: 1});
    
        $.ajax({
            url : '?act=loadrepfilters',
            dataType: 'script',
            success: function(resp) {
                // alert('Loaded report filters.');
            }

        });

        $('#reporttabs').tabs({active: 0});

        $ottreportsummary = $('#ttreportsummary').dataTable( {
            "sAjaxSource": "?act=loadreportsummary"+"&_timezone="+$('#timezone').val()+"&_projectid="+$('#projects').val()+"&_start="+$('#start').val()+' '+ConvertTimeformat('00:00',$('#starttime').val())+"&_end="+$('#end').val()+' '+ConvertTimeformat('00:00',$('#endtime').val()),
            "fnInitComplete": function () {

                $('#reporttabs').tabs({active: 1});
                $ottreportlog = $('#ttreportlog').dataTable( {
                    "sAjaxSource": "?act=loadreport"+"&_timezone="+$('#timezone').val()+"&_projectid="+$('#projects').val()+"&_start="+$('#start').val()+' '+ConvertTimeformat('00:00',$('#starttime').val())+"&_end="+$('#end').val()+' '+ConvertTimeformat('00:00',$('#endtime').val()),
                    "fnInitComplete": function () {
                        $('#reporttabs').tabs({active: 0});
                    }
                } );


            } // fnInitComplete()
        } );

        $('.timeselect').ptTimeSelect({ onClose: function (input) {
                console.log( "input: "+ConvertTimeformat("00:00", input.val()) );
            }

        });

    }
);
</script>