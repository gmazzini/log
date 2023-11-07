<?php
include "local.php";
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$mycall="IK4LZH";
$serial=1;
$query=mysqli_query($con,"select callsign,start from log where mycall='$mycall' order by start");
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
mysqli_close($con);

?>
