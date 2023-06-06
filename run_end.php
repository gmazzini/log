<?php

$qsoend=gmdate('Y-m-d H:i:s');
if($riglink){
  $frx=$_POST['Prigrx']*1000;
  $ftx=$_POST['Prigtx']*1000;
  if($ftx==0)$ftx=$frx;
  $fmode=$_POST['Prigm'];
}
else {
  $frx=$Ifreq*1000;
  $ftx=$frx;
  $fmode=$Imode;
}
if($runcontest){
  $Acontesttx=$Icontesttx;
  $Acontestrx=$Icontestrx;
  $Acontest=$Icontest;
}
else {
  $Acontesttx="";
  $Acontestrx="";
  $Acontest="";
}
if($myshow)echo "insert into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx,contesttx,contestrx,contest) value ('$mycall','$Icallsign','$qsostart','$qsoend','$fmode',$ftx,$frx,'$Isignaltx','$Isignalrx','$Acontesttx','$Acontestrx','$Acontest')\n";
mysqli_query($con,"insert into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx,contesttx,contestrx,contest) value ('$mycall','$Icallsign','$qsostart','$qsoend','$fmode',$ftx,$frx,'$Isignaltx','$Isignalrx','$Acontesttx','$Acontestrx','$Acontest')");

?>
