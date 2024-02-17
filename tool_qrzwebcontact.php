<?php
include "local.php";
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$mycall="IK4LZH";
$mystart="2024-01-01 00:00:00";
$query=mysqli_query($con,"select distinct callsign from log where mycall='$mycall' and callsign not in (select callsign from qrzwebcontact where sent=1) order by callsign");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  echo "$callsign\n";
//  mysqli_query($con,"update log set serial=$serial where mycall='$mycall' and callsign='$callsign' and start='$start'");
}
mysqli_free_result($query);
mysqli_close($con);

?>
