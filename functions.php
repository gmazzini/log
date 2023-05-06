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

function myqso($con,$call){
}

?>
