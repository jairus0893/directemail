<?php
/***
        url: "agentstatus/changed.php?"+$.param({
            "oldstatus" : text, 
            "newstatus" : currentText, 
            "phone" : $("#phone").val(), 
            "userid" : userid, 
            "leadid" : $("#leadid").val(), 
            "projectid" : $("#switchprojectid").val()
        })
***/

include_once("./../../dbconnect.php");

    $_qry = "UPDATE liveusers SET statustimestamp = unix_timestamp() WHERE userid = " . $_REQUEST["userid"];
    mysql_query($_qry);

    // echo $_qry;
?>