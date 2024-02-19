<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
include "def_qrzwc.php";
$mycall="IK4LZH";
$myshow=0;

$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query=mysqli_query($con,"select distinct callsign from log where mycall='$mycall' and callsign not in (select callsign from qrzwebcontact where mycall='$mycall') order by callsign");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  mysqli_query($con,"insert into qrzwebcontact (mycall,callsign,sent,source,looked) value ('$mycall','$callsign',0,'me',0)");
}
mysqli_free_result($query);

$out=myqrzwebcontact($mycall);
foreach($out as $v){
  $query1=mysqli_query($con,"select count(*) from qrzwebcontact where mycall='$mycall' and callsign='$v'");
  $row1=mysqli_fetch_row($query1);
  $aux=(int)$row1[0];
  mysqli_free_result($query1);
  if($aux==0)mysqli_query($con,"insert into qrzwebcontact (mycall,callsign,sent,source,looked) value ('$mycall','$v',1,'web',0)");
  else mysqli_query($con,"update qrzwebcontact set sent=1,source='web' where mycall='$mycall' and callsign='$v'");
}

mysqli_close($con);
?>