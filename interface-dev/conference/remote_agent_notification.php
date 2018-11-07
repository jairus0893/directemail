<?php
// Expects: 
//  $confchan - conference channel
//  $confserver - conference server
//

  // require_once('phpagi-asmanager.php');
  // Already included by transfer.php

  $asm = new AGI_AsteriskManager();
  if($asm->connect($confserver,'bcpami','cd84b1ade73162c123bca44bf398e6e9'))
  {
    $peer = $asm->command("originate Local/$notify_extension application ChanSpy $confchan,qw");

    // print_r($peer);

    $asm->disconnect();
  }
?>
