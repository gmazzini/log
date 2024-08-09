<?php
include "local.php";
include "utility.php";

$mycall="IK4LZH";
// all here is to think

$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
$lowrep=-35;
$highrep=35;

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
  @$acc[$myband[$freqMHZ]][$signaltx-$signalrx]++;
  @$tot[$myband[$freqMHZ]]++;
}
mysqli_free_result($query);

foreach($myband as $ff => $ll)if($ll>=10 && $ll<=160)@$bb[$ll]++;
echo "<html>\n";
echo "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>\n";
echo "<script type='text/javascript'>\n";
echo "google.charts.load('current',{'packages':['corechart']});\n";
echo "google.charts.setOnLoadCallback(drawChart);\n";
echo "function drawChart(){\n";
echo "var data=google.visualization.arrayToDataTable([\n";
echo "['Delta'"; foreach($bb as $ll => $vv)echo ",'$ll'"; echo "],\n";
for($i=$lowrep;$i<=$highrep;$i++){
  echo "[$i"; foreach($bb as $ll => $vv){echo ","; echo $acc[$ll][$i]/$tot[$ll];} echo "]";
  if($i<$highrep)echo ",";
  echo "\n";
}
echo "]);\n";
echo "var options={title:'Channel Symmetricity by IK4LZH',curveType:'function',vAxis:{viewWindowMode:'explicit',viewWindow:{min:0.0}},legend:{position:'bottom'}};\n";
echo "var chart=new google.visualization.LineChart(document.getElementById('curve_chart'));\n";
echo "chart.draw(data,options);\n";
echo "}\n";
echo "</script>\n";
echo "<div id='curve_chart' style='width: 1400px; height: 800px'></div>\n";

echo "</html>\n"; exit(0);
echo "<pre>";
foreach (array(3,7,10,14,18,21,24,28) as $f){
  $med=0;
  $sqr=0;
  for($i=$lowrep;$i<=$highrep;$i++){
    $med+=$i*$acc[$f][$i];
    $sqr+=$i*$i*$acc[$f][$i];
  }
  $med=$med/$tot[$f];
  $sqr=sqrt($sqr/$tot[$f]-$med*$med);
  printf("%2d %9d %+7.5f %7.4f\n",$f,$tot[$f],$med,$sqr);
}

echo "</html>\n";

mysqli_close($con);
?>
