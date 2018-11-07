<?php
session_start();
ini_set("display_errors", 'off');
error_reporting(E_ALL ^ E_DEPRECATED);
date_default_timezone_set($_SESSION['timezone']);
include "../dbconnect.php";
include "phpfunctions.php";
require_once '../classes/classes.php';
require "../classes/S3.php";
require "../classes/lists.php";
$s3			= new S3($GLOBAL_S3_ACCESS_KEY[0], $GLOBAL_S3_ACCESS_KEY[1]);
$s3bucket   = "bcclientuploads";
$bcid        = getbcid();
$act         = $_REQUEST['act'];
$iswin       = $_REQUEST['iswin'];
$target_path = "C:\\xampp\\htdocs\\proActiv\\leads\\";
$target_path = "./leads/";
$duplicate   = 0;
$uleads      = 0;
$dupcheck    = $_REQUEST['dupcheck'];
// SERVER UNIX_TIMESTAMP
$unixtimestamp_res = mysql_query("SELECT UNIX_TIMESTAMP()");
$unixtimestamp_row = mysql_fetch_assoc($unixtimestamp_res);
$unixtimestamp = $unixtimestamp_row["UNIX_TIMESTAMP()"];

if ($act == 'parsemap') {
    extract($_POST);
    //$projres = mysql_query("SELECT projectid from projects where projectname ='$project'");
    //$projrow = mysql_fetch_array($projres);
    $projid   = $_POST['project'];
    $projects = new projects();
    $project  = $projects->findById($projid);
    $cfields  = json_decode($project['customfields'], true);
    $cf       = array();
    foreach ($cfields as $key => $value) {
        $cf[] = $key;
    }
    
    $rower    = $_POST;
    $csv      = fopen($targetp, "r");
    $exclures = mysql_query("SELECT id,phone from lists_exclusion_data where projectid = $projid");
    while ($exrow = mysql_fetch_row($exclures)) {
        $exclusion[$exrow['id']] = $exrow['phone'];
    }
    $phonenumbers       = leadsloadersettings::phonenumbers();
    $dups               = leadsloadersettings::duplicatecheck();
    $duplist            = new duplicatecheck();
    $duplist->projectid = $projid;
    $duplist->listid    = $listid;
    $duplist->bcid      = $bcid;
    $duplist->preprop($dupcheck);
    $duplicates = array();
    $excluded   = 0;
    while ($data = fgetcsv($csv, 1000, ",")) {
        $n = 0;
        
        $tdata = array('listid' => $listid);
        $cdata = array();
        $dtct  = count($data);
        //$tquery = "INSERT into leads_raw (leadtype, listid, $gquery) values ('$leadtype', '$listid', ";
        while ($n < $dtct) {
            $assfield = $rower['field' . $n];
            if ($assfield != 'nomap' && !in_array($assfield, $cf, true)) { // MOD BY VINCENT CASTRO
                if (in_array($assfield, $phonenumbers, true)) { // MOD BY VINCENT CASTRO
                    $data[$n] = str_replace(" ", "", $data[$n]);
                    $data[$n] = str_replace("(", "", $data[$n]);
                    $data[$n] = str_replace(")", "", $data[$n]);
                    $data[$n] = str_replace("-", "", $data[$n]);
                    $data[$n] = ereg_replace('[^0-9]+', '', $data[$n]);
					
                }
				
                $tdata[$rower['field' . $n]] = $data[$n];
            } elseif (in_array($assfield, $cf)) {
                $cdata[$assfield] = $data[$n];
            }
            $n++;
        }
        //check exclusion
        if (in_array($tdata['phone'], $exclusion)) {
            $excluded++;
        } else {
            //check duplicate in list
            if ($tdata['phone'] == "" || empty($tdata['phone'])) {
            	
            } else {
            	if ($dupcheck == 'nocheck') {
	                $genid = leads::add($tdata);
	                if (count($cdata) > 0) {
	                    customdata::add($genid, $cdata);
	                }
	                $uleads++;
	            } else {
	                $isdup = false;
	                foreach ($dups as $checkthis) {
	                    if ($duplist->dupcheck($checkthis, $tdata[$checkthis])) {
	                        $isdup        = true;
	                        $duplicates[] = $tdata;
	                        $duplicate++;
	                    } else {
	                        $duplist->addin($checkthis, $tdata[$checkthis]);
	                    }
	                }
	                
	                //end duplicate check
	                if (!$isdup) {
	                    $genid = leads::add($tdata);
	                    if (count($cdata) > 0) {
	                        customdata::add($genid, $cdata);
	                    }
	                    $uleads++;
	                }
	            }
            }
        }
    }
    
    //$duplicate = count($duplicates);
    
    echo "Loaded $uleads records<br>";
    echo "$excluded records excluded<br>";
    foreach ($duplicates as $datacsv){
		$numItems = count($datacsv);
		$i = 0;
		foreach ($datacsv as $key => $value) {
			if(++$i === $numItems) {
				$newCSV .= $value."\n";
			} else {
				$newCSV .= $value.",";
			}
		}
	}
    $dupfile = "leads/" . $listid . "_dups_" . $unixtimestamp . ".csv";
	$prefix	= $bcid . "/" . $dupfile;
	$s3->putObjectString( $newCSV, $s3bucket, $prefix, S3::ACL_PUBLIC_READ );
	$s3file = "http://".$s3bucket.".s3.amazonaws.com/".$prefix;
    if ($dupcheck == 'nocheck') {
        echo "Duplicates were not checked <br>";
    } else
        echo $duplicate . " duplicates found <br> Click <a href=\"".$s3file."\" target=\"_blank\">here to download duplicates</a>";
    mysql_query("update lists set dupscount = $duplicate, listcount = $uleads where listid = '$listid'");

	phplog_on();
	phplog("$bcid-$projid $targetp $listid $leadtype DUPCHECK:$dupcheck +$uleads -$excluded *$duplicate");
	exit;
}
function parsecsv($path, $targetp)
{
    
    //echo $path.'<br>';
    $leadtype = $_REQUEST['leadtype'];
    $csv      = fopen($targetp, "r");
    $data     = fgetcsv($csv, 0, ",");
    $z        = 0;
    $ct       = count($data);
    $li       = $_REQUEST['listid'];
    $pr       = $_REQUEST['projects'];
    $projects = new projects();
    $project  = $projects->findById($pr);
    $cfields  = json_decode($project['customfields'], true);
    
    $dupcheck  = $_REQUEST['dupcheck'];
    $field[$z] = $data[$z];
    $iswin     = $_REQUEST['iswin'];
    echo '<style>
	    		#loading {
				    z-index:11; 
				    position:absolute;
				    top:0px;
				    left:0px;
				    text-align:center;
				}
	    	</style>
		    <div class="entryform" style="width:300px; height:350px">
		    <div id="loading" style="display:none">    
		        <img style="margin-top:130px;" src="loading_big.gif" alt="loading" />             
		    </div>
            <title>Field Mapping</title>
            <form name="mapping" id="mapping" method="post" action="' . $_SERVER['PHP_SELF'] . '">
	<input type="hidden" name="act" value="parsemap"><input type="hidden" name="leadtype" value="' . $leadtype . '">
	<input type="hidden" name="dupcheck" value="' . $dupcheck . '">
	<input type="hidden" name="targetp" value="' . $targetp . '">
	<input type="hidden" name="listid" value="' . $li . '">
	<input type="hidden" name="project" value="' . $pr . '">';
    
    if ($iswin == '1')
        echo '<input type=hidden name=iswin value="1">';
    echo '
        <div id="respmessage"></div>    
        <table id="mappingtable" width="100%"><tr><td class="center-title tableheader"><h3>CSV Column</h3></td><td class="center-title tableheader"><h3>Field</h3></td></tr>';
    $fldres = mysql_query("SELECT cname, cfname, clname, title, company, address1, address2, suburb, city, state, country, zip, phone, altphone, comments, industry, sic, email, positiontitle, mobile,dispo from leads_raw limit 1");
    $fldct  = mysql_num_fields($fldres);
    
    while ($z < $ct) {
        $y = 0;
        
        echo "<tr><td>" . $data[$z] . ":</td><td><select name=\"field$z\"><option value=\"nomap\">No Mapping</option>";
        while ($y < $fldct) {
            $fld = mysql_field_name($fldres, $y);
            if ($fld == 'zip') {
                echo '<option value="' . $fld . '">Postcode</option>';
            } elseif ($fld == 'cname') {
                echo '<option value="' . $fld . '">Name</option>';
            } elseif ($fld == 'clname') {
                echo '<option value="' . $fld . '">LastName</option>';
            } elseif ($fld == 'cfname') {
                echo '<option value="' . $fld . '">FirstName</option>';
            } elseif ($fld == 'positiontitle') {
                echo '<option value="' . $fld . '">Position Title</option>';
            } else {
                echo "<option value=\"" . $fld . "\">" . ucfirst($fld) . "</option>";
            }
            $y++;
        }
        foreach ($cfields as $field => $value) {
            echo "<option value=\"" . $field . "\">" . ucfirst($value) . "</option>";
        }
        echo "</select></td></tr>";
        $z++;
    }
    echo "
            
        </table>    
        <input type=\"hidden\" value=\"" . $ct . "\" name=\"fieldcount\">
        
        </form><br>
        <div class=buttons style=\"position:relative\">
        <input type=\"button\" value=\"Next\" onclick=\"submitmaps3()\">
        </div>
        </div>";
    
}

