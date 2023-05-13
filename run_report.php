<?php

echo "<pre>";

$query=mysqli_query($con,"select count(lotw) from log where mycall='$mycall'");
$row=mysqli_fetch_array($query);
printf("# QSO: %6d\n",$row[0]);
mysqli_free_result($query);

echo "</pre>";

?>
