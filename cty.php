<?php

$sock=socket_create(AF_INET,SOCK_DGRAM,0);
socket_bind($sock,"0.0.0.0",44444);
for(;;){
  socket_recvfrom($sock,$buf,1000,0,$remote_ip,$remote_port);
  if($remote_ip<>"127.0.0.1")continue;
  socket_sendto($sock,$buf,strlen(buf),0,"127.0.0.1");
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
