<?php

$myband=array(0=>0,1=>160,3=>80,5=>60,7=>40,10=>30,14=>20,18=>17,21=>15,24=>12,28=>10,29=>10,50=>6,144=>2,145=>2,430=>0.7,431=>0.7,432=>0.7,433=>0.7);
$mymode=array("SSB"=>"PH","CW"=>"CW","USB"=>"PH","LSB"=>"PH","FT8"=>"DG","RTTY"=>"DG","MFSK"=>"DG","FT4"=>"DG","FM"=>"PH","AM"=>"PH","PKT"=>"DG","TOR"=>"DG","AMTOR"=>"DG");

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
  global $z1,$z2,$z3,$loadcty;
  if(!isset($loadcty)){
    $loadcty=1;
    $j=0;
    $hh=fopen(dirname(__FILE__)."/cty.csv","r");
    while(!feof($hh)){
      $dd=fgetcsv($hh,100000);
      if($dd===FALSE)continue;
      $ee=explode(" ",substr($dd[9],0,-1));
      foreach($ee as $ff){
        $z1[$j]["base"]=$dd[0];
        $z1[$j]["name"]=$dd[1];
        $z1[$j]["dxcc"]=$dd[2];
        
        $to1=strpos($ff,"(");
        if($to1!==false){
          $to2=strpos($ff,")",$to1+1);
          $z1[$j]["cqzone"]=(int)substr($ff,$to1+1,$to2-$to1-1);
          $ff=substr($ff,0,$to1).substr($ff,$to2+1);
        }
        else $z1[$j]["cqzone"]=(int)$dd[4];
        
        $to1=strpos($ff,"[");
        if($to1!==false){
          $to2=strpos($ff,"]",$to1+1);
          $z1[$j]["ituzone"]=(int)substr($ff,$to1+1,$to2-$to1-1);
          $ff=substr($ff,0,$to1).substr($ff,$to2+1);
        }
        else $z1[$j]["ituzone"]=(int)$dd[5];
        
        $to1=strpos($ff,"{");
        if($to1!==false){
          $to2=strpos($ff,"}",$to1+1);
          $z1[$j]["cont"]=substr($ff,$to1+1,$to2-$to1-1);
          $ff=substr($ff,0,$to1).substr($ff,$to2+1);
        }
        else $z1[$j]["cont"]=$dd[3];
        
        $to1=strpos($ff,"<");
        if($to1!==false){
          $to2=strpos($ff,">",$to1+1);
          $ff=substr($ff,0,$to1).substr($ff,$to2+1);
        }
        
        $to1=strpos($ff,"~");
        if($to1!==false){
          $to2=strpos($ff,"~",$to1+1);
          $ff=substr($ff,0,$to1).substr($ff,$to2+1);
        }
        
        if($ff[0]=="="){
          $z2[substr($ff,1)]=$j;
        }
        
        $to1=strpos($ff,"/");
        if($to1!==false){
          $pre=substr($ff,0,$to1);
          $post=strtoupper(substr($ff,$to1+1));
          $z3[$pre.$post]=$j;
          for($w1=48;$w1<58;$w1++)$z3[$pre.chr($w1).$post]=$j;
          for($w1=48;$w1<58;$w1++)for($w2=48;$w2<58;$w2++)$z3[$pre.chr($w1).chr($w2).$post]=$j;
        }
        
        $z1[$j]["prefix"]=$ff;
        $z3[$ff]=$j;
        $j++;
      }
    }
    fclose($hh);
  }
  
  $call=strtoupper($a);
  if(isset($z2[$call]))return $z1[$z2[$call]];
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
  $s=-1;
  for($q=1;$q<=$lc;$q++){
    if(isset($z3[substr($call,0,$q)]))$s=$z3[substr($call,0,$q)];
  }
  if($s!=-1)return $z1[$s];
}

?>
