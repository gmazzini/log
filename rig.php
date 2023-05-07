<?php

$fp=fsockopen("188.209.85.89",6789);
if($fp){
  stream_set_timeout($fp,0,200000);
  fwrite($fp,"fim\n");
  $line=trim(fgets($fp,30));
  printf("%7.1f\n",(int)$line/1000);
  $line=trim(fgets($fp,30));
  printf("%7.1f\n",(int)$line/1000);
  $line=trim(fgets($fp,30));
  printf("%s\n",$line);
}

?>
