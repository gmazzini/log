<?php
include "local.php";
$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$mycall="IK4LZH";

$query=mysqli_query($con,"select signalrx,signaltx from log where mycall='$mycall' and mode='FT8'");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $signaltx=$row["signaltx"];
  $signalrx=$row["signalrx"];
  $diff=((int)$signaltx)-((int)$signalrx);
  @$cc[$diff]++;
}
mysqli_free_result($query);
ksort($cc);
$t=0; for($i=-40;$i<=40;$i++)$t+=$cc[$i];
for($i=-40;$i<=40;$i++)printf("%d,%7.4f\n",$i,cc$[$i]/$t);

mysqli_close($con);
?>
