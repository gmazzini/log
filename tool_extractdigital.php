<?php
include "local.php";

$mycall="IK4LZH";
// all here is to think

$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
$lowrep=-26;
$highrep=26;

$query=mysqli_query($con,"select freqtx,signaltx,signalrx from log where mode='FT8'");
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
  $freqMHZ=(int)($row["freqtx"]/1000000);
  if($freqMHZ==0 || $freqMHZ>29)continue;
  $signaltx=(int)$row["signaltx"];
  if($signaltx<$lowrep || $signaltx>$highrep)continue;
  $signalrx=(int)$row["signalrx"];
  if($signalrx<$lowrep || $signalrx>$highrep)continue;
  @$acc[$freqMHZ][$signaltx-$signalrx]++;
}
mysqli_free_result($query);

// print_r($acc);

echo "<html>\n";
echo "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>\n";
echo "<script type='text/javascript'>\n";
echo "google.charts.load('current',{'packages':['corechart']});\n";
echo "google.charts.setOnLoadCallback(drawChart);\n";
echo "function drawChart(){\n";
echo "var data=google.visualization.arrayToDataTable([\n";
echo "['Delta','80m','40m','30m','20m','17m','15m','12m','10m'],\n";
for($i=$lowrep;$i<$highrep;$i++)printf("[%d,%d,%d,%d,%d,%d,%d,%d,%d],\n",$i,$acc[3][$i],$acc[7][$i],$acc[10][$i],$acc[14][$i],$acc[18][$i],$acc[21][$i],$acc[24][$i],$acc[28][$i]);
printf("[%d,%d,%d,%d,%d,%d,%d,%d,%d]\n",$i,$acc[3][$i],$acc[7][$i],$acc[10][$i],$acc[14][$i],$acc[18][$i],$acc[21][$i],$acc[24][$i],$acc[28][$i]);
echo "]);\n";
echo "var options={title:'Channel Symmetricity by IK4LZH',curveType:'function',legend:{position:'bottom'}};\n";
echo "var chart=new google.visualization.LineChart(document.getElementById('curve_chart'));\n";
echo "chart.draw(data,options);\n";
echo "}\n";
echo "</script>\n";
echo "<div id='curve_chart' style='width: 900px; height: 500px'></div>\n";
echo "</html>\n";

mysqli_close($con);
?>
