<?php
include "local.php";
include "utility.php";
$sock=socket_create(AF_INET,SOCK_DGRAM,0);
socket_bind($sock,"0.0.0.0",2333);
$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
for(;;){
  socket_recvfrom($sock,$aux,1000,0,$remote_ip,$remote_port);
  echo $aux."\n";
  $secretkey=myextract($aux,"comment");
  if($secretkey!=$secret_rx)continue;
  $timeon=myextract($aux,"time_on");
  if(strlen($timeon)==4)$timeon.="00";
  $timeoff=myextract($aux,"time_off");
  if(strlen($timeoff)==4)$timeoff.="00";
  if($timeon==$timeoff)continue;
  $mycall=myextract($aux,"station_callsign");
  $callsign=myextract($aux,"call");
  $freqtx=myextract($aux,"freq")*1000000;
  $freqrx=myextract($aux,"freq_rx")*1000000;
  $signaltx=myextract($aux,"rst_sent");
  $signalrx=myextract($aux,"rst_rcvd");
  $mode=myextract($aux,"mode");
  $contesttx=myextract($aux,"stx_string");
  if(strlen($contesttx)==0)$contesttx=myextract($aux,"stx");
  $contestrx=myextract($aux,"srx_string");
  if(strlen($contestrx)==0)$contestrx=myextract($aux,"srx");
  $contest=myextract($aux,"contest_id");
  $dateon=myextract($aux,"qso_date");
  $dateoff=myextract($aux,"qso_date_off");
  if(strlen($dateoff)==0)$dateoff=$dateon;
  $start=substr($dateon,0,4)."-".substr($dateon,4,2)."-".substr($dateon,6,2)." ".substr($timeon,0,2).":".substr($timeon,2,2).":".substr($timeon,4,2);
  $end=substr($dateoff,0,4)."-".substr($dateoff,4,2)."-".substr($dateoff,6,2)." ".substr($timeoff,0,2).":".substr($timeoff,2,2).":".substr($timeoff,4,2);
  mysqli_query($con,"insert ignore into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx,contesttx,contestrx,contest) value ('$mycall','$callsign','$start','$end','$mode',$freqtx,$freqrx,'$signaltx','$signalrx','$contesttx','$contestrx','$contest')");
  echo "insert ignore into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx,contesttx,contestrx,contest) value ('$mycall','$callsign','$start','$end','$mode',$freqtx,$freqrx,'$signaltx','$signalrx','$contesttx','$contestrx','$contest')\n";
}
?>
