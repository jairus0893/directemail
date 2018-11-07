<?php
mysql_connect("127.0.0.1","root","");
mysql_select_db("proactiv");
$query1 = mysql_query("select leadid from leads_done where listid = '20090129_Chelsea_00001_B'");
$ct = 0;
while ($row1 = mysql_fetch_row($query1))
	{
	$dt = $row1[0];
	if ($ct != 0)
		{
		$dis .= ",";
		}
	$dis.= "'$dt'";
	$ct++;
	}
$query2 = mysql_query("update leads_raw set hopper = 0 where listid = '20090129_Chelsea_00001_B' and hopper = 1 and leadid not in ($dis)");
$row2 = mysql_fetch_row($query2);
echo $row2[0]; 