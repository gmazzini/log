<?php
include "local.php";
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$mycall="IK4LZH";
$query=mysqli_query($con,"select distinct callsign from log where mycall='$mycall' and callsign not in (select callsign from qrzwebcontact where mycall='$mycall' and sent=1) order by callsign");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  echo "$callsign\n";
  mysqli_query($con,"insert into qrzwebcontact (mycall,callsign,sent) value ('$mycall','$callsign',0)");
}
mysqli_free_result($query);
mysqli_close($con);

?>
