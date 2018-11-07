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
		if (strpos($tableData[$i]["username"],'-')) {
			$range = explode("-",$tableData[$i]["username"]);
			$start = intval($range[0]);
			$end = intval($range[1]);
			$ct = 0;
			while ($start <= $end) {
				mysql_query("INSERT into bc_phones set name = '$start', defaultuser='$start', secret = '$secret', bcid= '$bcid'");
				$start++;
				$ct++;
			}
		} else {
			mysql_query("INSERT into bc_phones set name = '$username', defaultuser='$username', secret = '$secret', bcid= '$bcid'");
		}
	}
	return $return;
}
?>