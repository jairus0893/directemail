<?php
require "../../classes/labels.php";
include_once "../../dbconnect.php";
include_once "../../phpfunctions.php";
if ($_REQUEST['act'] == "updatecampaignlist") {
	$listidlist .= '<option value="all">All</option>';
	$listres = mysql_query("SELECT * FROM lists WHERE projects = ".$_REQUEST['projectid']." AND active = 1 ORDER BY listid");
	while ($listrow = mysql_fetch_array($listres)) {
		$listidlist .= '<option value="'.$listrow['lid'].'">'.$listrow['listid'].'</option>';
	}
	echo $listidlist;
}
function tablegen2($headers, $rows, $width = "770", $rowscript = NULL, $tableclass= NULL) {
    echo '<table width="'.$width.'" class="'.$tableclass.'">';
	echo '<thead><tr>';
	foreach ($headers as $header) {
        if ($header == 'epoch_timeofcall')
        {
            echo '<th class="tableheader">'.ucfirst(labels::get("timeofcall")).'</th>';
        }
		else echo '<th class="tableheader">'.ucfirst(labels::get($header)).'</th>';
	}
	echo '</tr></thead><tbody>';
	$c = 1;
	foreach ($rows as $row) {
		$c++;
		if ($c % 2) $class = "tableitem";
		else $class = "tableitem_";
		echo '<tr class="'.$class.'" '.$row['options'].'>';
		foreach ($headers as $header) {
            if ($header == 'epoch_timeofcall')
            {
                if (strlen($row[$header]) < 1)
                {
                    echo '<td>0000-00-00 00:00:00</td>';
                }
                else echo '<td>'.date("Y-m-d H:i:s",$row[$header]).'</td>';
            }
            else if ($header == 'epoch_callable')
            {
                if ($row[$header] < 1)
                {
                    echo '<td>0000-00-00 00:00:00</td>';
                }
                else echo '<td>'.date("Y-m-d H:i:s",$row[$header]).'</td>';
            }
            else echo '<td>'.$row[$header].'</td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table>';
}