if ($act == 'listupload') {
	$name      	 = $_FILES['csvfile']['name'];
	$tmp 		 = $_FILES['csvfile']['tmp_name'];
	$listid      = $_REQUEST['listid'];
    $listdescription = $_REQUEST['listdescription'];
    $projectid   = $_REQUEST['projects'];
	$iswash      = $_REQUEST['washdate'] != "" ? 1 : 0;
    $washdate = $_REQUEST['washdate'];
	
    $r           = mysql_query("SELECT * FROM lists WHERE listid = '" . $listid . "' AND active = 1 AND is_deleted = 0");
    $ro          = mysql_num_rows($r);
    $lrow        = mysql_fetch_assoc($r);
    
	$getprefix  = $bcid . "/listupload/" . $projectid;
    $contents   = $s3->getBucket($s3bucket, $getprefix);
	foreach ($contents as $content) {
		$splitname = explode("/", $content['name']);
		$filename .= $splitname[3].",";
	}
	
	if ( strlen($name) > 0 ) {
		if ( !is_numeric($checkcsvdata[0]) ) {
			if ($ro == 0) {	
		        $datenow = date("Y-m-d");
		        mysql_query("insert into lists set listid = '$listid', listdescription = '$listdescription', projects = '$projectid', datecreated = '$datenow', 
		        bcid = '$bcid', iswash = '$iswash', washdate = '".strtotime($washdate)."', filename = '$name'");
		    	$listsid = mysql_insert_id();
		    	$prefix = $bcid . "/listupload/" . $projectid . "/" . $listsid . "|" . str_replace(" ", "", $name);
				if( $s3->putObjectFile( $tmp, $s3bucket, $prefix, S3::ACL_PUBLIC_READ ) ) {
					$s3file = "http://".$s3bucket.".s3.amazonaws.com/".$prefix;
					parsecsv($name, $s3file);
				} else {
					echo "<script>$('.ui-dialog').remove();alert('There was an error importing the file, please try again!');listMenu('managelist');</script>";
				}
			} else {
		        if ($lrow['bcid'] != $bcid) {
					echo "<script>$('.ui-dialog').remove();alert('The specifiedListId cannot be used in this account, please use a different ListId.');listMenu('managelist');</script>";
		        }
		    }
		} else {
			echo "<script>$('.ui-dialog').remove();alert('No header. Please try again.');listMenu('managelist');</script>";
		}
	} else {
		echo "<script>$('.ui-dialog').remove();alert('Please select a file.');listMenu('managelist');</script>";
	}
}
if ( $act == 'exclusionupload' ) {
    $name      	 = $_FILES['csvfile']['name'];
	$tmp 		 = $_FILES['csvfile']['tmp_name'];
    $listid      = $_REQUEST['exclusionlistid'];
    $projectid   = $_REQUEST['projects'];
	
	if ( strlen($projectid) > 0  && strlen($listid) > 0 ) {
		$listids_arr = lists::findbyProjectId($projectid, TRUE, false);
	    $listids     = array();
	    foreach ($listids_arr as $lis) {
	        $listids[$lis] = "'" . $lis . "'";
	    }
	    $aglist           = 'agentgenerated' . $projectid;
	    $listids[$aglist] = "'" . $aglist . "'";
	    $lstr             = implode(",", $listids);
		
		$getprefix  = $bcid . "/exclusionlistupload/" . $projectid;
	    $contents   = $s3->getBucket($s3bucket, $getprefix);
		foreach ($contents as $content) {
			$splitname = explode("/", $content['name']);
			$filename .= $splitname[4].",";
		} 
		
	    if ( strlen($name) > 0 ) {
	    	$checkcsvfile   = fopen($tmp, "r");
			$checkcsvdata = fgetcsv($checkcsvfile);
			if ( !is_numeric($checkcsvdata[0]) ) {
				$r           = mysql_query("SELECT * from lists_exclusion where exclusion_name = '" . $listid . "'");
			    $ro          = mysql_num_rows($r);
			    $lrow        = mysql_fetch_assoc($r);
				if ( $ro == 0 ) {
			        mysql_query("insert into lists_exclusion set exclusion_name = '$listid', projectid = '$projectid', bcid = '$bcid', date_created = unix_timestamp();") or die(mysql_error());
			        $exclusionid = mysql_insert_id();
					
					$prefix			= $bcid . "/exclusionlistupload/" . $projectid . "/" . $exclusionid . "/" . str_replace(" ", "", $name);
					if( $s3->putObjectFile( $tmp, $s3bucket, $prefix, S3::ACL_PUBLIC_READ ) ) {
						$s3file = "http://".$s3bucket.".s3.amazonaws.com/".$prefix;
						$csv   = fopen($s3file, "r");
			            $ct    = 0;
			            $ctaff = 0;
			            while (($data = fgetcsv($csv, 1000, ",")) !== FALSE) {
			                mysql_query("INSERT into lists_exclusion_data set exclusionid =$exclusionid, projectid = $projectid, phone= '" . $data[0] . "'") or die(mysql_error());
		                    $ex    = lists::exclude($projectid, $data[0], $lstr);
		                    $ctaff = $ctaff + $ex['affected'];
			                $ct++;
			            }
			            echo "Added $ct records into exclusion list. ";
		                echo $ctaff . " records set to DoNotCall.";
		                echo "<script>listMenu('manageexclusion');</script>";
					} else {
						echo "<script>$('.ui-dialog').remove();alert('Upload Failed.');listMenu('manageexclusion');</script>";
					}
			    } else {
					echo "<script>$('.ui-dialog').remove();alert('Upload Failed. Please change a different filename.');listMenu('manageexclusion');</script>";
			    }
			} else {
				echo "<script>$('.ui-dialog').remove();alert('No header. Please try again.');listMenu('manageexclusion');</script>";
			}
	    } else {
			echo "<script>$('.ui-dialog').remove();alert('Please select a file.');listMenu('manageexclusion');</script>";
		}
	} else {
		echo "<script>$('.ui-dialog').remove();alert('Please fill the fields.');listMenu('manageexclusion');</script>";
	}
}
if ( $act == 'dispoupdateupload' ) {
    $name      	 = $_FILES['csvfile']['name'];
	$tmp 		 = $_FILES['csvfile']['tmp_name'];
    $projectid   = $_REQUEST['projects'];
	
	if ( strlen($projectid) > 0 ) {
		$listids_arr = lists::findbyProjectId($projectid, TRUE, false);
	    $listids     = array();
	    foreach ($listids_arr as $lis) {
	        $listids[$lis] = "'" . $lis . "'";
	    }
	    $aglist           = 'agentgenerated' . $projectid;
	    $listids[$aglist] = "'" . $aglist . "'";
	    $lstr             = implode(",", $listids);
		
		$getprefix  = $bcid . "/dispositionupdateupload/" . $projectid;
	    $contents   = $s3->getBucket($s3bucket, $getprefix);
		foreach ($contents as $content) {
			$splitname = explode("/", $content['name']);
			$filename .= $splitname[4].",";
		}
		
	    if ( strlen($name) > 0 ) { 
	    	$checkcsvfile   = fopen($tmp, "r");
			$checkcsvdata = fgetcsv($checkcsvfile);
			if ( !is_numeric($checkcsvdata[0]) ) {
				mysql_query("INSERT into disposition_update_history set date_epoch = unix_timestamp(), bcid = $bcid, projectid = $projectid, filename = '$name'");
				$dispoupdateid 	= mysql_insert_id();
				$prefix			= $bcid . "/dispositionupdateupload/" . $projectid . "/" . $dispoupdateid . "/" . str_replace(" ", "", $name);
				
				if( $s3->putObjectFile( $tmp, $s3bucket, $prefix, S3::ACL_PUBLIC_READ ) ) {
					$s3file = "http://".$s3bucket.".s3.amazonaws.com/".$prefix;
					$csv   = fopen($s3file, "r");
				    $ct    = 0;
				    $ctaff = 0;
					while (($data = fgetcsv($csv, ",")) !== FALSE) {
				    	$aff   = lists::updatebyphone($projectid, $data[0], $data[1], $lstr);
				    	$ctaff = $ctaff + $aff;
				        $ct++;
				    }
					mysql_query("UPDATE disposition_update_history set records_total = $ct, records_updated = $ctaff where id = $dispoupdateid");
		
					echo "Uploaded " . $ct . " updated.";
	                echo $ctaff . " records affected";
	                echo "<script>listMenu('dispoupdate');</script>";
				} else {
					echo "<script>$('.ui-dialog').remove();alert('Upload Failed.');listMenu('dispoupdate');</script>";
				}
			} else {
				echo "<script>$('.ui-dialog').remove();alert('No header. Please try again.');listMenu('dispoupdate');</script>";
			}
	    } else {
			echo "<script>$('.ui-dialog').remove();alert('Please select a file.');listMenu('dispoupdate');</script>";
		}
	} else {
		echo "<script>$('.ui-dialog').remove();alert('Please select a campaign.');listMenu('dispoupdate');</script>";
	}
}
if ( $act == 'dnclistupload' ) {
	$name      	 = $_FILES['csvfile']['name'];
	$tmp 		 = $_FILES['csvfile']['tmp_name'];
    $dnclistname = $_REQUEST['dnclistname'];
	$listid		= $_REQUEST['listid'];
	$iswash		= $_REQUEST['washdate'] != "" ? 1 : 0;
    $newwashdate = $_REQUEST['washdate'];
    
	$listres 	= mysql_query("SELECT * from lists where lid = '".$listid."'");
    $listrow 	= mysql_fetch_assoc($listres);
    $listidname	= $listrow["listid"];
	$projectid	= $listrow["projects"];
	$lidlistcount	= $listrow["listcount"];
	$oldwashdate	= $listrow["washdate"];
	
	if ( strlen($name) > 0 ) {
		$checkcsvfile   = fopen($tmp, "r");
		$checkcsvdata = fgetcsv($checkcsvfile);
		if (!is_numeric($checkcsvdata[0]) ) {
			if ( strlen($listid) > 0 && strlen($dnclistname) > 0 && strlen($newwashdate) > 0 ) {
				mysql_query("INSERT INTO donotcall_list SET dnc_listname = '".$dnclistname."', dnc_listid = '".$listid."', 
				dnc_projectid  = '".$projectid."', dnc_date_created = unix_timestamp(), oldwashdate = '".$oldwashdate."', 
				newwashdate = '".strtotime($newwashdate)."', dnc_bcid = '".$bcid."', userid = '".$_SESSION['auth']."'");
			    $dncid = mysql_insert_id();
				
				mysql_query("UPDATE lists SET washdate = '".strtotime($newwashdate)."' WHERE lid = '".$listid."'");
				
				$count = 0;
	            while (($data = fgetcsv($checkcsvfile, 1000, ",")) !== FALSE) {
	            	$dncres = mysql_query("SELECT * FROM donotcall JOIN donotcall_list ON donotcall_list.id = donotcall.dncid 
					WHERE donotcall_list.dnc_projectid = '".$projectid."' AND donotcall.phone = '".$data[0]."' AND 
					donotcall_list.dnc_active = 1 AND donotcall_list.dnc_is_deleted = 0");
	            	if (mysql_num_rows($dncres) < 1) {
	            		$leadsrawres = mysql_query("SELECT * FROM leads_raw WHERE phone = '".$data[0]."' AND listid = '".$listidname."'");
						$leadsrawrow = mysql_fetch_assoc($leadsrawres);
	            		mysql_query("INSERT INTO donotcall SET dncid = ".$dncid.", phone = '".$data[0]."', projectid = '".$projectid."'");
						mysql_query("UPDATE leads_raw SET dispo = 'DNCR' WHERE phone = '".$data[0]."' AND listid = '".$listidname."'");
						mysql_query("UPDATE leads_done SET dispo = 'DNCR' WHERE phone = '".$data[0]."' AND listid = '".$listidname."'");
						mysql_query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', dncid = '".$dncid."', listid = '".$listidname."', 
						leadid = ".$leadsrawrow["leadid"].", oldvalue = '".$leadsrawrow["dispo"]."', newvalue = 'DNCR', field = 'dispo', epoch = unix_timestamp();");
						$count++;
					}
	            }
		        mysql_query("UPDATE donotcall_list SET dnc_listcount = '".$count."' WHERE id = '".$dncid."'");
				echo "<script>$('.ui-dialog').remove();alert('Loaded ".$count." records. Uploaded successfully.');listMenu('managedonotcall');</script>";
			} else {
				echo "<script>$('.ui-dialog').remove();alert('Please fill the fields.');listMenu('managedonotcall');</script>";
			}
		} else {
			echo "<script>$('.ui-dialog').remove();alert('No header on the file. Please try again.');listMenu('managedonotcall');</script>";
		}
	} else {
		echo "<script>$('.ui-dialog').remove();alert('Please select a file.');listMenu('managedonotcall');</script>";
	}
}
?>
