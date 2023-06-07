<?php
include "local.php";
include "utility.php";

$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
mysto($con,"rulock","0\n");
echo "<pre>qrz.ru de lock\n</pre>";
mysqli_close($con);

?>
