<?php

function rendercustomdata($bid, $cvfields) {
	$res = mysql_query("SELECT customfields from projects where projectid = '$bid'");
	$row = mysql_fetch_assoc($res);
	$row = json_decode($row['customfields']);
	$table = '<div id="customdatatable"><h3>Custom Fields</h3><table class="vftab"><tr><th>Column</th><th>Show</th></tr>';
	foreach ($row as $key => $value) {
		$customarray[] = array($key => $key, 0);
		if (!empty($cvfields)) {
			$checked = "checked";
			$cvs = explode(", ", $cvfields);
		    if (in_array($key, $cvs)) {
		    	$table .= '<tr><td class="label">'.$key.'</td><td class="ck"><input type="checkbox" name="viewfields[]" class="vfs" value="'.$key.'" '.$checked.'></td></tr>';
			} else {
		$table .= '<tr><td class="label">'.$key.'</td><td class="ck"><input type="checkbox" name="viewfields[]" class="vfs" value="'.$key.'"></td></tr>';
	}
} else {
	$table .= '<tr><td class="label">'.$key.'</td><td class="ck"><input type="checkbox" name="viewfields[]" class="vfs" value="'.$key.'"></td></tr>';
}
}
$table .= '</table></div>';
    // print_r($customarray);
    // print_r($viewfields);
	return $table;
}
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
function arraycustomdata($bid){
	$res = mysql_query("SELECT customfields from projects where projectid = '$bid'");
	$row = mysql_fetch_assoc($res);
	$row = json_decode($row['customfields']);
	foreach ($row as $key => $value) {
		$customarray[$key] = array($key, 0, 'custom');
	}
	return $customarray;
}
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
function getbcidbypid($pid){
	$res = mysql_query("SELECT bcid from projects where projectid = '$pid'");
	$row = mysql_fetch_assoc($res);
	return $row['bcid'];
}