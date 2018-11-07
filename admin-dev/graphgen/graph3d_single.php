<?php
require_once("colorgen.php");

echo '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<graph caption="Monthly Unit Sales" xAxisName="Month" yAxisName="Units" decimalPrecision="0" formatNumberScale="0">';
$xarr = $_REQUEST['xarr'];
$yarr = $_REQUEST['yarr'];
$xaxis = explode("|",$xarr);
$yaxid = explode("|",$yarr);
$c = 0;
foreach($xaxis as $x)
	{
		echo '<set name="'.$x.'" value="'.$yaxis[$c].'" color="'.rand_colorCode().'" />';
		$c++;
	}
echo "</graph>";
?>
