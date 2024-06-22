<?php
  @$fp=fsockopen($_GET['rigIP'],$_GET['rigPORT']);
  if($fp){
    stream_set_timeout($fp,0,200000);
    fwrite($fp,"sfim\n");
    $split=(int)fgets($fp,30);
    $ricsplit=fgets($fp,30);
    $arx=trim(fgets($fp,30));
    $rx=(int)$arx/1000;
    printf("%.1f\n",$rx);
    $atx=trim(fgets($fp,30));
    $tx=(int)$atx/1000;
    printf("%.1f\n",$tx);
    $mode=trim(fgets($fp,30));
    $bandwidth=trim(fgets($fp,30));
    printf("%s\n",$mode);
    fclose($fp);
  }


?>
