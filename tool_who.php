<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$mycall="IK4LZH";

$qq=0;
$query2=mysqli_query($con,"select distinct callsign from log where mycall='$mycall' and callsign like '2%' order by callsign");
for(;;){
  $row2=mysqli_fetch_row($query2);
  if($row2==null)break;
  $Icallsign=$row2[0];
  $query=mysqli_query($con,"select count(callsign) from who where callsign='$Icallsign'");
  $row=mysqli_fetch_row($query);
  $cc=(int)$row[0];
  mysqli_free_result($query);
  if($cc>0)continue;
  echo "$Icallsign $cc\n";
  $aux=qrz($con,$Icallsign);
  if($aux==0)$aux=ru($con,$Icallsign);
}
mysqli_free_result($query2);
mysqli_close($con);

?>
