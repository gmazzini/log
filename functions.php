<?php

function myextract($buf,$token){
  $pos=stripos($buf,"<".$token.":");
  if($pos===false)return null;
  $pose=stripos($buf,">",$pos);
  $ltok=strlen($token)+2;
  $ll=(int)substr($buf,$pos+$ltok,$pose-$pos-$ltok);
  return substr($buf,$pose+1,$ll);
}

function myinsert($buf,$token){
  return "<".$token.":".strlen($buf).">".$buf; 
}

function myqso($con,$mycall,$callsign){
  $query=mysqli_query($con,"select freq,mode from log where mycall='$mycall' and callsign='$row[0]'");
  $row=mysqli_fetch_array($query);
  mysqli_free_result($query);
}

?>
