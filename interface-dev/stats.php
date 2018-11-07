<?php
$uid = $_GET['uid'];
include "../dbconnect.php";
$dres = mysql_query("SELECT substr(start,1,10) as tdate, if(t.ccount is null,0,t.ccount) as ccount from finalhistory left join (SELECT substr(start,1,10) as ddate,count(callid) as ccount from finalhistory where assigned = '$uid' and `start` >= DATE_SUB(NOW(),INTERVAL 7 DAY) group by ddate order by ddate DESC limit 7) as t on substr(finalhistory.`start`,1,10) = t.ddate where `start` >= DATE_SUB(NOW(),INTERVAL 7 DAY) group by tdate; ");
$dct = 0;
while ($drow = mysql_fetch_array($dres))
	{
		if ($dct == 0)
			{
				$min = $drow['tdate'];
			}
		if ($dct > 0) 
			{
			$data .= ",";
;
			}
		$dct++;
		$data .= '[\''.$drow['tdate'].'\','.$drow['ccount'].']';

		
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<link rel="stylesheet" type="text/css" href="../jquery/dist/jquery.jqplot.css" />
 <link rel="stylesheet" type="text/css" href="../jquery/dist/examples.css" />
<script type="text/javascript" src="../jquery/dist/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../jquery/dist/jquery.jqplot.js"></script>
<script language="javascript" type="text/javascript" src="../jquery/dist/plugins/jqplot.canvasTextRenderer.js"></script>
  <script language="javascript" type="text/javascript" src="../jquery/dist/plugins/jqplot.categoryAxisRenderer.js"></script>
  <script language="javascript" type="text/javascript" src="../jquery/dist/plugins/jqplot.barRenderer.js"></script>
<script language="javascript" type="text/javascript" src="../jquery/dist/plugins/jqplot.canvasAxisTickRenderer.js"></script>
<script language="javascript" type="text/javascript" src="../jquery/dist/plugins/jqplot.dateAxisRenderer.js"></script>
<style type="text/css" media="screen">
    .jqplot-axis {
      font-size: 0.85em;
    }
    div.plot {
        margin-bottom: 70px;
    }
    
    p {
        margin: 2em 0;
    }
</style>





 <script type="text/javascript" language="javascript">
  
  $(document).ready(function(){
      $.jqplot.config.enablePlugins = true;

    var line1 = [<?=$data?>];
    plot1 = $.jqplot('chart2', [line1], {
        title:'Call Count Summary',
        axes:{
            xaxis:{
                renderer:$.jqplot.DateAxisRenderer, 
                tickInterval:'1 day',
				min: '<?=$min?>',
                rendererOptions:{
                    tickRenderer:$.jqplot.CanvasAxisTickRenderer},
                    tickOptions:{formatString:'%b %#d, %Y', fontSize:'8pt', fontFamily:'Tahoma', angle:-40, fontWeight:'normal', fontStretch:1}
            },
			yaxis:{
				min:0
			}
        },
        
		seriesDefaults: {
    		color: '#F90',
    		renderer: $.jqplot.BarRenderer,
    		shadow: false,
			barWidth:10
    	}

    });


  });

</script>
<div id="cnt" style="width:1024px; margin:0 auto; position:relative">
<div id="chart2" class="chartdiv" style="height:200px; width:200px; float:left; display:block; position:relative"> </div>
</div>
