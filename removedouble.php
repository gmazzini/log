<?php
include "local.php";

$mycall="IK4LZH";

$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query=mysqli_query($con,"select call,start from log where mycall='$mycall'");
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
}




?>
