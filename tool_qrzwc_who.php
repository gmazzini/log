<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
include "def_qrzwc.php";
$mycall="IK4LZH";
$myshow=0;
$process=10000;

$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$i=0;
$query=mysqli_query($con,"select callsign from qrzwebcontact where mycall='$mycall' and sent=1 and qrzed=0 order by rand()");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];

  echo "$i $callsign\n";
  qrz($con,$callsign);
  $tt=(int)(time()/86400);
  mysqli_query($con,"update qrzwebcontact set qrzed=$tt where mycall='$mycall' and callsign='$callsign'");
  sleep(1);
  $i++;
  if($i==$process)break;
}
mysqli_free_result($query);
mysqli_close($con);

?>
