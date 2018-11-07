<?php
require "../../classes/labels.php";
include_once "../../dbconnect.php";
include_once "../../phpfunctions.php";
if ($_REQUEST['act'] == "updatecampaignlist") {
	$listres = mysql_query("SELECT * FROM lists WHERE projects = ".$_REQUEST['projectid']." AND active = 1 ORDER BY listid");
	$listidlist .= '<option value=""></option>';
	while ($listrow = mysql_fetch_array($listres)) {
		$listidlist .= '<option value="'.$listrow['lid'].'">'.$listrow['listid'].'</option>';
	}
	echo $listidlist;
}