<?php


require "../../../classes/S3.php";
include_once "../../../dbconnect.php";
$s3				= new S3($GLOBAL_S3_ACCESS_KEY[0], $GLOBAL_S3_ACCESS_KEY[1]);
$s3bucket      	= "bcclientuploads";
$projectid 		= $_REQUEST['pid'];
$bcid		 	= $_REQUEST['bcid'];
$tmp 			= $_FILES['photo-path']['tmp_name'];
$act 			= $_REQUEST['act'];

$extension = pathinfo($_FILES["photo-path"]["name"], PATHINFO_EXTENSION);
$newname = $_POST['nameval'];
move_uploaded_file($_FILES["photo-path"]["tmp_name"], $newname.".".$extension);
$newname =  $newname.".".$extension;


if ( $act == 'emailattachment' ) {
    $templateid 	= $_REQUEST['tid'];
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
				// if (strlen($attach) > 0) {
				// 	$aq = "update templates set attachments = '$attach,$newname' where templateid = '$templateid'";
				// } else {
				// 	$aq = "update templates set attachments = '$newname' where templateid = '$templateid'";
				// }
				// mysql_query($aq);
				
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

}elseif ( $act == 'signatureattachment' ){

	$sigid		 	= $_REQUEST['sigid'];
	$imageupload	= $_REQUEST['imageupload'];
	
	if ($imageupload  == 'true'){


		$prefix			= $bcid . "/emailsignature/" . $projectid . "/" . $sigid . "/" . $newname;
		if( strlen( $newname ) > 0 ) {
			$getprefix  = $bcid . "/emailsignature/" . $projectid . "/" . $sigid;
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
					$res = mysql_query("SELECT * from signatures where sigid = '$sigid'");
					$row = mysql_fetch_array($res);
					$attach = $row['signature_images'];
					if (strlen($attach) > 0) {
						$aq = "update signatures set signature_images = '$attach,$newname' where sigid = '$sigid'";
					} else {
						$aq = "update signatures set signature_images = '$newname' where sigid = '$sigid'";
					}
					mysql_query($aq);
					
					$s3file = "http://".$s3bucket.".s3.amazonaws.com/".$prefix;
					$s3prefix  = $bcid . "/emailsignature/" . $projectid . "/" . $sigid;
				
					echo '{success:true, file:'.json_encode($newname).', url: '.json_encode($s3file).', s3prefix: '.json_encode($s3prefix).'}' ;
				} else {
					// echo "<script>parent.cmess('Upload Failed.');</script>";
					echo "{'success': false, 'error': 'Upload Failed'}";
				}
			}
		} else {
			echo "{'success': false, 'error': 'Upload Failed. Please select a file.'}";
		}
	
	}else{

		// Read image path, convert to base64 encoding
		$imageData = base64_encode(file_get_contents($tmp));

		// Format the image SRC:  data:{mime};base64,{data};
		$src = 'data: '.mime_content_type($tmp).';base64,'.$imageData;
		echo '{success:true, localsrc: '.json_encode($src).'}' ;
	}


	


}


