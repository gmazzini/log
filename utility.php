<?php

$myshow=1;
$myband=array(0=>0,1=>160,3=>80,5=>60,7=>40,10=>30,14=>20,18=>17,21=>15,24=>12,28=>10,29=>10,50=>6,144=>2,145=>2,430=>0.7,431=>0.7,432=>0.7,433=>0.7);
$mymode=array("SSB"=>"PH","CW"=>"CW","USB"=>"PH","LSB"=>"PH","FT8"=>"DG","RTTY"=>"DG","MFSK"=>"DG","FT4"=>"DG","FM"=>"PH","AM"=>"PH","PKT"=>"DG","TOR"=>"DG","AMTOR"=>"DG","PSK"=>"DG");
$qslwin=240;

function cyrlat($a,$t){
  if($t==0)return $a;
  $cyr =array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у', 
  'ф','х','ц','ч','ш','щ','ъ', 'ы','ь', 'э', 'ю','я',
  'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У',
  'Ф','Х','Ц','Ч','Ш','Щ','Ъ', 'Ы','Ь', 'Э', 'Ю','Я' );
  $lat = array( 'a','b','v','g','d','e','e','zh','z','i','y','k','l','m','n','o','p','r','s','t','u',
  'f' ,'h' ,'ts' ,'ch','sh' ,'sht' ,'i', 'y', 'y', 'e' ,'yu' ,'ya','A','B','V','G','D','E','E','Zh',
  'Z','I','Y','K','L','M','N','O','P','R','S','T','U',
  'F' ,'H' ,'Ts' ,'Ch','Sh' ,'Sht' ,'I' ,'Y' ,'Y', 'E', 'Yu' ,'Ya' );
  return str_replace($cyr,$lat,$a);
}

function mycurlget($ff){
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,$ff);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_USERAGENT,"curl/7.74.0");
  $out=curl_exec($ch);
  curl_close($ch);
  return $out;
}

function wpx($s){
  for($i=strlen($s)-1;$i>=0;$i--){
    $ls=ord($s[$i]);
    if($ls>47 && $ls<58)break;
  }
  return substr($s,0,$i+1);
}

function myprint($a){
  foreach($a as $key => $value){
    if(is_float($value))printf("[%s]=>%.2f ",$key,$value);
    elseif(is_numeric($value))printf("[%s]=>%d ",$key,$value);
    else printf("[%s]=>%s ",$key,$value);
  }
}

function griddb($con,$call1,$call2){
  $o["griddistance"]=-1;
  $query=mysqli_query($con,"select grid from who where callsign='$call1'");
  $row=mysqli_fetch_assoc($query);
  mysqli_free_result($query);
  if($row==null)return $o;
  $aux=$row["grid"];
  $g1=strtoupper(substr($aux,0,2)).substr($aux,2,2).strtolower(substr($aux,4,2));
  $query=mysqli_query($con,"select grid from who where callsign='$call2'");
  $row=mysqli_fetch_assoc($query);
  mysqli_free_result($query);
  if($row==null)return $o;
  $aux=$row["grid"];
  $g2=strtoupper(substr($aux,0,2)).substr($aux,2,2).strtolower(substr($aux,4,2));
  $o["grid1"]=$g1;
  $o["grid2"]=$g2;
  $x1["latitude"]=(ord(substr($g1,1,1))-65)*10+(int)substr($g1,3,1)+(ord(substr($g1,5,1))-97)/24+1/48-90;
  $x1["longitude"]=-((ord(substr($g1,0,1))-65)*20+(int)substr($g1,2,1)*2+(ord(substr($g1,4,1))-97)/12+1/24-180);
  $x2["latitude"]=(ord(substr($g2,1,1))-65)*10+(int)substr($g2,3,1)+(ord(substr($g2,5,1))-97)/24+1/48-90;
  $x2["longitude"]=-((ord(substr($g2,0,1))-65)*20+(int)substr($g2,2,1)*2+(ord(substr($g2,4,1))-97)/12+1/24-180);
  $lat1=(float)$x1["latitude"]*M_PI/180;
  $lat2=(float)$x2["latitude"]*M_PI/180;
  $lon1=(float)$x1["longitude"]*M_PI/180;
  $lon2=(float)$x2["longitude"]*M_PI/180;
  $a=pow(sin(($lat1-$lat2)/2),2)+cos($lat1)*cos($lat2)*pow(sin(($lon1-$lon2)/2),2);
  $c=2*atan2(sqrt($a),sqrt(1-$a));
  $o["griddistance"]=6371*$c;
  $b=atan2(sin($lon1-$lon2)*cos($lat2),cos($lat1)*sin($lat2)-sin($lat1)*cos($lat2)*cos($lon1-$lon2))/M_PI*180;
  if($b<0)$b+=360;
  $o["gridbeaming"]=$b;
  return $o;
}

