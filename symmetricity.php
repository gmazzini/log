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
  @$cqdata[substr($row["start"],0,4)*100+(int)((substr($row["start"],5,2)-1)*100/12)][$mycq[$row["dxcc"]]]++;
}
mysqli_free_result($query);
ksort($cqdata);

foreach($myband as $ff => $ll)if($ll>=10 && $ll<=160)@$bb[$ll]++;
$bb["all"]=1;
?>
<html>
<head>
<style>
  body {
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    background: #f5f6fa;
    margin: 0;
  }
  .dashboard {
    max-width: 1400px;
    margin: 20px auto 40px;
    padding: 0 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(480px, 1fr));
    gap: 24px;
  }
  .panel {
    background: #ffffff;
    border-radius: 14px;
    padding: 16px 20px 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  }
  .panel h2 {
    margin: 0 0 12px;
    font-size: 1.1rem;
    font-weight: 600;
  }
  .chart {
    width: 100%;
    height: 420px;
  }
  .panel-table {
    grid-column: 1 / -1;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
  }
  thead {
    background: #f0f2f7;
  }
  th, td {
    padding: 6px 10px;
    text-align: right;
    border-bottom: 1px solid #e0e3ea;
    white-space: nowrap;
  }
  th:first-child, td:first-child {
    text-align: left;
  }
  tbody tr:hover {
    background: #f9fafc;
  }
</style>    
</head>
<h2>Real time channel symmetricity data analisys on IK4LZH QSOs collection</h2>
<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
<script type='text/javascript'>
  google.charts.load('current',{'packages':['corechart']});
  google.charts.setOnLoadCallback(draw1);
  google.charts.setOnLoadCallback(draw2);
  function draw1(){
    var data=google.visualization.arrayToDataTable([ 
    ['Delta' <?php foreach($bb as $ll => $vv)echo ",'$ll'"; ?> ],
    <?php for($i=$lowrep;$i<=$highrep;$i++){ echo "[$i"; foreach($bb as $ll => $vv){echo ","; echo $acc[$ll][$i]/$tot[$ll];} echo "]"; if($i<$highrep)echo ","; echo "\n"; } ?>
  ]);
  var options={title:'TX-RX(dB) pdfs',curveType:'function',vAxis:{viewWindowMode:'explicit',viewWindow:{min:0.0}},legend:{position:'bottom'}};
  var chart=new google.visualization.LineChart(document.getElementById('curve1'));
  chart.draw(data,options);
  }
  function draw2(){
    var data=google.visualization.arrayToDataTable([
      ['ID','X','Y','tot','vv'],
      <?php foreach($cqdata as $ll => $vv){if($ll<201901)continue; foreach($vv as $lll => $vvv){if($lll>0)echo "['',$ll,$lll,$vvv,1],\n"; }} ?>
      ['',202601,1,1,4]
    ]);
    var options={colorAxis:{colors:['yellow','red']},bubble:{textStyle:{fontSize:6}}};
    var chart=new google.visualization.BubbleChart(document.getElementById('curve2'));
    chart.draw(data,options);
  }
</script>

<div class="dashboard">
    <div class="panel">
      <h2>Grafico 1</h2>
      <div id="curve1" class="chart"></div>
    </div>
    <div class="panel">
      <h2>Grafico 2</h2>
      <div id="curve2" class="chart"></div>
    </div>
    <div class="panel panel-table">
      <h2>Characteristic parameter analysis</h2>
      <table>
        <thead>
          <tr>
            <th>Band</th>
            <th>QSOs</th>
            <th>Average</th>
            <th>Stdev</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach ($bb as $ll => $vv) {
              $med = 0;
              $sqr = 0;
              for ($i = $lowrep; $i <= $highrep; $i++) {
                  $med += $i * $acc[$ll][$i];
                  $sqr += $i * $i * $acc[$ll][$i];
              }
              $med = $med / $tot[$ll];
              $sqr = sqrt($sqr / $tot[$ll] - $med * $med);
              $med_fmt = sprintf("%+7.5f", $med);
              $sqr_fmt = sprintf("%7.4f", $sqr);
          ?>
            <tr>
              <td><?= htmlspecialchars($ll) ?></td>
              <td><?= number_format($tot[$ll], 0, ',', '.') ?></td>
              <td><?= $med_fmt ?></td>
              <td><?= $sqr_fmt ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  
<div id='curve1' style='width: 1400px; height: 800px'></div>
<pre><b>Characteristic parameter analysis</b>
  
<?php 
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
mysqli_close($con);
?>

</pre>
<div id='curve2' style='width: 1400px; height: 800px'></div>
</html>

