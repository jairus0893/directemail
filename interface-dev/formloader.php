<?php
include "../dbconnect.php";
$act = $_REQUEST['act'];
if ($act == 'updatepage')
	{
		$id = $_REQUEST['id'];
		$res = mysql_query("SELECT * from qantas where suppliernumber = '$id' limit 1");
		while ($row = mysql_fetch_array($res))
			{
				$eid = $row['id'];
				$disp .= '<div style="position:relative; float:left">';
				$disp .= 'Name:'.$row['suppliername'].'<br>';
				$disp .= 'Category:'.$row['category'].'<br>';
				$disp .= 'Subcategory:'.$row['subcategory'].'<br>';
				$disp .= 'OperatingUnit:'.$row['operatingunit'].'<br>';
				$disp .= 'Account:'.$row['account'].'<br>';
				$disp .= 'Site:'.$row['suppliersitename'].'<br>';
				$disp .= 'Email: <input type="text" name="email'.$id.'" id="email'.$id.'"><br>Contact Name:<input type="text" name="contact'.$id.'" id="contact'.$id.'"><br></div>';
				$disp .= '<div style="position:relative; float:right;padding-right:100px"><a href="#" onclick="updateqantas(\''.$id.'\')">Update</a>';
				$disp .= '</div><div style="clear:both"></div><br>';
			}
		echo $disp;
	}
if ($act == 'newlead')
	{
		?>
        <style>
		.theader {
			background-color:#CCC;
			color:#000;
			border-bottom:1px solid #F00;
			text-align:center;
			font-weight:900;
		}
		#nlp {
			background-color:#CCC;
			width:100%;
			text-align:center;
		}
		#formbutton {
			background-color:#999;
			border-bottom:1px solid #000;
			border-right:1px solid #000;
			border-left:1px solid #CCC;
			border-top:1px solid #CCC;
			font-size:10px;
			font-family:Tahoma;
		}
		#nlmain {
			width:100%;
		}
		.datas {
			float:left;
			border-right:1px solid #CCC;
			border-bottom:1px solid #CCC;
			width:30%;
			padding-left:5px;
		}
		.clear {
			clear:both;
		}
		.nldetails {
			width:90%;
			border-bottom:1px solid #CCC;
			border-right:1px solid #CCC;
			padding-left:15px;
			padding-top:3px;
		}
		</style>
        <div id=nlp>
        Search: <input type="text" id="nlphone" name="nlphone"/> <input type="button" value="Search" onclick="nlsearch()" id="formbutton"/>
        </div>
        <div id=nlmain>
        </div>
        <?
	}
?>