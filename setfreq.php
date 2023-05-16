<?php

$setfreq=$_GET["freq"];
include "local.php";
$fp=@fsockopen($rigIP,$rigPORT);
if($fp){
  stream_set_timeout($fp,0,200000);
  fwrite($fp,"F $setfreq\n");
  fclose($fp);
}

?>
