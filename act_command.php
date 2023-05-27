<?php
include "local.php";

$mycall=$_GET["mycall"];
$md5passwd=$_GET["md5passwd"];
$start=$_GET["start"];
$callsign=$_GET["callsign"];
$command=strtoupper($_GET["command"]);

$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query=mysqli_query($con,"select mycall from user where mycall='$mycall' and md5passwd='$md5passwd'");
$row=mysqli_fetch_assoc($query);
if($row!=null){
  $mynow=gmdate('Y-m-d H:i:s');
  mysqli_query($con,"insert into booking (mycall,command,myupdate) value ('$mycall','$command','$mynow')");
}
mysqli_free_result($query);
mysqli_close($con);

?>
