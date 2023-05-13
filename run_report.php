<?php

echo "<pre>fff";

query=mysqli_query($con,"select count(lotw),count(eqsl),count(qrz) from log where mycall='$mycall'");
$row=mysqli_fetch_array($query);
printf("# lotw: %6d\n",$row[0]);
printf("# eqsl: %6d\n",$row[1]);
printf("# qrz: %6d\n",$row[2]);
mysqli_free_result($query);

echo "</pre>";

?>
