<?php

echo "<pre>";

query=mysqli_query($con,"select count(lotw) from log where mycall='$mycall' and lotw=1");
$row=mysqli_fetch_array($query);
printf("# lotw: %6d\n",$row[0]);
printf("# eqsl: %6d\n",$row[1]);
printf("# qrz: %6d\n",$row[2]);
mysqli_free_result($query);

echo "</pre>";

?>
