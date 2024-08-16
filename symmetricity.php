<?php
include "local.php";
include "utility.php";

$mycall="IK4LZH";

$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
$lowrep=-35;
$highrep=35;

$query=mysqli_query($con,"select cqzone,dxcc from cty");
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
  $mycq[$row["dxcc"]]=$row["cqzone"];
}
mysqli_free_result($query);

$query=mysqli_query($con,"select freqtx,signaltx,signalrx,dxcc,start from log where mode='FT8' or mode='MFSK'");
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
  $freqMHZ=(int)($row["freqtx"]/1000000);
  if($freqMHZ==0 || $freqMHZ>29)continue;
  $signaltx=(int)$row["signaltx"];
  if(!is_numeric($row["signaltx"]) || $signaltx<$lowrep || $signaltx>$highrep)continue;
  $signalrx=(int)$row["signalrx"];
  if(!is_numeric($row["signalrx"]) || $signalrx<$lowrep || $signalrx>$highrep)continue;
  @$acc[$myband[$freqMHZ]][$signaltx-$signalrx]++;
  @$acc["all"][$signaltx-$signalrx]++;
  @$tot[$myband[$freqMHZ]]++;
  @$tot["all"]++;
  @$cqdata[substr($row["start"],0,4).substr($row["start"],5,2)][$mycq[$row["dxcc"]]]++;
}
mysqli_free_result($query);
ksort($cqdata);

foreach($myband as $ff => $ll)if($ll>=10 && $ll<=160)@$bb[$ll]++;
$bb["all"]=1;
echo "<html>\n";
echo "<h2>Real time channel symmetricity data analisys on IK4LZH QSOs collection</h2>";
echo "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>\n";
echo "<script type='text/javascript'>\n";
echo "google.charts.load('current',{'packages':['corechart']});\n";
echo "google.charts.setOnLoadCallback(draw1);\n";
echo "google.charts.setOnLoadCallback(draw2);\n";

echo "function draw1(){\n";
echo "var data=google.visualization.arrayToDataTable([\n";
echo "['Delta'"; foreach($bb as $ll => $vv)echo ",'$ll'"; echo "],\n";
for($i=$lowrep;$i<=$highrep;$i++){
  echo "[$i"; foreach($bb as $ll => $vv){echo ","; echo $acc[$ll][$i]/$tot[$ll];} echo "]";
  if($i<$highrep)echo ",";
  echo "\n";
}
echo "]);\n";
echo "var options={title:'TX-RX(dB) pdfs',curveType:'function',vAxis:{viewWindowMode:'explicit',viewWindow:{min:0.0}},legend:{position:'bottom'}};\n";
echo "var chart=new google.visualization.LineChart(document.getElementById('curve1'));\n";
echo "chart.draw(data,options);\n";
echo "}\n";

echo "function draw2(){\n";
echo "var data=google.visualization.arrayToDataTable([\n";
echo "['ID','X','Y','tot'],\n";
$i=0;
foreach($cqdata as $ll => $vv){
  if(++$i>100)break;
  foreach($vv as $lll => $vvv){
    if($lll>0)echo "['',$ll,$lll,$vvv],\n";
  }
}
echo "['',202001,1,1]\n";
echo "]);\n";
echo "var options={colorAxis:{colors:['yellow','red']}};\n";
echo "var chart=new google.visualization.BubbleChart(document.getElementById('curve2'));\n";
echo "chart.draw(data,options);\n";
echo "}\n";

echo "</script>\n";
echo "<div id='curve1' style='width: 1400px; height: 800px'></div>\n";

echo "<pre><b>Characteristic parameter analysis</b>\n";
printf("%4s %9s %7s %7s\n","band","QSOs","average","stdev");
foreach($bb as $ll => $vv){
  $med=0;
  $sqr=0;
  for($i=$lowrep;$i<=$highrep;$i++){
    $med+=$i*$acc[$ll][$i];
    $sqr+=$i*$i*$acc[$ll][$i];
  }
  $med=$med/$tot[$ll];
  $sqr=sqrt($sqr/$tot[$ll]-$med*$med);
  printf("%4s %9d %+7.5f %7.4f\n",$ll,$tot[$ll],$med,$sqr);
}
echo "</pre>";

echo "<div id='curve2' style='width: 1400px; height: 800px'></div>\n";

echo "</html>\n";

mysqli_close($con);
?>
