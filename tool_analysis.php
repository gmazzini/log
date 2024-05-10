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
  @ $cc[$diff]++;
}
mysqli_free_result($query);

print_r($diff);

mysqli_close($con);
?>
