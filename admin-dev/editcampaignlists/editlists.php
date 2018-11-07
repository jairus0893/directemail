<?php
include_once "../../dbconnect.php";
require "../../classes/classes.php";
require "../../classes/lists.php";
include "../../admin/phpfunctions.php";
include "../../admin/editcampaignlists/editcampaignlists-include.php";
session_start();
// SERVER UNIX_TIMESTAMP
// $unixtimestamp_res = mysql_query("SELECT UNIX_TIMESTAMP()");
// $unixtimestamp_row = mysql_fetch_assoc($unixtimestamp_res);
// $unixtimestamp = $unixtimestamp_row["UNIX_TIMESTAMP()"];

// MYSQLI
$mysqli = mysqli_connect('10.0.1.184','bcdev','17c7e8d1c00cb4d6bf6dacd5c97ba617','bcdev');
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
$bcid = $_SESSION["bcid"];
function filterdata($tableData) {
	$tableData = json_decode($tableData,TRUE);
	$fdata = '';
	for($i=0; $i<count($tableData); $i++) {
		$field		= $tableData[$i]["fields"];
		$operand	= urldecode($tableData[$i]["operand"]);
		$val		= $tableData[$i]["value"];
		$fdata		.= " and ";
		if ($operand == 'in' || $operand == 'not in') {
			$v = explode(",",$val);
			$ct = 0;
			foreach ($v as $vl) {
				$val2[$ct] .= "'".$vl."'";
				$ct++;
			}
			$inval = implode(",",$val2);
			$fdata .= $field." ".$operand." (".$inval.")";
		} else if ($operand == 'like') {
			$fdata .= $field." ".$operand." '%".$val."%'";
		} else if ($operand == 'not like') {
			$fdata .= $field." ".$operand." '%".$val."%'";
		} else if ($operand == 'startlike') {
			$fdata .= $field." ". "like" . " '" .$val."%'";
		} else if ($operand == 'not startlike') {
			$fdata .= $field." ". "not like" . " '" .$val."'%'";
		} else if ($operand == 'endlike') {
			$fdata .= $field." ". "like" . " '%" .$val."'";
		} else if ($operand == 'not endlike') {
			$fdata .= $field." ". "not like" . " '%" .$val."'";
		} else {
			$fdata .= $field." ".$operand." '".$val."'";
		}
	}
	return $fdata;
}
if ($_REQUEST['act'] == "editlists") {
	$multilistids = $_REQUEST['lid'];
	$reslists = mysql_query("SELECT * from lists where lid = ".$multilistids." and bcid = ".$bcid." and active = 1");
	$rowlists = mysql_fetch_assoc($reslists);
	$projectid = $rowlists["projects"];
	
	if (strpos($multilistids, ",")) {
		$multilist = explode(",", $multilistids);
	} else {
		$multilist = $multilistids;
	}
	$vfs = array(
		'listid',
	    'cname',
	    'cfname',
	    'clname',
	    'title',
	    'company',
	    'address1',
	    'address2',
	    'suburb',
	    'city',
	    'state',
	    'country',
	    'zip',
	    'phone',
	    'altphone',
	    'email',
	    'comments',
	    'industry',
	    'sic',
	    'dispo',
	    'positiontitle',
	    'mobile'
	);
	foreach ($vfs as $vf) {
	    $viewfields[$vf][1] = 1;
	}
	foreach ($viewfields as $key => $value) {
	    if ($value[1] && !$value[2]) {
	        $exportfields[] = $key;
	    }
	}
	
	if (!empty($_REQUEST["act1"])) {
		if ($_REQUEST["act1"] == "columnsearch") {
			$fdata = filterdata($_REQUEST['data']);
			$records = lists::listrecordseditcampaignlists($bcid, $projectid, $exportfields, $multilist, $fdata);
		}
		if ($_REQUEST["act1"] == "columnedit") {
			$records = lists::listrecordseditcampaignlists($bcid, $projectid, $exportfields, $multilist);
		}
	} else {
		$records = lists::listrecordseditcampaignlists($bcid, $projectid, $exportfields, $multilist);
	}
	
	$resprojects	= mysql_query("SELECT * from projects where projectid = '".$projectid."'");
	$projectname	= mysql_fetch_assoc($resprojects);
	$headers			= array();
	$cdata				= $records['cdata'];
	$cdheader			= $records['cdheaders'];
	
	foreach ($exportfields as $hd) {
	    $headers[$hd] = $hd;
	}
	$rows =& $records["records"];
	$resmembers = mysql_query("SELECT *, memberdetails.afirst, memberdetails.alast, memberdetails.team from members left join memberdetails on members.userid = memberdetails.userid where bcid = 
	".$projectname['bcid']." AND alast <> '' AND afirst <> '' ORDER BY alast,afirst,userlogin");
	while ($rowmembers = mysql_fetch_assoc($resmembers)) {
		$allmembers[$rowmembers['userid']] = $rowmembers;
	}
	//CDATA
	foreach ($cdata as $leadid => $customfields) {
	    $customdata = json_decode($customfields['customfields'], true);
	    foreach ($customdata as $cd1key => $cd1value) {
	    	$customdata2 = json_decode($cdheader, true);
		    foreach ($customdata2 as $cd2key => $cd2value) {    	
		        if ($cd1key == $cd2key) {
		        	$key = "*" . $cd1key;
		        	$rows[$leadid][$key] = $cd1value;
		        }
		    }
		    unset($customdata2);
	        
	    }
	    unset($customdata);
	}
	unset($cdata);
	unset($records['cdata']);
	
	$cdheaders = json_decode($cdheader, true);
    foreach ($cdheaders as $key => $value) {    	
        $key			= "*" . $key;
        $headers[$key]	= $key;
	}
	
	$labels = new labels();
    // LEADS RAW FIELD LIST 
    $leadsrawfieldlist = "";
	$leads_raw_fieldres = mysql_query("SHOW COLUMNS FROM leads_raw");
	while ($leads_raw_fieldrow = mysql_fetch_assoc($leads_raw_fieldres)) {
		if (in_array($leads_raw_fieldrow["Field"], $headers)) {
			$label = $labels->get($leads_raw_fieldrow["Field"]);
			$leadsrawfieldlist .= '<option value="'.$leads_raw_fieldrow["Field"].'">'.ucfirst($label).'</option>';
		}
	}
	// CUSTOM FILED LIST
	$customfieldlist = "";
	$projectres  = mysql_query("SELECT * from projects where projectid = '$projectid'");
	$projectrow = mysql_fetch_assoc($projectres);
	$cfields  = json_decode($projectrow['customfields'], true);
	foreach ($cfields as $field => $value) {
		$customfieldlist .= '<option value="cf|'.$field.'">'.ucfirst($field).'</option>';
	}
	if (!empty($_REQUEST["act1"])) {
		if ($_REQUEST["act1"] == "columnsearch" || $_REQUEST["act1"] == "columnedit") {
			$result = '';
			$result .= '
		    <table class="editcampaignliststable">
			<thead>
				<tr>';
					$labels = new labels();
					foreach ($headers as $header){
						$label = $labels->get($header);
                        $result .= '<th class="tableheader">'.ucfirst($label).'</th>';
					}
				$result .= '</tr>
			</thead>
			<tbody>';
				$columncount = count($headers);
				$c = 1;
				foreach ($rows as $row) {
					$c++;
					if ($c % 2) $class = "tableitem";
					else $class = "tableitem_";
					$result .= '<tr class="'.$class.'" '.$row['options'].'>';
					foreach ($headers as $header) {
						if ($header == "listid") {
							$result .= '<td><input type="hidden" name="editlistsleadid[]" value="'.$row['leadid'].'">'.$row[$header].'</td>';
						} else {
							$pos = strpos($header, "*");
							if ($pos === false) {
								$result .= '<td><input type="text" name="editlistslead" disabled onchange="triggerchange()" onblur="editlead(this,\'' . $row['leadid'] . '\',\'' . $header . '\')" value="'.$row[$header].'"/></td>';
							} else {
								$result .= '<td><input type="text" class="editlistsleadcf_'.$row['leadid'].'" name="'.ltrim($header, "*").'" disabled onchange="triggerchange()" onblur="editlead(this,\'' . $row['leadid'] . '\',\'' . $header . '\')" value="'.$row[$header].'"/></td>';
							}
						}
					}
					echo '</tr>';
				}
			$result .= '</tbody>
			</table>';
			echo $result;
		}
	}
	
	if ($_REQUEST["sub"] == "defaulteditlists") {
		?>
		<div id="tabs">
			<div class="active" id="globaltab" onclick="listsviewtab('global','<?php echo $_REQUEST["lid"];?>')"><p style="font-size:1.2em">Bulk Editing</p></div>
		    <div class="inactive" id="inlinetab" onclick="listsviewtab('inline','<?php echo $_REQUEST["lid"];?>')"><p style="font-size:1.2em">Row Editing</p></div>
		    <div class="clear"></div>
		    <div id="boundary"></div>
		</div>
		<div class="ecloading">    
		    <img src="loading_big.gif" alt="loading" />             
		</div>
		<div class="clear"></div>
		<br/>
		<div id="tabcont">
			<div id="tabinline" class="tabc">
				<br/>
				<?php
				$fldres = mysql_query("SELECT cname, cfname, clname, title, company, address1, address2, suburb, city, state, country, zip, phone, altphone, comments, industry, sic, email, dispo from leads_raw limit 1");
				$fldct = mysql_num_fields($fldres);
				$y = 0;
				while ($y < $fldct)
				{
					$fld = mysql_field_name($fldres, $y);
					if ($fld == 'zip')
					{
						$fldlist .= '<option value="'.$fld.'" onclick="nodrop()">Postcode</option>';
					}
					elseif ($fld == 'cname')
					{
						$fldlist .= '<option value="'.$fld.'" onclick="nodrop()">Name</option>';
					}
					elseif ($fld == 'cfname')
					{
						$fldlist .= '<option value="'.$fld.'" onclick="nodrop()">FirstName</option>';
					}
					elseif ($fld == 'clname')
					{
						$fldlist .= '<option value="'.$fld.'" onclick="nodrop()">SurName</option>';
					}
					elseif ($fld == 'dispo')
					{
						$fldlist .= '<option value="'.$fld.'" onclick="popdispo(\''.$projid.'\')">Disposition</option>';
					}
					else {
						$fldlist .= '<option value="'.$fld.'" onclick="nodrop()">'.ucfirst($fld).'</option>';
					}
					$y++;
				}
				$operand = '
				<option value="'.urlencode('like').'">contains</option>
				<option value="'.urlencode('startlike').'">starts with</option>
				<option value="'.urlencode('endlike').'">ends with</option>
				<option value="'.urlencode('<').'">less than</option>
				<option value="'.urlencode('>').'">greater than</option>
				<option value="'.urlencode('in').'" onclick="nodrop()">listed in</option>
				<option value="'.urlencode('=').'">equal to</option>
				<option value="'.urlencode('not like').'">not contain</option>
				<option value="'.urlencode('not startlike').'">not start with</option>
				<option value="'.urlencode('not endlike').'">not end with</option>
				<option value="'.urlencode('!=').'">not equal to</option>
				<option value="'.urlencode('not in').'" onclick="nodrop()">not listed in</option>
				';
				?>
				<div class="clear"></div>
				<br/>
				<p><span style="font-size: 1.2em">CURRENT LIST(S) SELECTED FOR EDITING:</span><br/><br/><span style="color: blue;font-size: 1.4em;"><b><?php echo $rowlists["listid"];?></b></span></p>
				
				<br/>
				<div id="gparent">
					<div id="gwide">
						<h2 style="color: red;"><input name="editingradiobtn" id="columneditingbtn" type="radio"/> <span style="color:red; font-size: 1.3em"> EDIT ALL RECORDS IN THE LIST(S):</span></h2>
						<div id="columneditingdiv" style="display:none">
							<br/>
							<hr/>
							<br/>
							<i>*Operation:</i>
							<select name="columnapplyeditlistfields" id="columnapplyeditlistfields" onchange="changecolumnoperationeditlist();">
								<option value="">-</option>
								<option title="Add at the end" value="append">Append</option>
								<option title="Add at the beginning" value="prepend">Prepend</option>
								<option title="Replace with new value" value="replaceall">Replace Text in Selected Column</option>
								<option title="Move values to another column" value="move">Move Column</option>
								<option title="Copy values to another column" value="copy">Copy Column</option>
								<option title="Swap values of two column" value="swap">Swap Column</option>
							</select>
							<br/>
							<br/>
							<div style="display:none" id="columnnewvalueeditlist">
								<span class="expnotif3" style="font-style: italic; font-size: 0.9em"></span>
								<div style="display:none" id="columnreplacevalueeditlist">
								<h3>*Replace value:</h3>
								<textarea name="columnreplacevalue" id="columnreplacevalue" class="columnreplacevalue" style="width: 300px;"></textarea>
								</div>
								<br/>
								<h3>*New value:</h3>
								<textarea name="columnnewvalue" id="columnnewvalue" class="columnnewvalue" style="width: 300px;"></textarea>
								<br/>
								<h3>*Apply to:</h3>
								<select name="columnapplytovalue" id="columnapplytovalue" style="width: 100px;">
									<option value="">-</option>
									<?php echo $leadsrawfieldlist;?>
									<option disabled>──────────</option>
									<?php echo $customfieldlist;?>
								</select>
							</div>
							<div style="display:none" id="columnswapvalueeditlist">
								<span class="expnotif4" style="font-style: italic; font-size: 0.9em"></span>
								<h3>*From:</h3>
								<select name="columnfromeditlistfields" id="columnfromeditlistfields" style="width: 100px;" onchange="columncheckdefaultcustomfields();">
									<option value="">-</option>
									<?php echo $leadsrawfieldlist;?>
									<option disabled>──────────</option>
									<?php echo $customfieldlist;?>
								</select>
								<br/>
								<h3>*To:</h3>
								<select name="columntoeditlistfields" id="columntoeditlistfields" style="width: 100px;" onchange="columncheckdefaultcustomfields();">
									<option value="">-</option>
									<?php echo $leadsrawfieldlist;?>
									<option disabled>──────────</option>
									<?php echo $customfieldlist;?>
								</select>
							</div>
						</div>
					</div>
					<div id="gnarrow">
						<h2><input name="editingradiobtn" id="searcheditingbtn" type="radio"/> <span style="font-size:1.3em"> Select Records From List(s) To Edit</span></h2>
						<div id="searcheditingdiv" style="display:none">
							<br/>
							<hr/>
							<br/>
							<i>Search:</i>
							<table id="globaledittableFilters" style="width:100%;">
								<tr>
									<td class="dataleft">
										<select name="searcheditlistfields">
											<?php echo $fldlist?>
											<!-- <option disabled>──────────</option> -->
											<?php //echo $customfieldlist?>
										</select>
									</td>
									<td class="dataleft">
										<select name="operand"><?php echo $operand?></select>
									</td>
									<td>
										<span id="vaspaneditlists">
											<input type="text" name="vaedit" style="width: 200px;">
										</span>
									</td>
									<td>
										<span id="addnewrow">
											<img src="../admin/icons/add.gif">
										</span>
									</td>
								</tr>
							</table>
							<br/>
							<br/>
							<a href="#" class="jbut" id="searcheditlistsbtn" onclick="searcheditlists(<?php echo $multilist ?>);">Search</a>
							<br/><br/>
							<hr/>
							<br/>
							<i>*Operation:</i>
							<select name="applyeditlistfields" id="applyeditlistfields" disabled onchange="changeoperationeditlist();">
								<option value="">-</option>
								<option title="Add at the end" value="append">Append</option>
								<option title="Add at the beginning" value="prepend">Prepend</option>
								<option title="Replace the selective string with new value" value="selectivereplace">Selective Replace</option>
								<option title="Replace with new value" value="replaceall">Replace Text in Selected Column</option>
								<option title="Move values to another column" value="move">Move Column</option>
								<option title="Copy values to another column" value="copy">Copy Column</option>
								<option title="Swap values of two column" value="swap">Swap Column</option>
							</select>
							<br/>
							<br/>
							<div style="display:none" id="newvalueeditlist">
								<span class="expnotif1" style="font-style: italic; font-size: 0.9em"></span>
								<div style="display:none" id="replacevalueeditlist">
								<h3>*Replace value:</h3>
								<textarea name="replacevalue" id="replacevalue" class="replacevalue" style="width: 300px;"></textarea>
								</div>
								<br/>
								<h3>*New value:</h3>
								<textarea name="newvalue" id="newvalue" class="newvalue" style="width: 300px;"></textarea>
								<br/>
								<h3>*Apply to:</h3>
								<select name="applytovalue" id="applytovalue" style="width: 100px;">
									<option value="">-</option>
									<?php echo $leadsrawfieldlist;?>
									<option disabled>──────────</option>
									<?php echo $customfieldlist;?>
								</select>
							</div>
							<div style="display:none" id="swapvalueeditlist">
							<span class="expnotif2" style="font-style: italic; font-size: 0.9em"></span>
							<h3>*From:</h3>
							<select name="fromeditlistfields" id="fromeditlistfields" style="width: 100px;" onchange="checkdefaultcustomfields();">
								<option value="">-</option>
								<?php echo $leadsrawfieldlist;?>
								<option disabled>──────────</option>
								<?php echo $customfieldlist;?>
							</select>
							<br/>
							<h3>*To:</h3>
							<select name="toeditlistfields" id="toeditlistfields" style="width: 100px;" onchange="checkdefaultcustomfields();">
								<option value="">-</option>
								<?php echo $leadsrawfieldlist;?>
								<option disabled>──────────</option>
								<?php echo $customfieldlist;?>
							</select>
						</div>
						</div>
					</div>
					<div id="gnarrow1">
						<br/>
						<button style="font-weight:bold; font-size:1.3em; display:none; width:150px; height: 80px; background-color: #d9534f" id="submiteditlistsbtn" onclick="confirmeditlists(<?php echo $multilist ?>);">Commit changes</button>
						<button style="font-weight:bold; font-size:1.3em; display:none; width:150px; height: 80px; background-color: #d9534f" id="columnsubmiteditlistsbtn" onclick="columnconfirmeditlists(<?php echo $multilist ?>);">Commit changes</button>
					</div>
				</div>
				<br/>
				<hr/><br/>
				<div id="leadsglobaleditresult">
				</div>
				<br/>
				<br/>
			</div>
			<div id="tabglobal" class="tabc">
			</div>
		</div>
		<?php
	} else if ($_REQUEST["sub"] == "inlineedit") {
		?>
		<br/>
		<table class="editcampaignliststable">
			<thead>
				<tr>
					<?php
					$labels = new labels();
					foreach ($headers as $header){
						$label = $labels->get($header);
                        echo '<th class="tableheader">'.ucfirst($label).'</th>';
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
				$columncount = count($headers);
				$c = 1;
				foreach ($rows as $row) {
					$c++;
					if ($c % 2) $class = "tableitem";
					else $class = "tableitem_";
					echo '<tr class="'.$class.'" '.$row['options'].'>';
					foreach ($headers as $header) {
						if ($header == "listid") {
							echo '<td><input type="hidden" name="editlistsleadid" value="'.$row['leadid'].'">'.$row[$header].'</td>';
						} else {
							$pos = strpos($header, "*");
							if ($pos === false) {
								echo '<td><input type="text" name="editlistslead" onchange="triggerchange()" onblur="editlead(this,\'' . $row['leadid'] . '\',\'' . $header . '\')" value="'.$row[$header].'"/></td>';
							} else {
								echo '<td><input type="text" class="editlistsleadcf_'.$row['leadid'].'" name="'.ltrim($header, "*").'" onchange="triggerchange()" onblur="editlead(this,\'' . $row['leadid'] . '\',\'' . $header . '\')" value="'.$row[$header].'"/></td>';
							}
						}
					}
					echo '</tr>';
				}
				?>
			</tbody>
		</table>
		<?php
	} else if ($_REQUEST["sub"] == "globaledit") {
		$fldres = mysql_query("SELECT cname, cfname, clname, title, company, address1, address2, suburb, city, state, country, zip, phone, altphone, comments, industry, sic, email, dispo from leads_raw limit 1");
        $fldct = mysql_num_fields($fldres);
        $y = 0;
        while ($y < $fldct)
        {
            $fld = mysql_field_name($fldres, $y);
            if ($fld == 'zip')
            {
                $fldlist .= '<option value="'.$fld.'" onclick="nodrop()">Postcode</option>';
            }
            elseif ($fld == 'cname')
            {
	            $fldlist .= '<option value="'.$fld.'" onclick="nodrop()">Name</option>';
            }
            elseif ($fld == 'cfname')
            {
    	        $fldlist .= '<option value="'.$fld.'" onclick="nodrop()">FirstName</option>';
            }
            elseif ($fld == 'clname')
            {
        	    $fldlist .= '<option value="'.$fld.'" onclick="nodrop()">SurName</option>';
            }
            elseif ($fld == 'dispo')
            {
            	$fldlist .= '<option value="'.$fld.'" onclick="popdispo(\''.$projid.'\')">Disposition</option>';
            }
            else {
            	$fldlist .= '<option value="'.$fld.'" onclick="nodrop()">'.ucfirst($fld).'</option>';
            }
            $y++;
        }
        $operand = '
        <option value="'.urlencode('like').'">contains</option>
        <option value="'.urlencode('startlike').'">starts with</option>
        <option value="'.urlencode('endlike').'">ends with</option>
        <option value="'.urlencode('<').'">less than</option>
        <option value="'.urlencode('>').'">greater than</option>
        <option value="'.urlencode('in').'" onclick="nodrop()">listed in</option>
        <option value="'.urlencode('=').'">equal to</option>
        <option value="'.urlencode('not like').'">not contain</option>
        <option value="'.urlencode('not startlike').'">not start with</option>
        <option value="'.urlencode('not endlike').'">not end with</option>
        <option value="'.urlencode('!=').'">not equal to</option>
        <option value="'.urlencode('not in').'" onclick="nodrop()">not listed in</option>
        ';
		?>
		<div class="clear"></div>
		<br/>
		<p><span style="font-size: 1.2em">CURRENT LIST(S) SELECTED FOR EDITING:</span><br/><br/><span style="color: blue;font-size: 1.4em;"><b><?php echo $rowlists["listid"];?></b></span></p>
		
		<br/>
		<div id="gparent">
			<div id="gwide">
			<h2 style="color: red;"><input name="editingradiobtn" id="columneditingbtn" type="radio"/> <span style="color:red; font-size: 1.3em"> EDIT ALL RECORDS IN THE LIST(S)</span></h2>
				<div id="columneditingdiv" style="display:none">
					<br/>
					<hr/>
					<br/>
					<i>*Operation:</i>
					<select name="columnapplyeditlistfields" id="columnapplyeditlistfields" onchange="changecolumnoperationeditlist();">
						<option value="">-</option>
						<option title="Add at the end" value="append">Append</option>
						<option title="Add at the beginning" value="prepend">Prepend</option>
						<option title="Replace with new value" value="replaceall">Replace Text in Selected Column</option>
						<option title="Move values to another column" value="move">Move Column</option>
						<option title="Copy values to another column" value="copy">Copy Column</option>
						<option title="Swap values of two column" value="swap">Swap Column</option>
					</select>
					<br/>
					<br/>
					<div style="display:none" id="columnnewvalueeditlist">
						<span class="expnotif3" style="font-style: italic; font-size: 0.9em"></span>
						<div style="display:none" id="columnreplacevalueeditlist">
						<h3>*Replace value:</h3>
						<textarea name="columnreplacevalue" id="columnreplacevalue" class="columnreplacevalue" style="width: 300px;"></textarea>
						</div>
						<br/>
						<h3>*New value:</h3>
						<textarea name="columnnewvalue" id="columnnewvalue" class="columnnewvalue" style="width: 300px;"></textarea>
						<br/>
						<h3>*Apply to:</h3>
						<select name="columnapplytovalue" id="columnapplytovalue" style="width: 100px;">
							<option value="">-</option>
							<?php echo $leadsrawfieldlist;?>
							<option disabled>──────────</option>
							<?php echo $customfieldlist;?>
						</select>
					</div>
					<div style="display:none" id="columnswapvalueeditlist">
						<span class="expnotif4" style="font-style: italic; font-size: 0.9em"></span>
						<h3>*From:</h3>
						<select name="columnfromeditlistfields" id="columnfromeditlistfields" style="width: 100px;" onchange="columncheckdefaultcustomfields();">
							<option value="">-</option>
							<?php echo $leadsrawfieldlist;?>
							<option disabled>──────────</option>
							<?php echo $customfieldlist;?>
						</select>
						<br/>
						<h3>*To:</h3>
						<select name="columntoeditlistfields" id="columntoeditlistfields" style="width: 100px;" onchange="columncheckdefaultcustomfields();">
							<option value="">-</option>
							<?php echo $leadsrawfieldlist;?>
							<option disabled>──────────</option>
							<?php echo $customfieldlist;?>
						</select>
					</div>
				</div>
			</div>
			<div id="gnarrow">
				<h2><input name="editingradiobtn" id="searcheditingbtn" type="radio"/> <span style="font-size:1.3em"> Select Records From List(s) To Edit:</span></h2>
				<div id="searcheditingdiv" style="display:none">
					<br/>
					<hr/>
					<br/>
					<i>Search:</i>
					<table id="globaledittableFilters" style="width:100%;">
						<tr>
			                <td class="dataleft">
			                	<select name="searcheditlistfields">
									<?php echo $fldlist?>
									<!-- <option disabled>──────────</option> -->
									<?php //echo $customfieldlist?>
								</select>
			                </td>
			                <td class="dataleft">
			                	<select name="operand"><?php echo $operand?></select>
			                </td>
			                <td>
			                	<span id="vaspaneditlists">
			                		<input type="text" name="vaedit" style="width: 200px;">
			                	</span>
			                </td>
			                <td>
			                	<span id="addnewrow">
			                		<img src="../admin/icons/add.gif">
			                	</span>
			                </td>
		                </tr>
					</table>
					<br/>
					<br/>
					<a href="#" class="jbut" id="searcheditlistsbtn" onclick="searcheditlists(<?php echo $multilist ?>);">Search</a>
					<br/><br/>
					<hr/>
					<br/>
					<i>*Operation:</i>
					<select name="applyeditlistfields" id="applyeditlistfields" disabled onchange="changeoperationeditlist();">
						<option value="">-</option>
						<option title="Add at the end" value="append">Append</option>
						<option title="Add at the beginning" value="prepend">Prepend</option>
						<option title="Replace the selective string with new value" value="selectivereplace">Selective Replace</option>
						<option title="Replace with new value" value="replaceall">Replace Text in Selected Column</option>
						<option title="Move values to another column" value="move">Move Column</option>
						<option title="Copy values to another column" value="copy">Copy Column</option>
						<option title="Swap values of two column" value="swap">Swap Column</option>
					</select>
					<br/>
					<br/>
					<div style="display:none" id="newvalueeditlist">
						<span class="expnotif1" style="font-style: italic; font-size: 0.9em"></span>
						<div style="display:none" id="replacevalueeditlist">
						<h3>*Replace value:</h3>
						<textarea name="replacevalue" id="replacevalue" class="replacevalue" style="width: 300px;"></textarea>
						</div>
						<br/>
						<h3>*New value:</h3>
						<textarea name="newvalue" id="newvalue" class="newvalue" style="width: 300px;"></textarea>
						<br/>
						<h3>*Apply to:</h3>
						<select name="applytovalue" id="applytovalue" style="width: 100px;">
							<option value="">-</option>
							<?php echo $leadsrawfieldlist;?>
							<option disabled>──────────</option>
							<?php echo $customfieldlist;?>
						</select>
					</div>
					<div style="display:none" id="swapvalueeditlist">
					<span class="expnotif2" style="font-style: italic; font-size: 0.9em"></span>
					<h3>*From:</h3>
					<select name="fromeditlistfields" id="fromeditlistfields" style="width: 100px;" onchange="checkdefaultcustomfields();">
						<option value="">-</option>
						<?php echo $leadsrawfieldlist;?>
						<option disabled>──────────</option>
						<?php echo $customfieldlist;?>
					</select>
					<br/>
					<h3>*To:</h3>
					<select name="toeditlistfields" id="toeditlistfields" style="width: 100px;" onchange="checkdefaultcustomfields();">
						<option value="">-</option>
						<?php echo $leadsrawfieldlist;?>
						<option disabled>──────────</option>
						<?php echo $customfieldlist;?>
					</select>
				</div>
				</div>
			</div>
			<div id="gnarrow1">
				<br/>
				<button style="font-weight:bold; font-size:1.3em; display:none; width:150px; height: 80px; background-color: #d9534f" id="submiteditlistsbtn" onclick="confirmeditlists(<?php echo $multilist ?>);">Commit changes</button>
				<button style="font-weight:bold; font-size:1.3em; display:none; width:150px; height: 80px; background-color: #d9534f" id="columnsubmiteditlistsbtn" onclick="columnconfirmeditlists(<?php echo $multilist ?>);">Commit changes</button>
			</div>
		</div>
		<br/>
		<hr/><br/>
		<div id="leadsglobaleditresult">
		</div>
		<br/>
		<br/>
		<?php
	}
	exit; 
}
if ($_REQUEST['act'] == "editlead") {
	$f = $_REQUEST['field'];
	$v = $_REQUEST['value'];
	$i = $_REQUEST['leadid'];
	$mysqli->autocommit(FALSE);
	$mysqli->begin_transaction();
	$leads_raw_res = $mysqli->query("SELECT ".$f.", listid from leads_raw where leadid = '".$i."' FOR UPDATE");
	$leads_raw_row = $leads_raw_res->fetch_assoc();
	$mysqli->query("UPDATE leads_raw SET ".$f." = '".$v."' WHERE leadid = '".$i."'");
	$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', leadid = ".$i.", oldvalue = '".$leads_raw_row[$f]."', newvalue = '".$v."', field = '".$f."', epoch = unix_timestamp()");
	$mysqli->commit();
	$mysqli->close();
	exit;
}
if ($_REQUEST['act'] == "editleadcf") {
	$i = $_REQUEST['leadid'];
	$cdata = $_REQUEST['cdata'];
	$mysqli->autocommit(FALSE);
	$mysqli->begin_transaction();
	$leads_raw_res = $mysqli->query("SELECT leads_raw.listid, leads_custom_fields.customfields FROM leads_raw JOIN leads_custom_fields ON leads_custom_fields.leadid = leads_raw.leadid WHERE leads_raw.leadid = '".$i."' FOR UPDATE");
	$leads_raw_row = $leads_raw_res->fetch_assoc();
	$countRows = $leads_raw_res->num_rows;
    if($countRows == 0){
		$mysqli->query("INSERT into leads_custom_fields SET leadid = '$i',customfields='".mysql_real_escape_string($cdata)."'");
    } else {
		$mysqli->query("UPDATE leads_custom_fields SET customfields='".mysql_real_escape_string($cdata)."' where leadid = '$i'");    
	}
	$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
	leadid = ".$i.", oldvalue = '".$leads_raw_row["customfields"]."', newvalue = '".mysql_real_escape_string($cdata)."', field = 'customfields', epoch = unix_timestamp()");
	$mysqli->commit();
	$mysqli->close();
	exit;
}
if ($_REQUEST['act'] == 'submiteditlists') {
	$multilistids = $_POST['lid'];
	$fdata = filterdata($_POST['data']);
	$tableData = $_POST['data'];
	$operation = $_POST['operation'];
	$_from = $_POST['from'];
	$_to = $_POST['to'];
	$replacevalue = $_POST['replacevalue'];
	$newvalue = $_POST['newvalue'];
	$_applytovalue = $_POST['applytovalue'];
	$frompos = strpos($_from, "cf|");
	$topos = strpos($_to, "cf|");
	$applytopos = strpos($_applytovalue, "cf|");
	if (isset($_from) || isset($_to)|| isset($_applytovalue)) {
		if (($frompos !== false && $topos === false) || ($frompos === false && $topos !== false)) {
			echo "Editing between default and custom fields are NOT allowed. This can work ONLY between default-default and custom-custom fields.";
		} else {
			$mysqli->autocommit(FALSE);
			$mysqli->begin_transaction();
			$reslists = $mysqli->query("SELECT * from lists where lid = ".$multilistids." and bcid = ".$bcid." and active = 1 FOR UPDATE");
			$rowlists = $reslists->fetch_assoc();
			$projectid = $rowlists["projects"];
			$records = lists::listrecordseditcampaignlists($bcid, $projectid, NULL, $multilistids, $fdata);
			foreach ($records['records'] as $record) {
				$leadid = $record["leadid"];
				// SWAP, MOVE AND COPY
				if (isset($_from) || isset($_to)) {
					if ($frompos === false && $topos === false) {
						$leads_raw_res = $mysqli->query("SELECT ".$_from.", ".$_to.", listid from leads_raw where leadid = '".$leadid."'");
						$leads_raw_row = $leads_raw_res->fetch_assoc();
						if ($operation == "swap") {
							$mysqli->query("UPDATE leads_raw SET ".$_from."=@tmp:=".$_from.", ".$_from."=".$_to.", ".$_to."=@tmp WHERE leadid = '".$leadid."'");    
							$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
							leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$_from].",".$leads_raw_row[$_to]."', newvalue = '".$leads_raw_row[$_to].",".$leads_raw_row[$_from]."', field = '".$operation."[".$_from.",".$_to."]', epoch = unix_timestamp()");
						} else if ($operation == "move") {
							$nv = $leads_raw_row[$_from];
							$mysqli->query("UPDATE leads_raw SET ".$_to." = '".$nv."', ".$_from." = '' WHERE leadid = '".$leadid."'");
							$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
							leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$_from].",".$leads_raw_row[$_to]."', newvalue = '".$nv."', field = '".$operation."[".$_from.",".$_to."]', epoch = unix_timestamp()");
						} else if ($operation == "copy") {
							$nv = $leads_raw_row[$_from];
							$mysqli->query("UPDATE leads_raw SET ".$_to." = '".$nv."' WHERE leadid = '".$leadid."'");
							$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
							leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$_from].",".$leads_raw_row[$_to]."', newvalue = '".$leads_raw_row[$_from].",".$nv."', field = '".$operation."[".$_from.",".$_to."]', epoch = unix_timestamp()");
						}
					} else if ($frompos !== false && $topos !== false) {
						$from_rep = str_replace("cf|", "", $_from);
						$to_rep = str_replace("cf|", "", $_to);
						$leads_raw_res = $mysqli->query("SELECT listid from leads_raw where leadid = '".$leadid."'");
						$leads_raw_row = $leads_raw_res->fetch_assoc();
						$customfieldres = $mysqli->query("SELECT * from leads_custom_fields where leadid = ".$leadid."");
						$customfieldrow = $customfieldres->fetch_assoc();
						$cfields  = json_decode($customfieldrow['customfields'], true);
						$countRows = $customfieldres->num_rows;
						if($countRows == 0){
							$countrows_message1 = 1;
						} else {
							if (array_key_exists($from_rep,$cfields) && array_key_exists($to_rep,$cfields)) {
								// OLD VALUE
								foreach ($cfields as $key => $value) {
									if ($from_rep == $key) {
										$from = $value;
									}
									if ($to_rep == $key) {
										$to = $value;
									}
								}
								if ($operation == "swap") {
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($from_rep == $key) {
											$newcf[$key] = $to;
										} else if ($to_rep == $key) {
											$newcf[$key] = $from;
										} else {
											$newcf[$key] = $value;
										}
									}
									$mysqli->query("UPDATE leads_custom_fields SET customfields = '".json_encode($newcf)."' WHERE leadid = '".$leadid."'");
									$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
									leadid = ".$leadid.", oldvalue = '".$from.", ".$to."', newvalue = '".$to.", ".$from."', field = 'customfields_".$operation."[".$from_rep.",".$to_rep."]', epoch = unix_timestamp()");	
								} else if ($operation == "move") {
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($from_rep == $key) {
											$newcf[$key] = "";
										} else if ($to_rep == $key) {
											$newcf[$key] = $from;
										} else {
											$newcf[$key] = $value;
										}
									}
									$mysqli->query("UPDATE leads_custom_fields SET customfields = '".json_encode($newcf)."' WHERE leadid = '".$leadid."'");
									$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
									leadid = ".$leadid.", oldvalue = '".$from.", ".$to."', newvalue = '".$from."', field = 'customfields_".$operation."[".$from_rep.",".$to_rep."]', epoch = unix_timestamp()");
								} else if ($operation == "copy") {
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($from_rep == $key) {
											$newcf[$key] = $from;
										} else if ($to_rep == $key) {
											$newcf[$key] = $from;
										} else {
											$newcf[$key] = $value;
										}
									}
									$mysqli->query("UPDATE leads_custom_fields SET customfields = '".json_encode($newcf)."' WHERE leadid = '".$leadid."'");
									$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
									leadid = ".$leadid.", oldvalue = '".$from.", ".$to."', newvalue = '".$from.", ".$from."', field = 'customfields_".$operation."[".$from_rep.",".$to_rep."]', epoch = unix_timestamp()");
								}
							} else {
								$arraykeyexist_message1 = 1;
							}
						}
					}
				}
				// APPEND, PREPEND, REPLACE ALL AND SELECTIVE REPLACE
				if (isset($_applytovalue)) {
					if ($applytopos === false) {
						$leads_raw_res = $mysqli->query("SELECT ".$_applytovalue.", listid from leads_raw where leadid = '".$leadid."'");
						$leads_raw_row = $leads_raw_res->fetch_assoc();
						if ($operation == "append") {
							$nv = $leads_raw_row[$_applytovalue]."".$newvalue;
						} else if ($operation == "prepend") {
							$nv = $newvalue."".$leads_raw_row[$_applytovalue];
						} else if ($operation == "selectivereplace") {
							$nv = str_replace($replacevalue, $newvalue, $leads_raw_row[$_applytovalue]);
						} else if ($operation == "replaceall") {
							$nv = $newvalue;
						}
						$mysqli->query("UPDATE leads_raw SET ".$_applytovalue." = '".$nv."' WHERE leadid = '".$leadid."'");	
						$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
						leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$_applytovalue]."', newvalue = '".$nv."', field = '".$operation."[".$_applytovalue."]', epoch = unix_timestamp()");
					} else if ($applytopos !== false) {
						$applytovalue_rep = str_replace("cf|", "", $_applytovalue);
						$leads_raw_res = $mysqli->query("SELECT listid from leads_raw where leadid = '".$leadid."'");
						$leads_raw_row = $leads_raw_res->fetch_assoc();
						$customfieldres = $mysqli->query("SELECT * from leads_custom_fields where leadid = ".$leadid."");
						$customfieldrow = $customfieldres->fetch_assoc();
						$cfields  = json_decode($customfieldrow['customfields'], true);
						$countRows = $customfieldres->num_rows;
						if($countRows == 0){
							$countrows_message2 = 1;
						} else {
							if (array_key_exists($applytovalue_rep, $cfields)) {
								// OLD VALUE
								foreach ($cfields as $key => $value) {
									if ($applytovalue_rep == $key) {
										$applytovalue = $value;
									}
								}
								if ($operation == "append") {
									$nv = $applytovalue."".$newvalue;
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($applytovalue_rep == $key) {
											$newcf[$key] = $nv;
										} else {
											$newcf[$key] = $value;
										}
									}
								} else if ($operation == "prepend") {
									$nv = $newvalue."".$applytovalue;
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($applytovalue_rep == $key) {
											$newcf[$key] = $nv;
										} else {
											$newcf[$key] = $value;
										}
									}
								} else if ($operation == "selectivereplace") {
									$nv = str_replace($replacevalue, $newvalue, $applytovalue);
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($applytovalue_rep == $key) {
											$newcf[$key] = $nv;
										} else {
											$newcf[$key] = $value;
										}
									}
								} else if ($operation == "replaceall") {
									$nv = $newvalue;
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($applytovalue_rep == $key) {
											$newcf[$key] = $nv;
										} else {
											$newcf[$key] = $value;
										}
									}
								}
								$mysqli->query("UPDATE leads_custom_fields SET customfields = '".json_encode($newcf)."' WHERE leadid = '".$leadid."'");	
								$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
								leadid = ".$leadid.", oldvalue = '".$applytovalue."', newvalue = '".$nv."', field = 'customfields_".$operation."[".$applytovalue_rep."]', epoch = unix_timestamp()");
							} else {
								$arraykeyexist_message2 = 1;
							}
						}
					}
				}
			}
			$mysqli->commit();
			$mysqli->close();
			if ($countrows_message1 != NULL || $countrows_message2 != NULL || $arraykeyexist_message1 != NULL || $arraykeyexist_message2 != NULL) {
				echo "The selected custom field(s) was not mapped when the list uploaded. Bulk Editing for the custom field(s) not allowed. Please make sure you selected the custom field(s) that are mapped.";
			} else {
				echo "Changes were committed successfully.";
			}
			
		}
	}
}
if ($_REQUEST['act'] == 'columnsubmiteditlists') {
	$lid = $_POST['lid'];
	$operation = $_POST['operation'];
	$_from = $_POST['from'];
	$_to = $_POST['to'];
	$replacevalue = $_POST['replacevalue'];
	$newvalue = $_POST['newvalue'];
	$_applytovalue = $_POST['applytovalue'];
	$frompos = strpos($_from, "cf|");
	$topos = strpos($_to, "cf|");
	$applytopos = strpos($_applytovalue, "cf|");
	if (isset($_from) || isset($_to)|| isset($_applytovalue)) {
		if (($frompos !== false && $topos === false) || ($frompos === false && $topos !== false)) {
			echo "Editing between default and custom fields are NOT allowed. This can work ONLY between default-default and custom-custom fields.";
		} else {
			$mysqli->autocommit(FALSE);
			$mysqli->begin_transaction();
			$getleadidres = $mysqli->query("SELECT leads_raw.leadid FROM leads_raw JOIN lists ON lists.listid = leads_raw.listid WHERE lists.bcid = '".$bcid."' AND lists.lid = '".$lid."'");
			while ($getleadidrow = $getleadidres->fetch_assoc()) {
				$leadid = $getleadidrow["leadid"];
				$frompos = strpos($_from, "cf|");
				$topos = strpos($_to, "cf|");
				$applytopos = strpos($_applytovalue, "cf|");
				// SWAP, MOVE AND COPY
				if (isset($_from) || isset($_to)) {
					if ($frompos === false && $topos === false) {
						$leads_raw_res = $mysqli->query("SELECT ".$_from.", ".$_to.", listid from leads_raw where leadid = '".$leadid."'");
						$leads_raw_row = $leads_raw_res->fetch_assoc();
						if ($operation == "swap") {
							$mysqli->query("UPDATE leads_raw SET ".$_from."=@tmp:=".$_from.", ".$_from."=".$_to.", ".$_to."=@tmp WHERE leadid = '".$leadid."'");    
							$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
							leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$_from].",".$leads_raw_row[$_to]."', newvalue = '".$leads_raw_row[$_to].",".$leads_raw_row[$_from]."', field = '".$operation."[".$_from.",".$_to."]', epoch = unix_timestamp()");
						} else if ($operation == "move") {
							$nv = $leads_raw_row[$_from];
							$mysqli->query("UPDATE leads_raw SET ".$_to." = '".$nv."', ".$_from." = '' WHERE leadid = '".$leadid."'");
							$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
							leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$_from].",".$leads_raw_row[$_to]."', newvalue = '".$nv."', field = '".$operation."[".$_from.",".$_to."]', epoch = unix_timestamp()");
						} else if ($operation == "copy") {
							$nv = $leads_raw_row[$_from];
							$mysqli->query("UPDATE leads_raw SET ".$_to." = '".$nv."' WHERE leadid = '".$leadid."'");
							$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
							leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$_from].",".$leads_raw_row[$_to]."', newvalue = '".$leads_raw_row[$_from].",".$nv."', field = '".$operation."[".$_from.",".$_to."]', epoch = unix_timestamp()");
						}
					} else {
						$from_rep = str_replace("cf|", "", $_from);
						$to_rep = str_replace("cf|", "", $_to);
						$leads_raw_res = $mysqli->query("SELECT listid from leads_raw where leadid = '".$leadid."'");
						$leads_raw_row = $leads_raw_res->fetch_assoc();
						$customfieldres = $mysqli->query("SELECT * from leads_custom_fields where leadid = ".$leadid."");
						$customfieldrow = $customfieldres->fetch_assoc();
						$cfields  = json_decode($customfieldrow['customfields'], true);
						$countRows = $customfieldres->num_rows;
						if($countRows == 0){
							$countrows_message1 = 1;
						} else {
							if (array_key_exists($from_rep,$cfields) && array_key_exists($to_rep,$cfields)) {
								// OLD VALUE
								foreach ($cfields as $key => $value) {
									if ($from_rep == $key) {
										$from = $value;
									}
									if ($to_rep == $key) {
										$to = $value;
									}
								}
								if ($operation == "swap") {
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($from_rep == $key) {
											$newcf[$key] = $to;
										} else if ($to_rep == $key) {
											$newcf[$key] = $from;
										} else {
											$newcf[$key] = $value;
										}
									}
									$mysqli->query("UPDATE leads_custom_fields SET customfields = '".json_encode($newcf)."' WHERE leadid = '".$leadid."'");
									$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
									leadid = ".$leadid.", oldvalue = '".$from.", ".$to."', newvalue = '".$to.", ".$from."', field = 'customfields_".$operation."[".$from_rep.",".$to_rep."]', epoch = unix_timestamp()");	
								} else if ($operation == "move") {
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($from_rep == $key) {
											$newcf[$key] = "";
										} else if ($to_rep == $key) {
											$newcf[$key] = $from;
										} else {
											$newcf[$key] = $value;
										}
									}
									$mysqli->query("UPDATE leads_custom_fields SET customfields = '".json_encode($newcf)."' WHERE leadid = '".$leadid."'");
									$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
									leadid = ".$leadid.", oldvalue = '".$from.", ".$to."', newvalue = '".$from."', field = 'customfields_".$operation."[".$from_rep.",".$to_rep."]', epoch = unix_timestamp()");
								} else if ($operation == "copy") {
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($from_rep == $key) {
											$newcf[$key] = $from;
										} else if ($to_rep == $key) {
											$newcf[$key] = $from;
										} else {
											$newcf[$key] = $value;
										}
									}
									$mysqli->query("UPDATE leads_custom_fields SET customfields = '".json_encode($newcf)."' WHERE leadid = '".$leadid."'");
									$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
									leadid = ".$leadid.", oldvalue = '".$from.", ".$to."', newvalue = '".$from.", ".$from."', field = 'customfields_".$operation."[".$from_rep.",".$to_rep."]', epoch = unix_timestamp()");
								}
							} else {
								$arraykeyexist_message1 = 1;
							}
						}
					}
				}
				// APPEND, PREPEND, REPLACE ALL AND SELECTIVE REPLACE
				if (isset($_applytovalue)) {
					if ($applytopos === false) {
						$leads_raw_res = $mysqli->query("SELECT ".$_applytovalue.", listid from leads_raw where leadid = '".$leadid."'");
						$leads_raw_row = $leads_raw_res->fetch_assoc();
						if ($operation == "append") {
							$nv = $leads_raw_row[$_applytovalue]."".$newvalue;
						} else if ($operation == "prepend") {
							$nv = $newvalue."".$leads_raw_row[$_applytovalue];
						} else if ($operation == "selectivereplace") {
							$nv = str_replace($replacevalue, $newvalue, $leads_raw_row[$_applytovalue]);
						} else if ($operation == "replaceall") {
							$nv = $newvalue;
						}
						$mysqli->query("UPDATE leads_raw SET ".$_applytovalue." = '".$nv."' WHERE leadid = '".$leadid."'");	
						$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
						leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$_applytovalue]."', newvalue = '".$nv."', field = '".$operation."[".$_applytovalue."]', epoch = unix_timestamp()");
					} else if ($applytopos !== false) {
						$applytovalue_rep = str_replace("cf|", "", $_applytovalue);
						$leads_raw_res = $mysqli->query("SELECT listid from leads_raw where leadid = '".$leadid."'");
						$leads_raw_row = $leads_raw_res->fetch_assoc();
						$customfieldres = $mysqli->query("SELECT * from leads_custom_fields where leadid = ".$leadid."");
						$customfieldrow = $customfieldres->fetch_assoc();
						$cfields  = json_decode($customfieldrow['customfields'], true);
						$countRows = $customfieldres->num_rows;
						if($countRows == 0){
							$countrows_message2 = 1;
						} else {
							if (array_key_exists($applytovalue_rep, $cfields)) {
								// OLD VALUE
								foreach ($cfields as $key => $value) {
									if ($applytovalue_rep == $key) {
										$applytovalue = $value;
									}
								}
								if ($operation == "append") {
									$nv = $applytovalue."".$newvalue;
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($applytovalue_rep == $key) {
											$newcf[$key] = $nv;
										} else {
											$newcf[$key] = $value;
										}
									}
								} else if ($operation == "prepend") {
									$nv = $newvalue."".$applytovalue;
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($applytovalue_rep == $key) {
											$newcf[$key] = $nv;
										} else {
											$newcf[$key] = $value;
										}
									}
								} else if ($operation == "selectivereplace") {
									$nv = str_replace($replacevalue, $newvalue, $applytovalue);
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($applytovalue_rep == $key) {
											$newcf[$key] = $nv;
										} else {
											$newcf[$key] = $value;
										}
									}
								} else if ($operation == "replaceall") {
									$nv = $newvalue;
									// NEW VALUE
									foreach ($cfields as $key => $value) {
										if ($applytovalue_rep == $key) {
											$newcf[$key] = $nv;
										} else {
											$newcf[$key] = $value;
										}
									}
								}
								$mysqli->query("UPDATE leads_custom_fields SET customfields = '".json_encode($newcf)."' WHERE leadid = '".$leadid."'");	
								$mysqli->query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
								leadid = ".$leadid.", oldvalue = '".$applytovalue."', newvalue = '".$nv."', field = 'customfields_".$operation."[".$applytovalue_rep."]', epoch = unix_timestamp()");
								
							} else {
								$arraykeyexist_message2 = 1;
							}
						}
					}
				}
			}
			$mysqli->commit();
			$mysqli->close();
			if ($countrows_message1 != NULL || $countrows_message2 != NULL || $arraykeyexist_message1 != NULL || $arraykeyexist_message2 != NULL) {
				echo "The selected custom field(s) was not mapped when the list uploaded. Bulk Editing for the custom field(s) not allowed. Please make sure you selected the custom field(s) that are mapped.";
			} else {
				echo "Changes were committed successfully. You can view the results by exporting the list or by clicking the Row Editing.";
			}
		}
	}
}
?>