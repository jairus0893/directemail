<?php
$pid  = $_REQUEST['pid'];
$act2 = $_REQUEST['act2'];


if ($act2 == 'canceltemplate') {
    // include "../dbconnect.php";
    $templateid = $_REQUEST['tid'];
	mysql_query("DELETE from templates where templateid = '$templateid'");
	exit;
    
}
if ($act2 == 'createnew') {
    mysql_query("insert into templates set projectid = '$pid', template_name = 'New Template'");
    $templateid = mysql_insert_id();
} else {
    $templateid = $_REQUEST['templateid'];
}

$tq      = "select * from templates where templateid = '$templateid'";
$isthere = mysql_query($tq);
$trow    = mysql_fetch_array($isthere);
$body    = $trow['template_body'];
if ($trow['sigid'] == 0)
    $sdnone = 'Selected';
$sigdrop = '<option value="0" ' . $sdnone . '>None</option>';
$sigs    = getdatatable("signatures where bcid = '$bcid'", 'sigid');
foreach ($sigs as $sig) {
    $selected = '';
    if ($sig['sigid'] == $trow['sigid'])
        $selected = 'Selected';
    $sigdrop .= '<option value="' . $sig['sigid'] . '" ' . $selected . '>' . $sig['signature_name'] . '</option>';
}
$attachments = split(",", $trow['attachments']);
$res         = mysql_query("SELECT * from statuses where projectid in ('0','$pid') ORDER BY statusname");
$drop .= '<select name="template_disposend" id="template_disposend">';

$drop .= '<option value="0">Inactive</option>';

while ($row = mysql_fetch_assoc($res)) {
    if ($row['statusname'] == $trow['disposend'])
        $sel = 'selected = "selected"';
    else
        $sel = '';
    $drop .= '<option value="' . $row['statusname'] . '" ' . $sel . '>' . ucfirst($row['statusname']) . '</option>';
}
$drop .= '</select>';
$encoptions = array(
    'none',
    'tls',
    'ssl'
);
$encdrop    = '';
foreach ($encoptions as $enc) {
    $selected = '';
    if ($trow['mailencryption'] == $enc)
        $selected = 'selected';
    $encdrop .= '<option value="' . $enc . '" ' . $selected . '>' . $enc . '</option>';
}
?>

<style>

  .x-panel-header {
    color: #000000 !important;
    border: 1px solid #abc;
    background: #D3D6FF !important;
}

.x-tab-panel-header, .x-tab-panel-footer {
    background: #D3D6FF !important;
    border-color: #abc !important;
}

</style>
<div class="apptitle">Email Template Editor</div>

<div class="back-btn">
    <input class="btn-extjs" type="button" onclick="manage_persist('<?=$trow['projectid'];?>')" value="Back" />
    <input class="btn-extjs" type="button" onclick="canceltemplate('<?=$templateid;?>', '<?=$trow['projectid'];?>')" value="Delete" />
</div>      

