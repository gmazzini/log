<?php

if(isset($_FILES['myfile']['tmp_name'])){
  $hh=fopen($_FILES['myfile']['tmp_name'],"r");
  $aux="";
  echo "<pre>";
  while(!feof($hh)){
    $line=trim(fgets($hh));
    if(substr($line,0,4)!="QSO:")continue;
    $parts=preg_split('/\s+/',$line);
    $start=$parts[3]." ".substr($parts[4],0,2).":".substr($parts[4],2,2).":00";
    $callsign=$parts[8];
    $contesttx=$parts[7];
    $contestrx=$parts[10];
    $contest=$Icontest;
    echo "update log set contesttx='$contesttx',contestrx='$contestrx',contest='$contest' where mycall='$mycall' and callsign='$callsign' and start='$start'\n";
  }
  echo "</pre>";
  fclose($hh);
}

?>
