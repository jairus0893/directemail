<?php
include "../../../dbconnect.php";

$act        = $_REQUEST['act'];
$scriptid   = $_REQUEST['scriptid'];
$scriptbody = $_POST['scriptbody'];

if ($act == 'updatescriptmsg') {
    $texts = rawurldecode($scriptbody);
    mysql_query("update scripts set scriptbody = '" . mysql_real_escape_string($texts) . "' where scriptid = '$scriptid'");
} elseif ($act == 'getscriptmsg') {
    $query        = "SELECT * from scripts where scriptid = '$scriptid'";
    $query2       = mysql_query($query);
    $row          = mysql_fetch_array($query2);
    $scripts_body = $row['scriptbody'];
    echo $scripts_body;
} elseif ($act == 'addcustomfield') {
    $pid         = $_REQUEST['projectid'];
    $customname  = $_POST['cname'];
    $customlabel = $_POST['clabel'];
    $res         = mysql_query("SELECT * from projects where projectid = '$pid'");
    $row         = mysql_fetch_assoc($res);
    $cf          = json_decode($row['customfields'], true);
    if ($cf) {
        $cf[$customname] = $customlabel;
    } else {
        $cf              = array();
        $cf[$customname] = $customlabel;
    }
    
    mysql_query("update projects set customfields = '" . mysql_real_escape_string(json_encode($cf)) . "' where projectid = '$pid'");
    exit;
} elseif ($act == 'savefield') {
    $slabel   = $_POST['slabel'];
    $sname    = $_POST['sname'];
    $htmlbody = $_POST['htmlbody'];
    $res      = mysql_query("SELECT * from scripts where scriptid = '$scriptid'");
    $row      = mysql_fetch_assoc($res);
    $savef    = json_decode($row['savedfields'], true);
    if (empty($savef)) {
        if ($savef) {
            $savef[$sname] = $htmlbody;
        } else {
            $savef          = array();
            $savef[$sname] = $htmlbody;
        }
        
        mysql_query("update scripts set savedfields = '" . mysql_real_escape_string(json_encode($savef)) . "' where scriptid = '$scriptid'");
        exit;
    } else {
        foreach ($savef as $key => $value) {
            if ($slabel == $key) {
                echo 'invalid';
            } else {
                if ($slabel == $key) {
                    echo 'invalid';
                } else {
                    if ($savef) {
                        $savef[$sname] = $htmlbody;
                    } else {
                        $savef          = array();
                        $savef[$sname] = $htmlbody;
                    }
                    
                    mysql_query("update scripts set savedfields = '" . mysql_real_escape_string(json_encode($savef)) . "' where scriptid = '$scriptid'");
                }
            }
        }
    }
} elseif ($act == 'getsavefieldval') {
    $fieldval      = $_POST['fieldval'];
    $script_result = mysql_query("SELECT * FROM scripts WHERE scriptid = '$scriptid'");
    $script_row    = mysql_fetch_array($script_result);
    $save_flds     = json_decode($script_row['savedfields'], true);
    
    // Custom Fields
    
    foreach ($save_flds as $key => $value) {
        if ($fieldval == $key) {
            echo $value;
        }
    }
} elseif ($act == 'removesavefieldval') {
    $fieldname     = $_POST['savelabel'];
    $script_result = mysql_query("SELECT * FROM scripts WHERE scriptid = '$scriptid'");
    $script_row    = mysql_fetch_array($script_result);
    $save_flds     = json_decode($script_row['savedfields'], true);
    foreach ($save_flds as $key => $value) {
        if ($key == $fieldname) {
            unset($save_flds[$key]);
        }
    }
    
    mysql_query("update scripts set savedfields = '" . mysql_real_escape_string(json_encode($save_flds)) . "' where scriptid = '$scriptid'");
    exit;
} elseif ($act == 'updatefield') {

    $slabel   = $_POST['slabel'];
    $htmlbody = $_POST['htmlbody'];
    $res      = mysql_query("SELECT * from scripts where scriptid = '$scriptid'");
    $row      = mysql_fetch_assoc($res);
    $savef    = json_decode($row['savedfields'], true);

    foreach ($savef as $key => $value) {

        if ($key == $slabel){

             $savef[$key] = $htmlbody;
            
        }
    }
    mysql_query("update scripts set savedfields = '" . mysql_real_escape_string(json_encode($savef)) . "' where scriptid = '$scriptid'");
    exit;

} elseif ($act == 'getcustomlabel') {
    $pid         = $_REQUEST['projectid'];
    $savelabel   = $_POST['savelabel'];
    $proj_result = mysql_query("SELECT * FROM projects WHERE projectid = '$pid'");
    $proj_row = mysql_fetch_array($proj_result);
    $cust_flds = json_decode($proj_row['customfields'], true);

    foreach ($cust_flds as $key => $value) {
        if ($key == $savelabel) {
            echo $value;    
        }
    }  
}elseif  ($act == 'addscriptdata') {

    $pid         = $_REQUEST['projectid'];
    $question    = $_POST['question'];
    $fieldname   = $_POST['fieldname'];
    $fields      = $_POST['fields'];
    $htmlbody     = $_POST['htmlbody'];

    $res         = mysql_query("SELECT * from projects where projectid = '$pid'");
    $row         = mysql_fetch_assoc($res);
    $cf          = json_decode($row['scriptcustomfields'], true);

    if ($cf) {
        $cf[$fieldname] = $question;
    } else {
        $cf              = array();
        $cf[$fieldname] = $question;
    }
    
    mysql_query("update projects set scriptcustomfields = '" . mysql_real_escape_string(json_encode($cf)) . "' where projectid = '$pid'");


    if ($fields == 'dropdown'){

        $res      = mysql_query("SELECT * from scripts where scriptid = '$scriptid'");
        $row      = mysql_fetch_assoc($res);
        $savef    = json_decode($row['savedfields'], true);

        if ($savef) {
            $savef[$fieldname]['dropdown'] = $htmlbody;
        } else {
            // $savef          = array();
            // $savef[$fieldname] = $htmlbody;
            
            $savef =array( $fieldname => array("dropdown" => $htmlbody));
        }
        
        // echo json_encode($savef);
        mysql_query("update scripts set savedfields = '" . mysql_real_escape_string(json_encode($savef)) . "' where scriptid = '$scriptid'");

    
    }else{
        $res      = mysql_query("SELECT * from scripts where scriptid = '$scriptid'");
        $row      = mysql_fetch_assoc($res);
        $savef    = json_decode($row['savedfields'], true);

        if ($savef) {
            $savef[$fieldname]['textbox'] = "";
        } else {

            $savef =array( $fieldname => array("textbox" => ""));
        }
        
        // echo json_encode($savef);
        mysql_query("update scripts set savedfields = '" . mysql_real_escape_string(json_encode($savef)) . "' where scriptid = '$scriptid'");
    }


} elseif ($act == 'getsavedatacapturedval') {
    $pid           = $_REQUEST['projectid'];
    $fieldval      = $_POST['fieldval'];
    $script_result = mysql_query("SELECT * from projects where projectid = '$pid'");
    $script_row    = mysql_fetch_array($script_result);
    $save_flds     = json_decode($script_row['scriptcustomfields'], true);
    
    foreach ($save_flds as $key => $value) {
        if ($fieldval == $key) {
            echo $value;
        }
    }

 
} elseif ($act == 'getsscriptdatafield') {

    $fieldval      = $_POST['fieldval'];
    $script_result = mysql_query("SELECT * FROM scripts WHERE scriptid = '$scriptid'");
    $script_row    = mysql_fetch_array($script_result);
    $save_flds     = json_decode($script_row['savedfields'], true);
    
    
    foreach($save_flds[$fieldval]  as $key=>$value) { 
        
        echo $key;
    }
    
} elseif ($act == 'getsscriptdatafieldvalue') {

    $fieldval      = $_POST['fieldval'];
    $script_result = mysql_query("SELECT * FROM scripts WHERE scriptid = '$scriptid'");
    $script_row    = mysql_fetch_array($script_result);
    $save_flds     = json_decode($script_row['savedfields'], true);
    
    
    foreach($save_flds[$fieldval]  as $key=>$value) { 
        
        echo $value;
    }

} elseif ($act == 'updatescriptdata') {

    $pid         = $_REQUEST['projectid'];
    $question    = $_POST['question'];
    $fieldname   = $_POST['fieldname'];
    $fields      = $_POST['fields'];
    $htmlbody    = $_POST['htmlbody'];

    $res         = mysql_query("SELECT * from projects where projectid = '$pid'");
    $row         = mysql_fetch_assoc($res);
    $cf          = json_decode($row['scriptcustomfields'], true);

    foreach ($cf  as $key => $value) {

        if ($key == $fieldname){

            $cf[$key] = $question;
            
        }
    }
    mysql_query("update projects set scriptcustomfields = '" . mysql_real_escape_string(json_encode($cf)) . "' where projectid = '$pid'");


    if ($fields == 'dropdown'){

        $res      = mysql_query("SELECT * from scripts where scriptid = '$scriptid'");
        $row      = mysql_fetch_assoc($res);
        $savef    = json_decode($row['savedfields'], true);


        foreach ($savef[$fieldname] as $key => $value) {
            $savef[$fieldname]['dropdown'] = $htmlbody;
        }
        mysql_query("update scripts set savedfields = '" . mysql_real_escape_string(json_encode($savef)) . "' where scriptid = '$scriptid'");

    }

} elseif ($act == 'removescriptdata') {
    $fieldname   = $_POST['fieldname'];
    $pid         = $_REQUEST['projectid'];
    $fields      = $_POST['fields'];

    $res         = mysql_query("SELECT * from projects where projectid = '$pid'");
    $row         = mysql_fetch_assoc($res);
    $cf          = json_decode($row['scriptcustomfields'], true);

    foreach ($cf as $key => $value) {
        if ($key == $fieldname) {
            unset($cf[$key]);
        }
    }

    mysql_query("update projects set scriptcustomfields = '" . mysql_real_escape_string(json_encode($cf)) . "' where projectid = '$pid'");



    if ($fields == 'dropdown'){
        $res      = mysql_query("SELECT * from scripts where scriptid = '$scriptid'");
        $row      = mysql_fetch_assoc($res);
        $savef    = json_decode($row['savedfields'], true);


        foreach ($savef[$fieldname] as $key => $value) {
            unset($savef[$fieldname]);
        }
        mysql_query("update scripts set savedfields = '" . mysql_real_escape_string(json_encode($savef)) . "' where scriptid = '$scriptid'");

    }
    
   
}