<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
include "def_qrzwc.php";
$mycall="IK4LZH";
$myshow=0;

$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query=mysqli_query($con,"select distinct callsign from log where mycall='$mycall' and callsign not in (select callsign from qrzwebcontact where mycall='$mycall')");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  mysqli_query($con,"insert into qrzwebcontact (mycall,callsign,source) value ('$mycall','$callsign','me')");
}
mysqli_free_result($query);

$query=mysqli_query($con,"select distinct callsign from log where mycall='$mycall' and callsign in (select callsign from qrzwebcontact where mycall='$mycall' and source!='me')");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  mysqli_query($con,"update qrzwebcontact set source='me' where mycall='$mycall' and callsign='$callsign'");
}
mysqli_free_result($query);

$out=myqrzwebcontact($mycall,$Ewc);
foreach($out as $v){
  $query1=mysqli_query($con,"select count(*) from qrzwebcontact where mycall='$mycall' and callsign='$v'");
  $row1=mysqli_fetch_row($query1);
  $aux=(int)$row1[0];
  mysqli_free_result($query1);
  if($aux==0)mysqli_query($con,"insert into qrzwebcontact (mycall,callsign,source,sent,you) value ('$mycall','$v','oth',1,1)");
  else mysqli_query($con,"update qrzwebcontact set sent=1,you=1 where mycall='$mycall' and callsign='$v'");
}

mysqli_close($con);
?>
