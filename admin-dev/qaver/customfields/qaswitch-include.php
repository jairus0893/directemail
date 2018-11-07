<?php

    if($act == 'getcustomdata'){
        if ($_REQUEST['projectid']){
            $render = rendercustomdata($_REQUEST['projectid'], $_REQUEST['cvfields']);
        }
        echo $render;
        exit();
    }
    /***************************/
    /* ADDED BY Vincent Castro */
    /***************************/
    if($act == 'showagents'){
        $pid = $_REQUEST['projectid'];
        $bcid = getbcidbypid($pid);
        $getAgents = getagentids($bcid);
        $assigned = $_REQUEST['assigned'];
        ?>
        <tr id="reassign_agent">
            <td colspan="2">Agent:</td>
            <td>
                <select name="assigned" id="reassign_agent_select">
                <?php 
                        foreach($getAgents as $key){
                            if ($assigned == $key['userid']) {
                                $sel = "selected";
                            } else {
                                $sel = "";
                            }
                        ?>
                        <option value="<?php echo $key['userid']; ?>" <?php echo $sel; ?>> <?php echo $agents[$key['userid']] ?> </option>
                        <?php 
                        } 
                    ?>
                </select>
            </td>
        </tr>
        <?php
        exit();
    }
    /***************************/
    /* ADDED BY Vincent Castro */
    /***************************/
    if($act == 'showagentsbulk'){
        $pid = $_REQUEST['projectid'];
        $bcid = getbcidbypid($pid);
        $getAgents = getagentids($bcid);
        ?>
        <label> Agent: </label>
        <select name="assigned" onchange="bulktransfercallback(this)">
            <?php 
                foreach($getAgents as $key){ 
                    /** ACTIVE AGENTS ONLY **/
                    if ($key['roleid'] == 3 && $key['active'] == 1) {
            ?>
                        <option value="<?=$key['userid']?>" > <?=$agents[$key['userid']]?> </option>
            <?php 
                    }
                } 
            ?>
        </select>
        <?php
        exit();
    }
    /***************************/
    /* ADDED BY Vincent Castro */
    /***************************/
    if ($act == 'transferagentsbulk') {

        $dib = $_REQUEST['leadid'];
        $agentid = $_REQUEST['agentid'];

        foreach ($dib as $key => $value) {
            mysql_query("update leads_done set assigned = $agentid, dispo = 'ScheduledCallback' where leadid = $value");
        }
        exit();
    }