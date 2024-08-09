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
echo "['Year', 'Sales', 'Expenses', 'terzo'],\n";
echo "['2004',  1000,      400, 1],\n";
echo "['2005',  1170,      460,2],\n";
echo "['2006',  660,       1120,3],\n";
echo "['2007',  1030,      540,4]\n";
echo "]);\n";
echo "var options={title:'Company Performance',curveType:'function',legend:{position:'bottom'}};\n";
echo "var chart=new google.visualization.LineChart(document.getElementById('curve_chart'));\n";
echo "chart.draw(data,options);\n";
echo "}\n";
echo "</script>\n";
echo "<div id='curve_chart' style='width: 900px; height: 500px'></div>\n";
echo "</html>\n";

mysqli_close($con);
?>
