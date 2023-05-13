<?php

if(!isset($_FILES['myfile']['tmp_name']))break;
$hh=fopen($_FILES['myfile']['tmp_name'],"r");
$dateon="";
$freq="";
echo "<pre>";
while(!feof($hh)){
  $line=strtoupper(trim(fgets($hh)));
  if(substr($line,0,1)=="D"){$dateon=substr($line,1); continue;}
  if(substr($line,0,1)=="F"){$freqtx=substr($line,1)*1000; continue;}
  if(substr($line,0,1)=="M"){$mode=substr($line,1); continue;}
  $aux=explode(" ",$line);
  $timeon=$aux[0]."00";
  $timeoff=$aux[0]."59";
  $callsign=$aux[1];
  $freqrx=$freqtx;
  if($row[2]==null)$signaltx="59";
  else $signaltx=$row[2];
  if($row[3]==null)$signalrx="59";
  else $signalrx=$row[3];
  $dateoff=$dateon;
  $start=substr($dateon,0,4)."-".substr($dateon,4,2)."-".substr($dateon,6,2)." ".substr($timeon,0,2).":".substr($timeon,2,2).":".substr($timeon,4,2);
  $end=substr($dateoff,0,4)."-".substr($dateoff,4,2)."-".substr($dateoff,6,2)." ".substr($timeoff,0,2).":".substr($timeoff,2,2).":".substr($timeoff,4,2);
  mysqli_query($con,"insert into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx,contesttx,contestrx,contest) value ('$mycall','$callsign','$start','$end','$mode',$freqtx,$freqrx,'$signaltx','$signalrx','','','')");
  echo "('$mycall','$callsign','$start','$end','$mode',$freqtx,$freqrx,'$signaltx','$signalrx','','','')\n";
 }
 echo "</pre>";
 fclose($hh);
 
?>
