<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
$mycall="IK4LZH";
$process=100;

$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query=mysqli_query($con,"select distinct callsign from log where mycall='$mycall' and callsign not in (select callsign from qrzwebcontact where mycall='$mycall') order by callsign");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  mysqli_query($con,"insert into qrzwebcontact (mycall,callsign,sent) value ('$mycall','$callsign',0)");
}
mysqli_free_result($query);

$query=mysqli_query($con,"select callsign from qrzwebcontact where mycall='$mycall' and sent=0 order by rand() limit $process");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  $aux=qrz($con,$callsign);
  echo "$callsign $aux\n";
  
}
mysqli_free_result($query);

mysqli_close($con);

?>
