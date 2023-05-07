<?php

$fp=fsockopen("188.209.85.89",6789);
if($fp){
  stream_set_timeout($fp,0,200000);
  fwrite($fp,"fim\n");
  $line=trim(fgets($fp,30));
  $tx=(int)$line/1000;
  printf("%.1f\n",$tx);
  $line=trim(fgets($fp,30));
  $rx=(int)$line/1000;
  if($rx==0)$split=0;
  else $split=$tx-$rx;
  printf("%.1f\n",$split);
  $line=trim(fgets($fp,30));
  printf("%s\n",$line);
}

?>
