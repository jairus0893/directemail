<?php



$val  = $_POST['values'];
$tid  = $_REQUEST['tid'];
$act  = $_REQUEST['act'];

include "../../../dbconnect.php";





if ($act == 'geteditormsg') {  
    
    $query              = "select * from templates where templateid = '$tid'";
    $query2             = mysql_query($query);
    $row                = mysql_fetch_array($query2);
    $template_body      = $row['template_body'];


    echo $template_body;

}else if ($act == 'updateeditor') {

    // mysql_query("Update templates set template_body = '.mysql_real_escape_string($val)' where templateid = '$tid'");
    $update = "update templates set template_body = '".mysql_real_escape_string($val)."' where templateid = '$tid'";
    mysql_query($update) or die(mysql_error());
    exit;

    echo 'Successfully Updated';

}else if ($act == 'updatesignature') {

    mysql_query("Update templates set sigid = '$val' where templateid = '$tid'");
    exit;

    echo 'Successfully Updated';


}else if ($act == 'getcurrentsignature') {



    $query              = "select templateid, signature_name From templates Inner Join signatures ON templates.sigid = signatures.sigid where templateid = '$tid'";
    $query3             = mysql_query($query);
    $row                = mysql_fetch_array($query3);
    $sig_name           = $row['signature_name'];

    echo $sig_name;
}






