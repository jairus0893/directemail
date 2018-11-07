<?php
include_once "../../dbconnect.php";
require "../../classes/classes.php";
require "../../classes/lists.php";
include "../../phpfunctions.php";
include "../../admin/editcampaignlists/editcampaignlists-include.php";
require "phpfunctions-include.php";
session_start();
// SERVER UNIX_TIMESTAMP
$unixtimestamp_res = mysql_query("SELECT UNIX_TIMESTAMP()");
$unixtimestamp_row = mysql_fetch_assoc($unixtimestamp_res);
$unixtimestamp = $unixtimestamp_row["UNIX_TIMESTAMP()"];
$leadsrawfieldlist = "";
if ($_REQUEST['act'] == "selectcampaignlists") {
	$multilistids = $_REQUEST['listids'];
	$projectid = $_REQUEST["projid"];
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
	foreach ($viewfields as $key => $vf) {
	    $viewfields[$key][1] = 0;
	}
	foreach ($vfs as $vf) {
	    $viewfields[$vf][1] = 1;
	}
	foreach ($viewfields as $key => $value) {
	    if ($value[1] && !$value[2]) {
	        $exportfields[] = $key;
	    }
	    if ($key == 'notes' && $value[1] == 1) {
	        $isNotes = true;
	    }
	}
	if($isNotes){
	    $key = array_search('notes', $exportfields);
	    unset($exportfields[$key]);
	}
	if ($_REQUEST["act1"] == "columnsearch") {
		$tableData = stripcslashes($_REQUEST['data']);
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
		$records = lists::listrecordseditcampaignlists($bcid, $projectid, $exportfields, $multilist, $fdata);
	} else if ($_REQUEST["act1"] == "columnedit") {
			$records = lists::listrecordseditcampaignlists($bcid, $projectid, $exportfields, $multilist);
	} else {
		$records = lists::listrecordseditcampaignlists($bcid, $projectid, $exportfields, $multilist);
	}
	
	$resprojects	= mysql_query("SELECT * from projects where projectid = '".$projectid."' AND bcid = '".$bcid." AND active = 1");
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
    
    // LEADS RAW FIELD LIST 
	$leadsrawfieldlist = "";
	$labels = new labels();
	$leads_raw_fieldres = mysql_query("SHOW COLUMNS FROM leads_raw");
	while ($leads_raw_fieldrow = mysql_fetch_assoc($leads_raw_fieldres)) {
		if (in_array($leads_raw_fieldrow["Field"], $headers)) {
			$label = $labels->get($leads_raw_fieldrow["Field"]);
			$leadsrawfieldlist .= '<option value="'.$leads_raw_fieldrow["Field"].'">'.ucfirst($label).'</option>';
		}
	}
	if ($_REQUEST["act1"] == "columnsearch" || $_REQUEST["act1"] == "columnedit") {
		$result = '';
		$result .= '
	    <table class="editcampaignmultipleliststable">
		<thead>
			<tr>';
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
						$result .= '<td><input type="hidden" name="editcampaignlistsleadid[]" value="'.$row['leadid'].'">'.$row[$header].'</td>';
					} else {
						$pos = strpos($header, "*");
						if ($pos === false) {
							$result .= '<td><input type="text" name="editlistslead" disabled onchange="triggerchange()" onblur="editcampaignlead(this,\'' . $row['leadid'] . '\',\'' . $header . '\')" value="'.$row[$header].'"/></td>';
						} else {
							$result .= '<td><input type="text" class="editcampaignlistsleadcf_'.$row['leadid'].'" name="'.ltrim($header, "*").'" disabled onchange="triggerchange()" onblur="editcampaignlead(this,\'' . $row['leadid'] . '\',\'' . $header . '\')" value="'.$row[$header].'"/></td>';
						}
					}
				}
				$result .= '</tr>';
			}
		$result .= '</tbody>
		</table>';
		echo $result;
	} else if ($_REQUEST["act1"] == "editcampaignlistsinline") {
		$result = '';
		$result .= '
	    <table class="editcampaignmultipleliststable">
		<thead>
			<tr>';
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
						$result .= '<td><input type="hidden" name="editcampaignlistsleadid" value="'.$row['leadid'].'">'.$row[$header].'</td>';
					} else {
						$pos = strpos($header, "*");
						if ($pos === false) {
							$result .= '<td><input type="text" name="editlistslead" onchange="triggerchange()" onblur="editcampaignlead(this,\'' . $row['leadid'] . '\',\'' . $header . '\')" value="'.$row[$header].'"/></td>';
						} else {
							$result .= '<td><input type="text" class="editcampaignlistsleadcf_'.$row['leadid'].'" name="'.ltrim($header, "*").'" onchange="triggerchange()" onblur="editcampaignlead(this,\'' . $row['leadid'] . '\',\'' . $header . '\')" value="'.$row[$header].'"/></td>';
						}
					}
				}
				$result .= '</tr>';
			}
		$result .= '</tbody>
		</table>';
		echo $result;
	} else if ($_REQUEST['act1'] == "editcampaignlists") {
		$bcid = $_REQUEST['bcid'];
		$prores = mysql_query("SELECT * from projects where bcid = '".$bcid."' AND active = 1 ORDER BY projectname");
		while ($prorow = mysql_fetch_array($prores)) {
			$prolist .= '<option value="'.$prorow['projectid'].'">'.$prorow['projectname'].'</option>';
		}
		if ($_REQUEST["sub"] == "defaulteditcampaignlists") {
			?>
			<div class="ecloading">    
		        <img src="../admin/loading_big.gif" alt="loading" />             
		    </div>
		    <br/>
			<div id="eclparent">
			  <div id="eclwide">
			  	<h3><p style="font-size:1.2em">*Campaign:</p></h3>
				<br/>
				<select name="projectideditlist" id="projectideditlist" onchange="campaignlistchange();">
					<option value="<?=$campaignlistidrow["projectid"];?>" selected="selected"><?=ucfirst($campaignlistidrow["projectname"]);?></option>
					<?=$prolist;?>
				</select>
			  </div>
			  <div id="eclnarrow">
			  	<h3><p style="font-size:1.2em">*Lists:</p></h3>
				<br/>
				<select name="editlistids[]" id="editlistids" multiple="multiple">
					<?=$listidlist;?>
				</select>
				<br/>
				<i><p style="font-size:xx-small;">Hold down the Ctrl (Windows) / Command (Mac) button to select multiple options.</p></i>
				<input type="hidden" id="campaignlistids"/>
			  </div>
			</div>
			<a href="#" class="jbut" onclick="selectcampaignlists();">Submit</a>
			<br/>
			<br/>
			<div id="tabs">
			    <div class="active" id="inlinecampaigntab" onclick="listsviewtab('inlinecampaign','<?php echo $bcid;?>')"><p style="font-size:1.2em">Row Editing</p></div>
			    <div class="inactive" id="globalcampaigntab" onclick="listsviewtab('globalcampaign','<?php echo $bcid;?>')"><p style="font-size:1.2em">Bulk Editing</p></div>
			    <div class="clear"></div>
			    <div id="boundary"></div>
			</div>
			<div class="clear"></div>
			<div id="tabcont">
				<div id="tabinlinecampaign" class="tabc">
					<div class="clear"></div>
					<div class="leadseditcampaignresult">
					</div>
					<br/>
					<br/>
				</div>
				<div id="tabglobalcampaign" class="tabc">
				</div>
			</div>
			<?php	
		} else if ($_REQUEST["sub"] == "inlineeditcampaign") {
			?>
			<div class="ecloading">    
		        <img src="../admin/loading_big.gif" alt="loading" />             
		    </div>
			<div class="clear"></div>
			<div class="leadseditcampaignresult">
			</div>
			<br/>
			<br/>
			<?php
		} else if ($_REQUEST["sub"] == "globaleditcampaign") {
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
			<div class="ecloading">    
		        <img src="../admin/loading_big.gif" alt="loading" />             
		    </div>
			<div class="clear"></div>
			<br/>
			<div id="gcparent">
				<div id="gcwide">
					<h2><input name="editingradiobtn" id="globalsearcheditingbtn" type="radio"/> <span style="font-size:1.3em"> Select Records From List(s) To Edit:</span></h2>
					<div id="globalsearcheditingdiv" style="display:none">
						<br/>
						<hr/>
						<br/>
						<i>Search:</i>
						<table id="globaleditcampaigntableFilters" style="width:100%;">
							<tr>
				                <td class="dataleft">
				                	<select name="searcheditcampaignlistfields"><?php echo $fldlist?></select>
				                </td>
				                <td class="dataleft">
				                	<select name="operandcampaign"><?php echo $operand?></select>
				                </td>
				                <td>
				                	<span id="vaspaneditcampaignlists">
				                		<input type="text" name="vaeditcampaign" style="width: 200px;">
				                	</span>
				                </td>
				                <td>
				                	<span id="addnewroweditcampaign">
				                		<img src="../admin/icons/add.gif">
				                	</span>
				                </td>
			                </tr>
						</table>
						<br/>
						<br/>
						<a href="#" class="jbut" id="searcheditcampaignlistsbtn" onclick="searcheditcampaignlists();">Search</a>
						<br/><br/>
						<hr/>
						<br/>
						<i>Operation:</i>
						<select name="applyeditcampaignlistfields" id="applyeditcampaignlistfields" disabled onchange="changeoperationeditcampaignlist();">
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
						<div style="display:none" id="newvalueeditcampaignlist">
							<span class="expnotif3" style="font-style: italic; font-size: 0.9em"></span>
							<div style="display:none" id="replacevalueeditcampaignlist">
							<h3>*Replace value:</h3>
							<textarea name="replacevaluecampaign" id="replacevaluecampaign" class="replacevaluecampaign" style="width: 300px;"></textarea>
							</div>
							<br/>
							<h3>*New value:</h3>
							<textarea name="newvaluecampaign" id="newvaluecampaign" class="newvaluecampaign" style="width: 300px"></textarea>
							<br/>
							<h3>*Apply to:</h3>
							<select name="applytovalueglobal" id="applytovalueglobal" style="width: 100px;">
								<option value="">-</option>
								<?=$leadsrawfieldlist;
								?>
							</select>
						</div>
						<div style="display:none" id="swapvalueeditcampaignlist">
							<span class="expnotif4" style="font-style: italic; font-size: 0.9em"></span>
							<h3>*From:</h3>
							<select name="fromeditcampaignlistfields" id="fromeditcampaignlistfields" style="width: 100px;"">
								<option value="">-</option>
								<?=$leadsrawfieldlist;
								?>
							</select>
							<br/>
							<h3>*To:</h3>
							<select name="toeditcampaignlistfields" id="toeditcampaignlistfields" style="width: 100px;"">
								<option value="">-</option>
								<?=$leadsrawfieldlist;
								?>
							</select>
						</div>
					</div>
				</div>
				<div id="gcnarrow">
					<h2><input name="editingradiobtn" id="globalcolumneditingbtn" type="radio"/> <span style="color:red; font-size: 1.3em"> EDIT ALL RECORDS IN THE LIST(S):</span></h2>
					<div id="globalcolumneditingdiv" style="display:none">
						<br/>
						<hr/>
						<br/>
						<i>Operation:</i>
						<select name="columnapplyeditcampaignlistfields" id="columnapplyeditcampaignlistfields" onchange="columnchangeoperationeditcampaignlist();">
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
						<div style="display:none" id="columnnewvalueeditcampaignlist">
							<span class="expnotif5" style="font-style: italic; font-size: 0.9em"></span>
							<div style="display:none" id="columnreplacevalueeditcampaignlist">
							<h3>*Replace value:</h3>
							<textarea name="columnreplacevaluecampaign" id="columnreplacevaluecampaign" class="columnreplacevaluecampaign" style="width: 300px;"></textarea>
							</div>
							<br/>
							<h3>*New value:</h3>
							<textarea name="columnnewvaluecampaign" id="columnnewvaluecampaign" class="columnnewvaluecampaign" style="width: 300px"></textarea>
							<br/>
							<h3>*Apply to:</h3>
							<select name="columnapplytovalueglobal" id="columnapplytovalueglobal" style="width: 100px;">
								<option value="">-</option>
								<?=$leadsrawfieldlist;
								?>
							</select>
						</div>
						<div style="display:none" id="columnswapvalueeditcampaignlist">
							<span class="expnotif6" style="font-style: italic; font-size: 0.9em"></span>
							<h3>*From:</h3>
							<select name="columnfromeditcampaignlistfields" id="columnfromeditcampaignlistfields" style="width: 100px;"">
								<option value="">-</option>
								<?=$leadsrawfieldlist;
								?>
							</select>
							<br/>
							<h3>*To:</h3>
							<select name="columntoeditcampaignlistfields" id="columntoeditcampaignlistfields" style="width: 100px;"">
								<option value="">-</option>
								<?=$leadsrawfieldlist;
								?>
							</select>
						</div>
					</div>
				</div>
				<div id="gcnarrow1">
					<br/>
					<button style="font-weight:bold; font-size:1.3em; display:none; width:150px; height: 80px; background-color: #d9534f" id="submiteditcampaignlistsbtn" onclick="confirmeditcampaignlists();">Commit changes</button>
					<button style="font-weight:bold; font-size:1.3em; display:none; width:150px; height: 80px; background-color: #d9534f" id="columnsubmiteditcampaignlistsbtn" onclick="columnconfirmeditcampaignlists();">Commit changes</button>
				</div>
			</div>
			<br/>
			<hr/><br/>
			<div id="leadsglobaleditcampaignresult">
			</div>
			<br/>
			<br/>
			<?php
		}
	}
	exit;
}
if (!empty($_REQUEST['lid'])) {
	$campaignlistidres = mysql_query("SELECT lists.lid, lists.listid, projects.projectid, projects.projectname FROM lists JOIN projects ON projects.projectid = lists.projects WHERE lists.bcid = '".$bcid."' AND lists.lid = ".$lid."");
	$campaignlistidrow = mysql_fetch_assoc($campaignlistidres);
	$listidlist .= '<option value="'.$lid.'" selected="selected">'.$campaignlistidrow['listid'].'</option>';
	$listidlist .= '<option value="all">All</option>';
	$listres = mysql_query("SELECT * FROM lists WHERE projects = ".$campaignlistidrow["projectid"]." AND bcid = '".$bcid."' AND active = 1 ORDER BY listid");
	while ($listrow = mysql_fetch_array($listres)) {
		$listidlist .= '<option value="'.$listrow['lid'].'">'.$listrow['listid'].'</option>';
	}
}
if ($_REQUEST['act'] == "editlead") {
	$f = $_REQUEST['field'];
	$v = $_REQUEST['value'];
	$i = $_REQUEST['leadid'];
	$leads_raw_res = mysql_query("SELECT ".$f.", listid from leads_raw where leadid = '".$i."'");
    $leads_raw_row = mysql_fetch_assoc($leads_raw_res);
	mysql_query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
	leadid = ".$i.", oldvalue = '".$leads_raw_row[$f]."', newvalue = '".$v."', field = '".$f."', epoch = '".$unixtimestamp."'");
	mysql_query("UPDATE leads_raw SET ".$f." = '".$v."' WHERE leadid = '".$i."'");
	exit;
}
if ($_REQUEST['act'] == "editcampaignlistsleadcf") {
	$i = $_REQUEST['leadid'];
	$cdata = $_REQUEST['cdata'];
    $selectcf = mysql_query("SELECT * from leads_custom_fields where leadid = '".$i."'");
    $countRows = mysql_num_rows($selectcf);
    if($countRows == 0){
      mysql_query("INSERT into leads_custom_fields SET leadid = '$i',customfields='".  mysql_real_escape_string($cdata)."'");
    } else {
      mysql_query("UPDATE leads_custom_fields SET customfields='".  mysql_real_escape_string($cdata)."' where leadid = '$i'");    
    }
	exit;
}
if ($_REQUEST['act'] == 'submitteditcampaignlists') {
	$multilistids = $_POST['lid'];
	$projid = $_POST['projid'];
	$tableData = $_POST['data'];
	$operation = $_POST['operation'];
	$from = $_POST['from']; 
	$to = $_POST['to'];
	if (strpos($multilistids, ",")) {
		$multilist = explode(",", $multilistids);
	} else {
		$multilist = $multilistids;
	}
	$reslists = mysql_query("SELECT * from lists where lid IN (".$multilistids.") AND bcid = '".$bcid."' AND active = 1");
	$rowlists = mysql_fetch_assoc($reslists);
	$projectid = $rowlists["projects"];
	$records = lists::listrecordseditcampaignlists($bcid, $projectid, NULL, $multilist, $fdata);
	foreach ($records['records'] as $record) {
		$leadid = $record["leadid"];
		if ($operation == "swap") {
			$leads_raw_res = mysql_query("SELECT ".$from.", ".$to.", listid from leads_raw where leadid = '".$leadid."'");
		    $leads_raw_row = mysql_fetch_assoc($leads_raw_res);
			mysql_query("UPDATE leads_raw SET ".$from."=@tmp:=".$from.", ".$from."=".$to.", ".$to."=@tmp WHERE leadid = '".$leadid."'");    
	    	mysql_query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
			leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$from]."', newvalue = '".$leads_raw_row[$to]."', field = '".$from.",".$to."', epoch = '".$unixtimestamp."'");
		} else if ($operation == "move") {
			$leads_raw_res = mysql_query("SELECT ".$from.", ".$to.", listid from leads_raw where leadid = '".$leadid."'");
		    $leads_raw_row = mysql_fetch_assoc($leads_raw_res);
			$nv = $leads_raw_row[$from];
			mysql_query("UPDATE leads_raw SET ".$to." = '".$nv."', ".$from." = '' WHERE leadid = '".$leadid."'");
	    	mysql_query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
			leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$to]."', newvalue = '".$leads_raw_row[$from]."', field = '".$from.",".$to."', epoch = '".$unixtimestamp."'");
		} else if ($operation == "copy") {
			$leads_raw_res = mysql_query("SELECT ".$from.", ".$to.", listid from leads_raw where leadid = '".$leadid."'");
		    $leads_raw_row = mysql_fetch_assoc($leads_raw_res);
			$nv = $leads_raw_row[$from];
			mysql_query("UPDATE leads_raw SET ".$to." = '".$nv."' WHERE leadid = '".$leadid."'");
			mysql_query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
			leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$to]."', newvalue = '".$leads_raw_row[$from]."', field = '".$from.",".$to."', epoch = '".$unixtimestamp."'");
		} else {
	    	$replacevalue = $_POST['replacevalue'];
			$newvalue = $_POST['newvalue'];
			$applytovalue = $_POST['applytovalue'];
			
			$leads_raw_res = mysql_query("SELECT ".$applytovalue.", listid from leads_raw where leadid = '".$leadid."'");
		    $leads_raw_row = mysql_fetch_assoc($leads_raw_res);
			if ($operation == "append") {
				$nv = $leads_raw_row[$applytovalue]."".$newvalue;
				mysql_query("UPDATE leads_raw SET ".$applytovalue." = '".$nv."' WHERE leadid = '".$leadid."'");	
			} else if ($operation == "prepend") {
				$nv = $newvalue."".$leads_raw_row[$applytovalue];
				mysql_query("UPDATE leads_raw SET ".$applytovalue." = '".$nv."' WHERE leadid = '".$leadid."'");	
			} else if ($operation == "selectivereplace") {
				$nv = str_replace($replacevalue, $newvalue, $leads_raw_row[$applytovalue]);
				mysql_query("UPDATE leads_raw SET ".$applytovalue." = '".$nv."' WHERE leadid = '".$leadid."'");	
			} else if ($operation == "replaceall") {
				$nv = $newvalue;
				mysql_query("UPDATE leads_raw SET ".$applytovalue." = '".$nv."' WHERE leadid = '".$leadid."'");	
			}
			mysql_query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
			leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$applytovalue]."', newvalue = '".$nv."', field = '".$applytovalue."', epoch = '".$unixtimestamp."'");
		}	
	}
	echo "Changes were committed successfully.";
}
if ($_REQUEST['act'] == 'columnsubmitteditcampaignlists') {
	$multilistids = $_POST['lid'];
	$projid = $_POST['projid'];
	$operation = $_POST['operation'];
	$from = $_POST['from']; 
	$to = $_POST['to'];
	// if (strpos($multilistids, ",")) {
		// $multilist = explode(",", $multilistids);
	// } else {
		// $multilist = $multilistids;
	// }
	$multilist = $multilistids;
	$getleadidres = mysql_query("SELECT leads_raw.leadid FROM leads_raw JOIN lists ON lists.listid = leads_raw.listid WHERE lists.bcid = '".$bcid."' AND lists.lid IN (".$multilist.")");
	while ($getleadidrow = mysql_fetch_assoc($getleadidres)) {
		$leadid = $getleadidrow["leadid"];
		if ($operation == "swap") {
			$leads_raw_res = mysql_query("SELECT ".$from.", ".$to.", listid from leads_raw where leadid = '".$leadid."'");
		    $leads_raw_row = mysql_fetch_assoc($leads_raw_res);
			mysql_query("UPDATE leads_raw SET ".$from."=@tmp:=".$from.", ".$from."=".$to.", ".$to."=@tmp WHERE leadid = '".$leadid."'");    
	    	mysql_query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
			leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$from]."', newvalue = '".$leads_raw_row[$to]."', field = '".$from.",".$to."', epoch = '".$unixtimestamp."'");
		} else if ($operation == "move") {
			$leads_raw_res = mysql_query("SELECT ".$from.", ".$to.", listid from leads_raw where leadid = '".$leadid."'");
		    $leads_raw_row = mysql_fetch_assoc($leads_raw_res);
			$nv = $leads_raw_row[$from];
			mysql_query("UPDATE leads_raw SET ".$to." = '".$nv."', ".$from." = '' WHERE leadid = '".$leadid."'");
	    	mysql_query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
			leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$to]."', newvalue = '".$leads_raw_row[$from]."', field = '".$from.",".$to."', epoch = '".$unixtimestamp."'");
		} else if ($operation == "copy") {
			$leads_raw_res = mysql_query("SELECT ".$from.", ".$to.", listid from leads_raw where leadid = '".$leadid."'");
		    $leads_raw_row = mysql_fetch_assoc($leads_raw_res);
			$nv = $leads_raw_row[$from];
			mysql_query("UPDATE leads_raw SET ".$to." = '".$nv."' WHERE leadid = '".$leadid."'");
			mysql_query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
			leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$to]."', newvalue = '".$leads_raw_row[$from]."', field = '".$from.",".$to."', epoch = '".$unixtimestamp."'");
		} else {
	    	$replacevalue = $_POST['replacevalue'];
			$newvalue = $_POST['newvalue'];
			$_applytovalue = $_POST['applytovalue'];
			$pos2 = strpos($_applytovalue, "cf|");
			if ($pos2 === false) {
					$leads_raw_res = mysql_query("SELECT ".$applytovalue.", listid from leads_raw where leadid = '".$leadid."'");
					$leads_raw_row = mysql_fetch_assoc($leads_raw_res);
					if ($operation == "append") {
						$nv = $leads_raw_row[$applytovalue]."".$newvalue;
						mysql_query("UPDATE leads_raw SET ".$applytovalue." = '".$nv."' WHERE leadid = '".$leadid."'");	
					} else if ($operation == "prepend") {
						$nv = $newvalue."".$leads_raw_row[$applytovalue];
						mysql_query("UPDATE leads_raw SET ".$applytovalue." = '".$nv."' WHERE leadid = '".$leadid."'");	
					} else if ($operation == "selectivereplace") {
						$nv = str_replace($replacevalue, $newvalue, $leads_raw_row[$applytovalue]);
						mysql_query("UPDATE leads_raw SET ".$applytovalue." = '".$nv."' WHERE leadid = '".$leadid."'");	
					} else if ($operation == "replaceall") {
						$nv = $newvalue;
						mysql_query("UPDATE leads_raw SET ".$applytovalue." = '".$nv."' WHERE leadid = '".$leadid."'");	
					}
					mysql_query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
					leadid = ".$leadid.", oldvalue = '".$leads_raw_row[$applytovalue]."', newvalue = '".$nv."', field = '".$applytovalue."', epoch = '".$unixtimestamp."'");
			} else {
				$applytovalue_rep = str_replace("cf|", "", $_applytovalue);
				$customfieldres = mysql_query("SELECT * from leads_custom_fields where leadid = ".$leadid."");
				$customfieldrow = mysql_fetch_assoc($customfieldres);
				$cfields  = json_decode($customfieldrow['customfields'], true);
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
					mysql_query("UPDATE leads_custom_fields SET customfields = '".json_encode($newcf)."' WHERE leadid = '".$leadid."'");	
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
					mysql_query("UPDATE leads_custom_fields SET customfields = '".json_encode($newcf)."' WHERE leadid = '".$leadid."'");	
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
					mysql_query("UPDATE leads_custom_fields SET customfields = '".json_encode($newcf)."' WHERE leadid = '".$leadid."'");	
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
					mysql_query("UPDATE leads_custom_fields SET customfields = '".json_encode($newcf)."' WHERE leadid = '".$leadid."'");	
				}
				mysql_query("INSERT INTO weblog SET user = '".$_SESSION['auth']."', listid = '".$leads_raw_row["listid"]."', 
				leadid = ".$leadid.", oldvalue = '".$customfieldres['customfields']."', newvalue = '".json_encode($newcf)."', field = 'customfields', epoch = '".$unixtimestamp."'");
			}
		}	
	}
	echo "Changes were committed successfully. You can view the results by exporting the list or by clicking the Row Editing.";
}
?>
