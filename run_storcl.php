<?php

echo "<pre>";
$ch=substr($run,-1);
if(substr($run,0,3)=="sto"){
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
  file_put_contents("sto$ch.dat","$frx\n$ftx\n$fmode\n");
}
else {
  $aux=file_get_contents("sto$ch.dat");
  $lines=explode("\n",$aux);
  $fp=@fsockopen($rigIP,$rigPORT);
  if($fp){
    stream_set_timeout($fp,0,200000);
    fwrite($fp,"F $lines[0]\n");
    // to be inserted other elements
    fclose($fp);
  }
}

echo "$run\n";
echo "</pre>";

?>