<div style="margin-top: 50px"></div>
<table width="25%" style="float:left; margin-right:10px" cellpadding="0" cellspacing="5" border="0">
        <tr>
            <td>
                <table width="100%">

                    <!--Direct Mailing -->
                    <tr>
                        <td class="title">Delivery Method:</td>
                        <td align="left">
                            <select id="deliverymethod" name="deliverymethod" onchange="changemailmethod(value,'<?=$templateid;?>');">
                                <option value="direct" <?php echo $trow[ 'delivery']=='direct' ? "Selected": "";?>>Direct Email</option>
                                <option value="relay" <?php echo $trow[ 'delivery']=='relay' ? "Selected": "";?>>Using Relay</option>
                            </select>
                        </td>
                    </tr>
                    <!--End -->

                    <tr>
                        <td class="title">Template Name:</td>
                        <td align="left">
                            <input type="text" name="template_name" id="template_name" class="box" value="<?=$trow['template_name'];?>" />
                        </td>
                    </tr>

                    <tr>
                        <td class="title">Email From Name:</td>
                        <td align="left">
                            <input type="text" name="emailfromname" id="emailfromname" class="box" value="<?=$trow['emailfromname'];?>" /> </td>
                    </tr>

                    <tr>
                        <td class="title">Email From Address:</td>
                        <input type="hidden" name="activationcode" id="activationcode" value="<?=$trow['activate_key'];?>" />
                        <td align="left">
                            <input type="text" name="emailfrom" id="emailfrom" class="box" value="<?=$trow['emailfrom'];?>" />
                            <?php if ($trow['activate_key'] != 'ACTIVATED'){  ?>
                                <?php if ($trow['delivery']=='direct') { ?>
                                    <div class="verifyfield">
                                        <input type="button" class="btn-extjs"  id="verifyfrom" onclick="verifyfrommail('<?=$templateid;?>','<?=$trow['emailfrom'];?>')" value="Verify" />
                                    </div>
                                <?php }else{?>
                                    <img src="icons/check.gif" style="margin-top: -37px; margin-left: 92%;" class="check_active" id="check_active">
                                <?php }?>
                            <?php }else{?>
                                    <img src="icons/check.gif" style="margin-top: -37px; margin-left: 92%;" class="check_active" id="check_active">
                            <?php }  ?>

                            <div class="verifyfield">
                                    <input type="button" class="btn-extjs" style="display:none;"  id="verifyfrom" onclick="verifyfrommail('<?=$templateid;?>','<?=$trow['emailfrom'];?>')" value="Verify" />
                            </div>
                            <img src="icons/check.gif" style="margin-top: -37px; margin-left: 92%; display:none;" class="check_active" id="check_active">
                        </td>
                    </tr>

                    <tr>
                        <td class="title">Email CC:</td>
                        <td align="left">
                            <input type="text" name="emailcc" id="emailcc" class="box" value="<?=$trow['emailcc'];?>" />
                        </td>
                    </tr>

                    <tr>
                        <td class="title">Email BCC:</td>
                        <td align="left">
                            <input type="text" name="emailbcc" id="emailbcc" class="box" value="<?=$trow['emailbcc'];?>" />
                        </td>
                    </tr>

                    <tr>
                        <td class="title">Subject:</td>
                        <td align="left">
                            <input type="text" name="template_subject" id="template_subject" class="box" value="<?=$trow['template_subject'];?>" />
                        </td>
                    </tr>

                    <tr>
                        <td class="title">Autosend by Dispo:</td>
                        <td align="left">
                            <?=$drop;?>
                        </td>
                    </tr>

                    <tr>
                        <td class="title">Agent Editable:</td>
                        <td align="left">
                            <select id="editable" name="editable">
                                <option value="1" <?php echo $trow[ 'editable']==1 ? "Selected": "";?>>Yes</option>
                                <option value="0" <?php echo $trow[ 'editable']==0 ? "Selected": "";?>>No</option>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>

        <tr>
            <td>

                <table width="100%" id="advancedemail" <?php if ($trow[ 'delivery'] !='relay' ) { ?> style="display:none"
                    <?php  } ?> >
                        <!--Direct Mailing -->
                        <tr>
                            <td class="title">Mail Encryption:</td>
                            <td align="left">
                                <select name="mailencryption" id="mailencryption">
                                    <?php echo $encdrop;?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="title">Mail Server:</td>
                            <td align="left">
                                <input type="text" name="mailserver" id="mailserver" class="box" value="<?=$trow['mailserver'];?>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="title">Mail Port:</td>
                            <td align="left">
                                <input type="text" name="mailport" id="mailport" class="box" value="<?=$trow['mailport'];?>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="title">Mail User:</td>
                            <td align="left">
                                <input type="text" name="mailuser" id="mailuser" class="box" value="<?=$trow['mailuser'];?>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="title">Mail Password:</td>
                            <td align="left">
                                <input type="password" name="mailpass" id="mailpass" class="box" value="<?=$trow['mailpass'];?>" />
                            </td>
                        </tr>
                        </div>

                </table>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <div class="btn-ext-align">
                    <input type="hidden" name="stored_emailfrom" id="stored_emailfrom" class="box" value="<?=$trow['emailfrom'];?>" >
                    <input class="btn-extjs" type="button" onclick="updatetemplate('<?=$templateid;?>',false)" value="Update" />
                    <input class="btn-extjs" type="button" onclick="updatetemplate('<?=$templateid;?>',true)" value="Test Mail" />
                </div>
            </td>
        </tr>

    </table>

     <table style="float:left; width:50%">
        <tr>
            <td align="left" colspan="2">
            <td>
                <div id="templateeditor"></div>
                <iframe onLoad="editor('<?=$templateid;?>','<?=$bcid;?>','<?=$trow['projectid'];?>')" style="display:none;"></iframe>
            </td>
        </tr>
        
    </table>

    <?php
            $proj_result = mysql_query("SELECT * FROM projects WHERE projectid = '$pid'");
            $proj_row = mysql_fetch_array($proj_result);
            $cust_flds = json_decode($proj_row['customfields'], true);
            if ($cust_flds)
            {
                foreach ($cust_flds as $key => $value)
                {
                    // echo "<li>";
                    // echo "    <a href=\"#\" draggable=\"true\" ondragstart=\"dragmerge(event,'custom-".str_replace(' ', '-', $key)."')\">".$key."</a>";
                    // echo "</li>";
                    $cust[] = array('name'=> $key );
                }
            }
            $customf = json_encode($cust);
        ?>
            
            <textarea id="custom-f" name="custom-f" style="display:none"><?php echo $customf ?></textarea>
