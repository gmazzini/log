<?php
include "local.php";
include "utility.php";
$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$mycall="IK4LZH";

$query=mysqli_query($con,"select callsign,signalrx,signaltx from log where mycall='$mycall' and mode='FT8'");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $call=$row["callsign"];
  $signaltx=$row["signaltx"];
  $signalrx=$row["signalrx"];
  $oo=searchcty($con,$call);
  $diff=((int)$signaltx)-((int)$signalrx);
  @$cc[$diff]++;
  @$cv[$oo["cont"]][$diff]++;
}
mysqli_free_result($query);
ksort($cc);
for($i=-40;$i<=40;$i++)printf("%d,%7.4f\n",$i,$cc[$i]);
print_r($cv);

mysqli_close($con);
?>
