<?php
include "local.php";

$mycall=$_GET["mycall"];
$act=$_GET["act"];
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
$mynow=gmdate('Y-m-d H:i:s');
mysqli_query($con,"insert into booking (mycall,act,myupdate) value ('$mycall','$act','$mynow')");
mysqli_close($con);

?>
