<?php

$myband=array(0=>0,1=>160,3=>80,5=>60,7=>40,10=>30,14=>20,18=>17,21=>15,24=>12,28=>10,29=>10,50=>6,144=>2,145=>2,430=>0.7,431=>0.7,432=>0.7,433=>0.7);
$mymode=array("SSB"=>"PH","CW"=>"CW","USB"=>"PH","LSB"=>"PH","FT8"=>"DG","RTTY"=>"DG","MFSK"=>"DG","FT4"=>"DG","FM"=>"PH","AM"=>"PH","PKT"=>"DG","TOR"=>"DG","AMTOR"=>"DG","PSK"=>"DG");

function myinc(&$w,$in,$el){
  if(isset($w[$in][$el]))$w[$in][$el]++;
  else $w[$in][$el]=1;
}

function mycmpkey($a,$b){
  if($a==$b)return 0;
  $aa=((float)$a)*1000+ord(substr($a,strcspn($a,"CPD"),1));
  $bb=((float)$b)*1000+ord(substr($b,strcspn($b,"CPD"),1));
  return ($aa<$bb)?-1:1;
}

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
  unset($w);
  $query=mysqli_query($con,"select freqtx,mode,lotw,eqsl,qrz from log where mycall='$mycall' and callsign='$callsign'");
  for(;;){
    $row=mysqli_fetch_array($query);
    if($row==null)break;
    $band=$myband[floor($row[0]/1000000)];
    $mode=$mymode[$row[1]];
    $tt=$band.$mode;
    myinc($w,0,$tt);
    if($row[2]==1)myinc($w,1,$tt);
    if($row[3]==1)myinc($w,2,$tt);
    if($row[4]==1)myinc($w,3,$tt);
  }
  mysqli_free_result($query);
  $key=array_keys($w[0]);
  usort($key,mycmpkey);
  $aux=sprintf("%3d ",array_sum($w[0]));
  foreach($key as &$kk)$aux.=$kk."(".$w[0][$kk].",".$w[1][$kk].",".$w[2][$kk].",".$w[3][$kk].") ";
  return $aux;
}

function findcall($a){
  $fp=@fsockopen("127.0.0.1",22222);
  if($fp){
    fwrite($fp,$a);
    $lookup=fgets($fp,1000);
    fclose($fp);
    return $lookup;
  }
}

?>
