<?php

$fp=fsockopen($_GET['rigIP'],$_GET['rigPORT']);
if($fp){
  stream_set_timeout($fp,0,200000);
  fwrite($fp,"sfim\n");
  fgets($fp,30);
  $line=trim(fgets($fp,30));
  $rx=(int)$line/1000;
  printf("%.1f\n",$rx);
  $line=trim(fgets($fp,30));
  $tx=(int)$line/1000;
  printf("%.1f\n",$tx);
  $line=trim(fgets($fp,30));
  printf("%s\n",$line);
}

?>
