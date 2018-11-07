<?php
	include_once("../../dbconnect.php");
	require_once("../../PHPAGI/phpagi-asmanager.php");

//	header('Content-Type: application/json');

    $host = $argv[1];
    $softphone_channel = $argv[2];

    $asm = new AGI_AsteriskManager();
    if($asm->connect($host,'bcpami','cd84b1ade73162c123bca44bf398e6e9'))
    {
        $core_show_channel = $asm->command("core show channel $softphone_channel");
        $channel_info = array();

    foreach(explode("\n", $core_show_channel['data']) as $line)
    {
    //      if (preg_match("/^\S*\s*$search_number\s*(\S*)\s*/", $line, $matches))

        if (preg_match("/^SIPDOMAIN=(\S*)$/", $line, $matches))
            $channel_info['confserver'] = $matches[1];

        if (preg_match("/^\s*Elapsed Time\: (\S*)$/", $line, $matches))
            $channel_info['duration'] = $matches[1];

        if (preg_match("/^\s*Frames in\: (\S*)$/", $line, $matches))
            $channel_info['frames_in'] = $matches[1];

        if (preg_match("/^\s*Frames in\: (\S*)$/", $line, $matches))
            $channel_info['frames_out'] = $matches[1];
    }

    print_r(json_encode($channel_info));

    $asm->disconnect();
    }

?>