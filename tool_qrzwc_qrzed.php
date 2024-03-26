<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
include "def_qrzwc.php";
$mycall="IK4LZH";
$myshow=0;
$process=100;

$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

for($sss=0;;$sss++){
  $query1=mysqli_query($con,"select min(qrzed) from qrzwebcontact where mycall='$mycall'");
  $row1=mysqli_fetch_row($query1);
  $minqrzed=(int)$row1[0];
  mysqli_free_result($query1);
  
  $query=mysqli_query($con,"select callsign from qrzwebcontact where mycall='$mycall' and qrzed=$minqrzed order by rand()");
  $myprocess=0;
  for(;;){
    $myprocess++;
    if($myprocess>$process)break;
    $row=mysqli_fetch_assoc($query);
    if($row==null)break;
    $callsign=$row["callsign"];
    echo "$myprocess:$sss $callsign\n";
    qrz($con,$callsign);
    $tt=(int)(time()/86400);
    mysqli_query($con,"update qrzwebcontact set qrzed=$tt where mycall='$mycall' and callsign='$callsign'");
    sleep(2);
  }
  mysqli_free_result($query);
}
mysqli_close($con);

?>
