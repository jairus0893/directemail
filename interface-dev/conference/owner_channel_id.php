<?php
  // Expects:
  // $host = '10.0.1.95';
  // $search_number = '1000';
require_once("../../PHPAGI/phpagi-asmanager.php");

function ast_owner_channel_id($_host, $_search_number)
{
  $asm = new AGI_AsteriskManager();
  if($asm->connect($_host,'bcpami','cd84b1ade73162c123bca44bf398e6e9'))
  {
    $sip_channels = $asm->command("sip show channels");
    $sip_channel_callids = array();
    foreach(explode("\n", $sip_channels['data']) as $line)
    {
    //      if (preg_match("/^$search_peer\s*$search_number\s*(\S*)\s*/", $line, $matches))
      if (preg_match("/^\S*\s*$_search_number\s*(\S*)\s*/", $line, $matches))
        $sip_channel_callids[] = $matches[1];
    }

    // print_r($sip_channel_callids);

    $owner_channel_ids = array();
    $sip_channel_detail = $asm->command("sip show channel " . $sip_channel_callids[0]);

    foreach(explode("\n", $sip_channel_detail['data']) as $line)
    {
      if (preg_match("/\s*Owner channel ID:\s*(\S*)/", $line, $matches))
        $owner_channel_ids[] = $matches[1];
    }

    // print_r($owner_channel_ids);

    $asm->disconnect();

    return $owner_channel_ids[0];
    }
}
?>
