<?php
include "local.php";
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$mycall="IK4LZH";
$mystart="2024-01-01 00:00:00";
$query=mysqli_query($con,"select callsign,start,flag from log where mycall='$mycall' and start>'$mystart' order by start");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  $start=$row["start"];
  $flag=$row["flag"];
  $query1=mysqli_query($con,"select email from who where callsign='$callsign'");
  $row1=mysqli_fetch_row($query1);
  $email=$row1[0];
  mysqli_free_result($query1);
  
  echo "$callsign $start $flag $email\n";
//  mysqli_query($con,"update log set serial=$serial where mycall='$mycall' and callsign='$callsign' and start='$start'");
}
mysqli_free_result($query);
mysqli_close($con);

?>
