<?php




require "../../../classes/S3.php";
include_once "../../../dbconnect.php";

$pid 		    = $_REQUEST['pid'];
$bcid		 	= $_REQUEST['bcid'];
$templateid 	= $_REQUEST['tid'];


$tq = "select * from templates where templateid = '$templateid'";
$isthere = mysql_query($tq);
$trow = mysql_fetch_array($isthere);

$attachments = split(",",$trow['attachments']);

$images = array();

$cts3 = 0;
$s3         = new S3($GLOBAL_S3_ACCESS_KEY[0], $GLOBAL_S3_ACCESS_KEY[1]);
$s3bucket   = "bcclientuploads";
$prefix     = $bcid . "/attachments/" . $pid . "/" . $templateid;
$contents   = $s3->getBucket($s3bucket, $prefix);
foreach ($contents as $content) {
    $splitname = explode("/", $content['name']);
    $filenamearray[] .= $splitname[4];
    $filename = $splitname[4];
    $fileurl = "http://".$s3bucket.".s3.amazonaws.com/".$prefix."/".$filename;

    // if(!preg_match('/\.(jpg|gif|png)$/', $fileurl)) continue;
    $size = filesize($fileurl);
    $lastmod = filemtime($fileurl)*1000;
    $images[] = array('name'=>$filename, 'size'=>$size, 
            'lastmod'=>$lastmod, 'url'=>$fileurl);
    ?>
   
 
    <?
    $cts3++;

}

foreach ($attachments as $attachment) {
    $uploadfilename = trim($attachment);
    if (in_array($uploadfilename, $filenamearray)) {
        //do nothing
    } else {
        if (strlen($uploadfilename) > 0) {

        $fileurl = "../attachments/".$uploadfilename;
        
        // if(!preg_match('/\.(jpg|gif|png)$/', $fileurl)) continue;
        $size = filesize($fileurl);
        $lastmod = filemtime($fileurl)*1000;
        $images[] = array('name'=>$uploadfilename, 'size'=>$size, 
            'lastmod'=>$lastmod, 'url'=>$fileurl);
            ?>
        
            <?
            $ct++;
        }
    }
}


$o = array('images'=>$images);
echo json_encode($o);



?>