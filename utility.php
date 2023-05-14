<?php

$myband=array(0=>0,1=>160,3=>80,5=>60,7=>40,10=>30,14=>20,18=>17,21=>15,24=>12,28=>10,29=>10,50=>6,144=>2,145=>2,430=>0.7,431=>0.7,432=>0.7,433=>0.7);
$mymode=array("SSB"=>"PH","CW"=>"CW","USB"=>"PH","LSB"=>"PH","FT8"=>"DG","RTTY"=>"DG","MFSK"=>"DG","FT4"=>"DG","FM"=>"PH","AM"=>"PH","PKT"=>"DG","TOR"=>"DG","AMTOR"=>"DG","PSK"=>"DG");

function mypost($token){
  global $_POST;
  if(isset($_POST[$token]))return $_POST[$token];
  return "";
}

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
  $tot=0;
  $query=mysqli_query($con,"select freqtx,mode,lotw,eqsl,qrz from log where mycall='$mycall' and callsign='$callsign'");
  for(;;){
    $row=mysqli_fetch_array($query);
    if($row==null)break;
    $band=$myband[floor($row[0]/1000000)];
    $mode=$mymode[$row[1]];
    $cc[$band.$mode]++;
    if($row[2]==1)$lotw[$band.$mode]++;
    if($row[3]==1)$eqsl[$band.$mode]++;
    if($row[4]==1)$qrz[$band.$mode]++;
    $tot++;
  }
  ksort($cc);
  $aux=sprintf("%3d ",$tot);
  foreach($cc as $key=>$value)$aux.=$key."(".$value.",".$lotw[$key].",".$eqsl[$key].",".$qrz[$key].") ";
  mysqli_free_result($query);
  return $aux;
}

function findcall($a){
  $fp=@fsockopen("127.0.0.1",22222);
  if($fp){
    fwrite($fp,$a);
    $lookup=gets($fp,1000);
    fclose($fp);
    return $lookup;
  }
}

?>
