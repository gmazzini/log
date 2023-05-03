<?php
include "local.php";

$mycall="IK4LZH";

$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query=mysqli_query($con,"select callsign,start,end from log where mycall='$mycall' order by callsign");
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
  $diff=strtotime($row[2])-strtotime($row[1]);
  if($diff>3600||$diff<0)echo "... $diff $row[0]  $row[1]  $row[2]\n";
  
  
  $query2=mysqli_query($con,"select start,end from log where mycall='$mycall' and callsign='$row[0]' and start>'$row[1]' and start<'$row[2]'");
  for(;;){
    $row2=mysqli_fetch_array($query2);
    if($row2==null)break;
    echo "$row[0]  $row[1]  $row[2]    $row2[0]  $row2[1]\n";
  }
  mysqli_free_result($query2);
}
mysqli_free_result($query);
mysqli_close($con);
?>
