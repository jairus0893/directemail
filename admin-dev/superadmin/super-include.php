<?
if (isset($_REQUEST['extbulkdeleteextensions']))

{

    $bcids = $_REQUEST['bcids'];

    foreach ($bcids as $dib)

    {

       mysql_query("delete from bc_phones where name = '$dib'");

    }

    exit;

}
if (isset($_REQUEST['extbulkdeleteproviders']))

{

    $bcids = $_REQUEST['bcids'];

    foreach ($bcids as $dib)

    {

       mysql_query("delete from bc_providers where name = '$dib'");

    }

    exit;

}
if (isset($_REQUEST['savebulkext'])) {
	$tableData = stripcslashes($_REQUEST['data']);
	$tableData = json_decode($tableData,TRUE);
	for($i=0; $i<count($tableData); $i++) {
		$bcid = $tableData[$i]["bcid"];
		$username = $tableData[$i]["username"];
		$secret = $tableData[$i]["secret"];
		$classification = $tableData[$i]["classification"];
		if (strpos($tableData[$i]["username"],'-')) {
			$range = explode("-",$tableData[$i]["username"]);
			$start = intval($range[0]);
			$end = intval($range[1]);
			$ct = 0;
			while ($start <= $end) {
				mysql_query("INSERT into bc_phones set name = '$start', defaultuser='$start', secret = '$secret', classification = '$classification', bcid= '$bcid'");
				$start++;
				$ct++;
			}
		} else {
			mysql_query("INSERT into bc_phones set name = '$username', defaultuser='$username', secret = '$secret', classification = '$classification', bcid= '$bcid'");
		}
	}
}
if (isset($_REQUEST['saverangeext'])) {
	$bcid			= $_REQUEST['bcid'];
	$usernamefrom 	= $_REQUEST['usernamefrom'];
	$usernameto		= $_REQUEST['usernameto'];
	$secret 		= $_REQUEST['secret'];
	$classification = $_REQUEST['classification'];
	$rangecount = $usernameto - $usernamefrom;
	for ( $i = 0; $i <= $rangecount; $i++ ) {
		$range = $usernamefrom + $i;
		mysql_query("INSERT into bc_phones set name = '$range', defaultuser='$range', secret = '$secret', classification = '$classification', bcid= '$bcid'");
	}
}
if (isset($_REQUEST['checkextension'])) {
	$username = $_REQUEST['username'];

	$link = mysql_connect('10.0.1.184','obri','niner123');
	if (!$link) {
	    die('Could not connect: ' . mysql_error());
	}
	if (!mysql_select_db('proactiv')) {
	    die('Could not select database: ' . mysql_error());
	}

	$bcphonedetails = mysql_query("SELECT name FROM bc_phones WHERE name LIKE '$username' LIMIT 1");
	if (!$bcphonedetails) {
	    die('Could not query:' . mysql_error());
	}
	$row = mysql_fetch_row($bcphonedetails);
	if ( $row[0] != NULL ) {
		$result = TRUE;
	} else {
		$result = FALSE;
	}
	echo $row[0];
}
if (isset($_REQUEST['checkextensionrange'])) {
	$usernamefrom 	= $_REQUEST['usernamefrom'];
	$usernameto		= $_REQUEST['usernameto'];
	$rangecount = $usernameto - $usernamefrom;
	for ( $i = 0; $i <= $rangecount; $i++ ) {
		$range = $usernamefrom + $i;
		$link = mysql_connect('10.0.1.184','obri','niner123');
		if (!$link) {
		    die('Could not connect: ' . mysql_error());
		}
		if (!mysql_select_db('proactiv')) {
		    die('Could not select database: ' . mysql_error());
		}

		$bcphonedetails = mysql_query("SELECT name FROM bc_phones WHERE name LIKE '$range' LIMIT 1");
		if (!$bcphonedetails) {
		    die('Could not query:' . mysql_error());
		}
		$row = mysql_fetch_row($bcphonedetails);
		$getname .= ','.$row[0];

	}
	$resultrtrim = rtrim($getname, ",");
	$resultltrim = ltrim($resultrtrim, ",");
	$result = preg_replace('/,+/', ',', $resultltrim);
	echo $result;
}
if (isset($_REQUEST['checkbulkext'])) {
	$tableData = stripcslashes($_REQUEST['data']);
	$tableData = json_decode($tableData,TRUE);
	for($i=0; $i<count($tableData); $i++) {
		$username = $tableData[$i]["username"];

		$link = mysql_connect('10.0.1.184','obri','niner123');
		if (!$link) {
		    die('Could not connect: ' . mysql_error());
		}
		if (!mysql_select_db('proactiv')) {
		    die('Could not select database: ' . mysql_error());
		}

		$bcphonedetails = mysql_query("SELECT name FROM bc_phones WHERE name LIKE '$username' LIMIT 1");
		if (!$bcphonedetails) {
		    die('Could not query:' . mysql_error());
		}
		$row = mysql_fetch_row($bcphonedetails);
		$getname .= ','.$row[0];
	}
	$resultrtrim = rtrim($getname, ",");
	$resultltrim = ltrim($resultrtrim, ",");
	$result = preg_replace('/,+/', ',', $resultltrim);
	echo $result;
}
?>
