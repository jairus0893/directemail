<?php
if ($projrow['dialmode'] == "progressive") {
    echo "<script>$('#queuepreviewdisplay').show();</script>";
}
?>
<script>							
function applycustomizeview(projectid, userid) 
{																
    var vf = $("input.vfs").serialize();								
    var permission = $("select#enabling").val();								
    $.ajax({																  		
        url: "admin.php?act=applycustomizeview&"+vf+"&projectid="+projectid+"&userid="+userid+"&permission="+permission,							  		
        success: function(data){}								
    });															
}							
function checkboxapplycustomizeview(projectid, userid) 
{																
    var vf = $("input.vfs").serialize();								
    $.ajax({																  		
        url: "admin.php?act=checkboxapplycustomizeview&"+vf+"&projectid="+projectid+"&userid="+userid,							  		
        success: function(data){}								
    });															}						
</script>						
<style>							
#cview {														    
    width:100%;														    
    height:150px;														
}														
.vftab {														    
    float:left;														    
    width:30%;														
}														
.vftab .ck {														    
    text-align:center;														
}							
.clear {								    
    clear:both														
}						
</style>
<div id="queuepreviewdisplay" style="display:none">                                         												
<h3>Queue Preview Settings</h3>
<br/>												
<div id="cview">

<?php
$customizeViewArray = array(
    'epoch_timeofcall' => array(
        'Date',
        1
    ),
    'status' => array(
        'QA status',
        1
    ),
    'dispo' => array(
        'Disposition',
        1
    ),
    'assigned' => array(
        'Agent',
        1
    ),
    'phone' => array(
        'Phone',
        1
    ),
    'altphone' => array(
        'Alt Phone',
        0
    ),
    'mobile' => array(
        'Mobile',
        0
    ),		
    'title' => array(        
        'Title',        
        1    
    ),    
    'cname' => array(
        'Name',
        1
    ),
    'cfname' => array(
        'Firstname',
        0
    ),
    'clname' => array(
        'Lastname',
        0
    ),
    'company' => array(
        'Company',
        1
    ),		
    'industry' => array(        
        'Industry',        
        1    
    ),    
    'email' => array(
        'Email',
        1
    ),
    'address1' => array(
        'Address1',
        0
    ),
    'address2' => array(
        'Address2',
        0
    ),
    'suburb' => array(
        'Suburb',
        0
    ),
    'city' => array(
        'City',
        1
    ),
    'state' => array(
        'State',
        0
    ),
    'country' => array(
        'Country',
        0
    ),
    'zip' => array(
        'Postcode',
        0
    ),
    'epoch_callable' => array(
        'Date Set',
        0
    )
);
$pid                          = $projrow['projectid'];
$uid                          = $_SESSION['uid'];
$getQueuePreviewCustomizeView = mysql_query("SELECT * FROM uiopt WHERE project_id = '$pid' AND user_id = '$uid' AND config = 'QueuePreviewFields' ORDER BY ts DESC LIMIT 1");
$getQueuePreviewIsEnabled     = mysql_query("SELECT * FROM uiopt WHERE project_id = '$pid' AND user_id = '$uid' AND config = 'QueuePreviewEnabled' ORDER BY ts DESC LIMIT 1");
if (mysql_num_rows($getQueuePreviewCustomizeView) > 0) {
    $getdata = array();
    while ($getrow = mysql_fetch_assoc($getQueuePreviewCustomizeView)) {
        $getdata = $getrow;
    }
    $jsonintoarray = json_decode($getdata["value"]);
    foreach ($jsonintoarray as $uioptkey => $uioptval) {
        $keys .= $uioptkey . ", ";
    }
    $keys = rtrim($keys, ", ");
    $rct  = 0;
    foreach ($customizeViewArray as $customizekey => $customizearv) {
        if ($rct == 7) {
            echo '</table>';
            $rct = 0;
        }
        if ($rct == 0) {
            echo '<table class="vftab"><tr><th><b>Column</b></th><th><b>Show</b></th></tr>';
        }
        $compare = strpos($keys, $customizekey);
        if ($compare === false) {
            echo "<tr><td class=\"label\">" . $customizearv[0] . "</td><td class=\"ck\"><input type=\"checkbox\" onchange=\"checkboxapplycustomizeview('$pid','$uid')\" name=\"viewfields[]\" class=\"vfs\" value=\"$customizekey\"></td></tr>";
        } else {
            foreach ($jsonintoarray as $jsonkey => $jsonarv) {
                $checked = '';
                if ($jsonarv[1] == 1)
                    $checked = "checked";
                if ($customizearv[0] == $jsonarv[0]) {
                    echo "<tr><td class=\"label\">" . $jsonarv[0] . "</td><td class=\"ck\"><input type=\"checkbox\" onchange=\"checkboxapplycustomizeview('$pid','$uid')\" name=\"viewfields[]\" class=\"vfs\" value=\"$jsonkey\" checked=\"$checked\"></td></tr>";
                }
            }
        }
        $rct++;
    }
    echo '</table>';
} else if (mysql_num_rows($getQueuePreviewCustomizeView) == 0) {
    $rct = 0;
    foreach ($customizeViewArray as $key => $arv) {
        if ($rct == 7) {
            echo '</table>';
            $rct = 0;
        }
        if ($rct == 0) {
            echo '<table class="vftab"><tr><th>Column</th><th>Show</th></tr>';
        }
        $checked = '';
        if ($arv[1] == 1)
            $checked = "checked";
        echo "<tr><td class=\"label\">" . $arv[0] . "</td><td class=\"ck\"><input type=\"checkbox\" onchange=\"checkboxapplycustomizeview('$pid','$uid')\" name=\"viewfields[]\" class=\"vfs\" value=\"$key\" checked=\"$checked\"></td></tr>";
        $rct++;
    }
    echo '</table>';
}
echo '<div class="clear"></div>';
if (mysql_num_rows($getQueuePreviewIsEnabled) > 0) {
    $getdata = array();
    while ($getrow = mysql_fetch_assoc($getQueuePreviewIsEnabled)) {
        $getdata = $getrow;
    }
    if ($getdata["value"] == "Y") {
        $enablinglist .= '<option value="Y">Enable Customize View</option>													<option value="N">Disable Customize View</option>';
    } else {
        $enablinglist .= '<option value="N">Disable Customize View</option>													<option value="Y">Enable Customize View</option>';
    }
    echo "<br/><select id=\"enabling\" onchange=\"applycustomizeview('$pid','$uid')\">" . $enablinglist . "</select><br/>";
} else if (mysql_num_rows($getQueuePreviewIsEnabled) == 0) {
    echo "<br/><select id=\"enabling\" onchange=\"applycustomizeview('$pid','$uid')\">											<option value=\"N\">Disable Customize View</option>											<option value=\"Y\">Enable Customize View</option>										</select><br/>";
}
?>
</div>
</div>
<br/><br/><br/><br/><br/><br/><br/><br/>