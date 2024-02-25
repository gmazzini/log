<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
include "def_qrzwc.php";
$mycall="IK4LZH";
$myshow=0;

$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query=mysqli_query($con,"select distinct callsign from log where mycall='$mycall' and callsign not in (select callsign from qrzwebcontact where mycall='$mycall') order by callsign");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  mysqli_query($con,"insert into qrzwebcontact (mycall,callsign,source) value ('$mycall','$callsign','me')");
}
mysqli_free_result($query);

$query=mysqli_query($con,"select distinct callsign from log where mycall='$mycall' and callsign in (select callsign from qrzwebcontact where mycall='$mycall' and source!='me') order by callsign");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  mysqli_query($con,"update qrzwebcontact set source='me' where mycall='$mycall' and callsign='$callsign'");
}
mysqli_free_result($query);

$out=myqrzwebcontact($mycall);
foreach($out as $v){
  mysqli_query($con,"update qrzwebcontact set sent=1,you=1 where mycall='$mycall' and callsign='$v'");
}

mysqli_close($con);
?>
