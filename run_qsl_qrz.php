<?php

if(!isset($_FILES['myfile']['tmp_name']))break;
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
    $bb=substr($dateon,0,4)."-".substr($dateon,4,2)."-".substr($dateon,6,2)." ".substr($timeon,0,2).":".substr($timeon,2,2).":00";
	  $ee=substr($dateon,0,4)."-".substr($dateon,4,2)."-".substr($dateon,6,2)." ".substr($timeon,0,2).":".substr($timeon,2,2).":59";
	  $qsl=myextract($aux,"app_qrzlog_status");
    if($qsl=="C"){
      echo "qsl via qrz on $callsign $dateon $timeon\n";
      mysqli_query($con,"update log set qrz=1 where mycall='$mycall' and callsign='$callsign' and start>='$bb' and start<='$ee'");
    }
    $aux=substr($line,$pp+5);
  }
}
echo "</pre>";
fclose($hh);

?>