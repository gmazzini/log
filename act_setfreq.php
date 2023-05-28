<?php
include "local.php";
$setfreq=$_GET["freq"];
$rigIP=$_GET["rigIP"];
$rigPORT=$_GET["rigPORT"];

$fp=@fsockopen($rigIP,$rigPORT);
if($fp){
  stream_set_timeout($fp,0,200000);
  fwrite($fp,"F $setfreq\n");
  fclose($fp);
}

?>
