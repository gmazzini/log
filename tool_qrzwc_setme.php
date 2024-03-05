<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
include "def_qrzwc.php";
$mycall="IK4LZH";
$myshow=0;
$process=1;

$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query=mysqli_query($con,"select callsign from qrzwebcontact where mycall='$mycall' and looked>0 and me=0 and you=1 and Ewc=1 order by Nwc desc");
$myprocess=0;
for(;;){
  sleep(rand(10,20));
  $myprocess++;
  if($myprocess>$process)break;
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  echo "$myprocess Setup $callsign\n";

  if(!myqrzsetwebcontact($callsign))continue;
  mysqli_query($con,"update qrzwebcontact set me=1 where mycall='$mycall' and callsign='$callsign'");
  
}
mysqli_free_result($query);
mysqli_close($con);

?>
