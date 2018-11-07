<?php
    $proj_result = mysql_query("SELECT * FROM projects WHERE projectid = '$pid'");
    $proj_row = mysql_fetch_array($proj_result);
    $cust_flds = json_decode($proj_row['customfields'], true);
    if ($cust_flds)
    {
        foreach ($cust_flds as $key => $value)
        {
            $cust[] = array('name'=> $key, 'value' => $value  );
        }
    }
    $customf = json_encode($cust);



    $script_result = mysql_query("SELECT * FROM scripts WHERE scriptid = '$scriptid'");
    $script_row = mysql_fetch_array($script_result);
    $save_flds = json_decode($script_row['savedfields'], true);
    if ($save_flds)
    {
        foreach ($save_flds as $key => $value)
        {
            $savedfields[] = array('name'=> $key, 'value' => $value  );
        }
    }
    $sfields = json_encode($savedfields);



    $scriptdata_r   =  mysql_query("SELECT * FROM projects WHERE projectid = '$pid'");
    $scriptdata_row = mysql_fetch_array($scriptdata_r);
    $scriptdatadd   = json_decode($scriptdata_row['scriptcustomfields'], true);
    if ($scriptdatadd )
    {
        foreach ($scriptdatadd as $key => $value)
        {
            $scrpdatadd[] = array('name'=> $key, 'value' => $value  );

        }
    }
    $scrpdatadd = json_encode($scrpdatadd);

    // SCRIPT CUSTOM FIELDS
    $scriptcustom_res = mysql_query("SELECT * FROM projects WHERE projectid = '$pid'");
    $scriptcustom_row = mysql_fetch_array($scriptcustom_res );
    $scriptcustom_flds = json_decode($scriptcustom_row['customfields_unmap'], true);
    if ($scriptcustom_flds)
    {
        foreach ($scriptcustom_flds as $key => $value)
        {
            $scriptcust[] = array('name'=> $key, 'value' => $value  );
        }
    }
    $scriptcust = json_encode($scriptcust);

    
?>

 <textarea id="custom-scr"  style="display:none"  name="custom-scr" ><?php echo $customf ?></textarea>
 <textarea id="sfields-scr" style="display:none"  name="sfields-scr"  ><?php echo $sfields ?></textarea>
 <textarea id="scrpdatadd"  style="display:none"  name="scrpdatadd"  ><?php echo $scrpdatadd ?></textarea>
<textarea id="scriptcust" style="display:none"  name="scriptcust"  ><?php echo $scriptcust ?></textarea>