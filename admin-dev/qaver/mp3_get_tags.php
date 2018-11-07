<?php

function mp3_get_tags($file) {
	$id3_tags = array();
	$versions = array("00" => "2.5", "01" => "x", "10" => "2", "11" => "1");
	$layers = array("00" => "x", "01" => "3", "10" => "2", "11" => "1");
	$bitrates = array(
		'V1L1'=>array(0,32,64,96,128,160,192,224,256,288,320,352,384,416,448),
	    'V1L2'=>array(0,32,48,56, 64, 80, 96,112,128,160,192,224,256,320,384),
	    'V1L3'=>array(0,32,40,48, 56, 64, 80, 96,112,128,160,192,224,256,320),
	    'V2L1'=>array(0,32,48,56, 64, 80, 96,112,128,144,160,176,192,224,256),
	    'V2L2'=>array(0, 8,16,24, 32, 40, 48, 56, 64, 80, 96,112,128,144,160),
	    'V2L3'=>array(0, 8,16,24, 32, 40, 48, 56, 64, 80, 96,112,128,144,160),
    );  
    $sample_rates = array(
		'1'   => array(44100,48000,32000),
        '2'   => array(22050,24000,16000),
        '2.5' => array(11025,12000, 8000),
    );
	$handle = fopen($file, "r");
	if (!$handle) {
		return;
	} else {
		$tags_array = array( 
							'TIT2' => 'title', 'TALB' => 'album', 'TPE1' => 'artist',
							'TYER' => 'year', 'COMM' => 'comment', 'TCON' => 'genre', 'TLEN' => 'length',
							'TT2' => 'title', 'TAL' => 'album', 'TP1' => 'artist',
							'TYE' => 'year', 'COM' => 'comment', 'TCO' => 'genre', 'TLE' => 'length'
							);
		$null = chr(0);
		$data = fread($handle, 10);
		if (substr($data,0,3) == 'ID3') {
			$mb_size = ord(substr($data,6,1));
			$kb_size = ord(substr($data,7,1));
			$byte128_size = ord(substr($data,8,1));
			$byte_size = ord(substr($data,9,1));
			$total_size = ($mb_size * 2097152) + ($kb_size * 16384) + ($byte128_size * 128) + $byte_size;
			$data .= stream_get_contents($handle, $total_size + ($footer_flag * 10));
		}
		$bits = null;
		while (!feof($handle)) {
			$data .= stream_get_contents($handle, 10);
			for ($i = 0; $i < strlen($data); $i++)
				$bits .= str_pad(decbin(ord($data[$i])), 8, 0, STR_PAD_LEFT);
			$frame_pos = strpos($bits, "11111111111");
			if ($frame_pos !== false)
			{
				$id3_tags["version"] = $versions[substr($bits, $frame_pos + 11, 2)];
				$id3_tags["layer"] = $layers[substr($bits, $frame_pos + 13, 2)];
				$id3_tags["crc"] = substr($bits, $frame_pos + 15, 1);
				$bitrate_index = bindec(substr($bits, $frame_pos + 16, 4));
				$id3_tags["bitrate"] = $bitrates["V".$id3_tags["version"][0]."L".$id3_tags["layer"]][$bitrate_index];
				$id3_tags["frequency"] = $sample_rates["1"][bindec(substr($bits, $frame_pos + 19, 2))];
				if (preg_match("/^(https?|ftp):\/\//", $file)) {
					$headers = get_headers($file, 1);
					$id3_tags["filesize"] = $headers["Content-Length"];
				} else {
					$id3_tags["filesize"] = filesize($file);
				}
				$bps = ($id3_tags["bitrate"]*1000)/8;
        		$id3_tags["duration"] = ($id3_tags["filesize"] - $total_size) / $bps;
				$id3_tags["formatted_time"] = gmdate("H:i:s", $id3_tags["duration"]);
				break;
			}
		}	
	}
	fclose($handle);
	return($id3_tags);
}
?>