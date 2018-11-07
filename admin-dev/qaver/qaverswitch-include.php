<?php
session_start();

include "../../dbconnect.php";
require "../../classes/classes.php";
require "../../classes/records.php";
require "../../classes/lists.php";
require "../../classes/projects.php";
require "../../classes/labels.php";
require "../../classes/S3.php";
include_once '../../phpmailer/PHPMailerAutoload.php';
require "../../classes/mailer.php";
include "../phpfunctions.php";
require "mp3_get_tags.php";
include "qaver.php";
$leadid = $_POST['leadid'];
$projectid = $_POST['projectid'];
$startdate = $_POST["startdate"];
$enddate = $_POST["enddate"];
$record = getrecord($leadid);
$audio = array();
$i = 0;
//$s3 = new S3("AKIAIFNBYO657IIJKOUQ", "w6Q/iJwhRYvS+RR1agf3zQoNrvtaw3T4as7qDpd2");
$s3 = new S3($GLOBAL_S3_ACCESS_KEY[0], $GLOBAL_S3_ACCESS_KEY[1]);
$rclogres = mysql_query("SELECT bucket, prefix, filename FROM recordinglog WHERE projectid = '".$projectid."' AND filename LIKE ('".$leadid."%') AND epoch >= ".strtotime($startdate." 00:00:00")." AND epoch <= ".strtotime($enddate." 23:59:59")."");
while ($rclogrow = mysql_fetch_assoc($rclogres)) {
	$s3bucket = $rclogrow["bucket"]; 
	$filename = str_replace("wav", "mp3", $rclogrow["filename"]);
	$prefix = $rclogrow["prefix"].$projectid."/".$filename;
	$audio[$i."|".$s3bucket] = $s3->getBucket($s3bucket,$prefix);
	$i++;
}

$cta = 0;
$recordingcount_extend = 0;  
$setcount_extend = $_POST["id"] + 5;
foreach (array_reverse($audio) as $keyrecordings => $valrecordings) {
	$bucket = $keyrecordings;
	foreach ($valrecordings as $aud) {
			if ($recordingcount_extend == $setcount_extend) {
			break;
		} else {
			$s3b = explode("|", $bucket);
			
			$m = $cta % 2;
			if ($m = 0) $cl = "tableitem";
			else $cl = "tableitem_";
	        $lc = filesizeformat($aud['size']);
	        $media = $s3->getAuthenticatedURL($s3b[1], $aud['name'], 259200, false, true);
			
	        $id3_tags = mp3_get_tags($media);
			$duration = $id3_tags["formatted_time"];
			
	        $dt = gettimefromname($aud['name']);                                
	        $dti = date("Y-m-d H:i:s",$dt);
			
			echo '<tr class="listrecordings'.$leadid.' '.$cl.'"><td>'.$dti.'</td>
			<td>'.$lc.'</td>
			<td>'.$duration.'</td>';
	        echo '<td width="220" align="center">';
	        if (!isset($_REQUEST['export']) && $act != 'emailtoclient')
	        {
				echo '<audio id="player" controls preload="none">
				<source src="'.$media.'" type="audio/mpeg">
			</audio>';
	        }
	        echo '</td>
			<td><a id="wavfile" href="'.$media.'" target="_blank">Download</a></td>
			</tr>';
			$cta++;
				$recordingcount_extend++;
			}
		}
	}
echo '<tr><td colspan="5">
<div class="show_more_main" id="show_more_main'.$setcount_extend.'">
<span id="'.$setcount_extend.'" class="show_more">Show more</span>

    <span class="loading" style="display: none;"><span class="loading_txt">Loading....</span></span>
</div>
</td></tr>';
?>