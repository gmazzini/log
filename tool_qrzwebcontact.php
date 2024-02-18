<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
$mycall="IK4LZH";
$myshow=0;
$process=10;

$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query=mysqli_query($con,"select distinct callsign from log where mycall='$mycall' and callsign not in (select callsign from qrzwebcontact where mycall='$mycall') order by callsign");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  mysqli_query($con,"insert into qrzwebcontact (mycall,callsign,sent,source) value ('$mycall','$callsign',0,'me')");
}
mysqli_free_result($query);

$query=mysqli_query($con,"select callsign from qrzwebcontact where mycall='$mycall' and sent=0 and source='me' order by rand() limit $process");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  $aux=qrz($con,$callsign);
  if($aux==1){
    $query1=mysqli_query($con,"select email from who where callsign='$callsign'");
    $row1=mysqli_fetch_assoc($query1);
    $email=$row1["email"];
    mysqli_free_result($query1);
    if(strlen($email)>5){
      echo "$callsign $email \n";
    }
  }  
}
mysqli_free_result($query);

mysqli_close($con);

?>
