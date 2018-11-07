<?php


require "../../../classes/S3.php";
include_once "../../../dbconnect.php";
$s3				= new S3($GLOBAL_S3_ACCESS_KEY[0], $GLOBAL_S3_ACCESS_KEY[1]);
$s3bucket      	= "bcclientuploads";
$projectid 		= $_REQUEST['pid'];
$bcid		 	= $_REQUEST['bcid'];
$templateid 	= $_REQUEST['tid'];

// $newname 			= $_FILES['photo-path']['name'];

$tmp 			= $_FILES['photo-path']['tmp_name'];




$extension = pathinfo($_FILES["photo-path"]["name"], PATHINFO_EXTENSION);
$newname = $_POST["name-file"];
 
move_uploaded_file($_FILES["photo-path"]["tmp_name"], $newname.".".$extension);




// $oldname =  $_FILES["photo-path"]["name"]."<br/>";
$newname =  $newname.".".$extension;


// echo '{success:true, file:'.json_encode($newname).'}';

	$prefix			= $bcid . "/attachments/" . $projectid . "/" . $templateid . "/" . $newname;
	if( strlen( $newname ) > 0 ) {
		$getprefix  = $bcid . "/attachments/" . $projectid . "/" . $templateid;
	    $contents   = $s3->getBucket($s3bucket, $getprefix);
		foreach ($contents as $content) {
			$splitname = explode("/", $content['name']);
			$filename .= $splitname[4].",";
		}
		$pos = strstr($filename, $newname);
		if ( $pos == true ) {
            // echo "<script>parent.cmess('Upload Failed. Please change a different filename.');</script>";
            echo "{'success': false, 'error': 'Upload Failed. Please change a different filename.'}";
		} else {
			if( $s3->putObjectFile( $tmp, $s3bucket, $prefix, S3::ACL_PUBLIC_READ ) ) {
				$res = mysql_query("SELECT * from templates where templateid = '$templateid'");
				$row = mysql_fetch_array($res);
				$attach = $row['attachments'];
				if (strlen($attach) > 0) {
					$aq = "update templates set attachments = '$attach,$newname' where templateid = '$templateid'";
				} else {
					$aq = "update templates set attachments = '$newname' where templateid = '$templateid'";
				}
				mysql_query($aq);
				
				$s3file = "http://".$s3bucket.".s3.amazonaws.com/".$prefix;
              
                echo '{success:true, file:'.json_encode($newname).'}';
			} else {
                // echo "<script>parent.cmess('Upload Failed.');</script>";
                echo "{'success': false, 'error': 'Upload Failed'}";
			}
		}
	} else {
        echo "{'success': false, 'error': 'Upload Failed. Please select a file.'}";
	}

// echo 'test';