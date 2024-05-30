<?php

$mystart="2024-05-01 00:00:00";
echo "<pre>$mystart\n";

$query=mysqli_query($con,"select serial from log where mycall='$mycall' and start>='$mystart' order by start limit 1");
$row=mysqli_fetch_assoc($query);
$serial=$row["serial"];
if(!isset($serial))$serial=1;
mysqli_free_result($query);

$query=mysqli_query($con,"select callsign,start from log where mycall='$mycall' and start>='$mystart' order by start");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  $start=$row["start"];
  mysqli_query($con,"update log set serial=$serial where mycall='$mycall' and callsign='$callsign' and start='$start'");
  $serial++;
  if($serial%1000==0)echo "$serial\n";
}
mysqli_free_result($query);
echo "</pre>";
?>
