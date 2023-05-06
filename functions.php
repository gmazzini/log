<?php

$myband=array(1=>"160",3=>"80",5=>"60",7=>"40",10=>"30",14=>"20",18=>"17",21=>"15",24=>"12",28=>"10",29=>"10",50=>"6",144=>"2",145=>"2");
$mymode=array("SSB"=>"PH","CW"=>"CW","USB"=>"PH","LSB"=>"PH","FT8"=>"DG","RTTY"=>"DG","MFSK"=>"DG","FT4"=>"DG","FM"=>"PH","AM"=>"PH");

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
  global $myband,$mymode;
  $query=mysqli_query($con,"select freqtx,mode from log where mycall='$mycall' and callsign='$callsign'");
  for(;;){
    $row=mysqli_fetch_array($query);
    if($row==null)break;
    $band=$myband[floor($row[0]/1000000)];
    $mode=$mymode[$row[1]];
    $cc[$band.$mode]++;
    echo "$band $mode ".floor($row[0]/1000)."\n";
  }
  $aux="--- $mycall $callsign ";
  foreach($cc as $key=>$value)$aux.=$key."(".$value.") ";
  mysqli_free_result($query);
  return $aux;
}

?>
