<?
$time_zones = $timezone_identifiers = \DateTimeZone::listIdentifiers();
foreach ($time_zones as $time_zone) {
    $date = new \DateTime('now', new \DateTimeZone($time_zone));
    $offset_in_hours = $date->getOffset() / 60 / 60;
    if (!is_null($offset) && $offset == $offset_in_hours) {
        echo "{$time_zone}: {$date->getOffset()} ($offset_in_hours)<br>";
    }
}

function tz_list() {
  $zones_array = array();
  $timestamp = time();
  foreach(timezone_identifiers_list() as $key => $zone) {
    date_default_timezone_set($zone);
    $zones_array[$key]['zone'] = $zone;
    $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
  }
  return $zones_array;
}
?>
<tr>
    <td width="80">Time zone</td>
    <td width="345">
    	<select id="timezone" style="width: 275px">
    		<?
    			foreach( tz_list() as $t ) {
    				$tz_list = "<option value='".$t['zone']."'>".$t['diff_from_GMT']." - ".$t['zone']."</option>";
					echo $tz_list;
				} 
			?>
    		<option value="<?$tz_list?>"><?$tz_list?></option>
    	</select>
    </td>
    <td width="80">&nbsp;</td>
    <td width="344">&nbsp;</td>
</tr>