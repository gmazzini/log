<?php

if(isset($_FILES['myfile']['tmp_name'])){
  $hh=fopen($_FILES['myfile']['tmp_name'],"r");
  $aux="";
  echo "<pre>";
  while(!feof($hh)){
    $line=trim(fgets($hh));
    $pp=stripos($line,"<eor>");
    if($pp===false)$aux.=$line;
    else {
      $aux.=substr($line,0,$pp);
      $callsign=myextract($aux,"call");
      $timeon=myextract($aux,"time_on");
      $dateon=myextract($aux,"qso_date");
      $ss=substr($dateon,0,4)."-".substr($dateon,4,2)."-".substr($dateon,6,2)." ".substr($timeon,0,2).":".substr($timeon,2,2).":00";
      $xss=strtotime($ss);
      $bb=date("Y-m-d H:i:s",$xss-$qslwin);
      $ee=date("Y-m-d H:i:s",$xss+$qslwin);
      $qsl=myextract($aux,"app_eqsl_ag");
      if($qsl=="Y"){
        echo "update log set eqsl=1 where mycall='$mycall' and callsign='$callsign' and start>='$bb' and start<='$ee'\n";
        mysqli_query($con,"update log set eqsl=1 where mycall='$mycall' and callsign='$callsign' and start>='$bb' and start<='$ee'");
      }
      $aux=substr($line,$pp+5);
    }
  }
  echo "</pre>";
  fclose($hh);
}

?>