function dbt($con,$call1,$call2){
  $x1=searchcty($con,$call1);
  $x2=searchcty($con,$call2);
  $lat1=(float)$x1["latitude"]*M_PI/180;
  $lat2=(float)$x2["latitude"]*M_PI/180;
  $lon1=(float)$x1["longitude"]*M_PI/180;
  $lon2=(float)$x2["longitude"]*M_PI/180;
  $a=pow(sin(($lat1-$lat2)/2),2)+cos($lat1)*cos($lat2)*pow(sin(($lon1-$lon2)/2),2);
  $c=2*atan2(sqrt($a),sqrt(1-$a));
  $o["distance"]=6371*$c;
  $b=atan2(sin($lon1-$lon2)*cos($lat2),cos($lat1)*sin($lat2)-sin($lat1)*cos($lat2)*cos($lon1-$lon2))/M_PI*180;
  if($b<0)$b+=360;
  $o["beaming"]=$b;
  $o["shift"]=$x1["gmtshift"]-$x2["gmtshift"];
  return $o;
}
  
function myinc(&$w,$in,$el,$opt=null){
  if(!isset($opt)){
    if(isset($w[$in][$el]))$w[$in][$el]++;
    else $w[$in][$el]=1;
  }
  else {
    if(isset($w[$in][$el][$opt]))$w[$in][$el][$opt]++;
    else $w[$in][$el][$opt]=1;
  }
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

function mysto($con,$channel,$content){
  $aux=mysqli_real_escape_string($con,$content);
  mysqli_query($con,"replace into store (channel,content) values ('$channel','$aux')");  
}

function myrcl($con,$channel){
  $query=mysqli_query($con,"select content from store where channel='$channel'");
  $row=mysqli_fetch_assoc($query);
  if($row==null)$content="";
  else $content=$row["content"];
  mysqli_free_result($query);
  return $content;
}

function myqso($con,$mycall,$callsign){
  global $myband,$mymode;
  unset($w);
  $timenow=time();
  $timemin=4000000000;
  $query=mysqli_query($con,"select start,freqtx,mode,lotw,eqsl,qrz from log where mycall='$mycall' and callsign='$callsign'");
  for(;;){
    $row=mysqli_fetch_assoc($query);
    if($row==null)break;
    $timediff=$timenow-strtotime($row["start"]." UTC");
    if($timemin>$timediff)$timemin=$timediff;
    $band=$myband[floor($row["freqtx"]/1000000)];
    $mode=$mymode[$row["mode"]];
    $tt=$band.$mode;
    myinc($w,0,$tt);
    if($row["lotw"]==1)myinc($w,1,$tt);
    if($row["eqsl"]==1)myinc($w,2,$tt);
    if($row["qrz"]==1)myinc($w,3,$tt);
  }
  mysqli_free_result($query);
  if(!isset($w[0]))return "";
  $key=array_keys($w[0]);
  usort($key,"mycmpkey");
  
  if($timemin<3600)$aux=sprintf("%2dm",$timemin/60);
  elseif ($timemin<86400)$aux=sprintf("%2dh",$timemin/3600);
  elseif ($timemin<2592000)$aux=sprintf("%2dD",$timemin/86400);
  elseif ($timemin<31536000)$aux=sprintf("%2dM",$timemin/2592000);
  else $aux=sprintf("%2dY",$timemin/31536000);
  
  $aux=sprintf("%3s %3d ",$aux,array_sum($w[0]));
  foreach($key as &$kk){
    if(isset($w[0][$kk]))$w0=$w[0][$kk]; else $w0="";
    if(isset($w[1][$kk]))$w1=$w[1][$kk]; else $w1="";
    if(isset($w[2][$kk]))$w2=$w[2][$kk]; else $w2="";
    if(isset($w[3][$kk]))$w3=$w[3][$kk]; else $w3="";
    $aux.=$kk."(".$w0.",".$w1.",".$w2.",".$w3.") ";
  }
  return $aux;
}

function searchcty($con,$call){
  $query=mysqli_query($con,"select base,name,dxcc,cont,cqzone,ituzone,latitude,longitude,gmtshift from cty where prefix='$call'");
  $row=mysqli_fetch_assoc($query);
  mysqli_free_result($query);
  if($row!=null)return $row;
  
  $to1=strrpos($call,"/");
  if($to1!==false){
    if(in_array(substr($call,$to1+1),array("P","M","LH","MM","AM","A","QRP","0","1","2","3","4","5","6","7","8","9"))){
      $call=substr($call,0,$to1);
      $to1=strrpos($call,"/");
    }
    if($to1!==false){
      $lc=strlen($call);
      if($to1<$lc-$to1-1)$call=substr($call,0,$to1);
      else $call=substr($call,$to1+1);
    }
  }
  
  $lc=strlen($call);
  for($q=$lc;$q>0;$q--){
    $prefix=substr($call,0,$q);
    $query=mysqli_query($con,"select base,name,dxcc,cont,cqzone,ituzone,latitude,longitude,gmtshift from cty where prefix='$prefix'");
    $row=mysqli_fetch_assoc($query);
    mysqli_free_result($query);
    if($row!=null)return $row;
  }
}

?>
