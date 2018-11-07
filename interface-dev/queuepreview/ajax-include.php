<?
if ($act == 'checkhopper')
{
	$leadid = $_REQUEST['leadid'];
	$projectid = $_REQUEST['projectid'];
    $checkhopperifcalled = mysql_query("SELECT * FROM hopper WHERE leadid = ".$leadid." AND projectid = ".$projectid."");
	while ($checkhopperdetails = mysql_fetch_assoc($checkhopperifcalled)) {
		$checkhopper[] = $checkhopperdetails;
	}
	echo $checkhopper[0]["called"];
}
?>