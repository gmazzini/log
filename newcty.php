<?php

include "local.php";
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
// loadcty($con);
print_r(searchcty($con,"R3TT/UF6V"));

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

function loadcty($con){
  mysqli_query($con,"truncate table cty");
  $hh=fopen("cty.csv","r");
  while(!feof($hh)){
    $dd=fgetcsv($hh,100000);
    if($dd===FALSE)continue;
    $ee=explode(" ",substr($dd[9],0,-1));
    foreach($ee as $ff){
      $base=$dd[0];
      $name=$dd[1];
      $dxcc=$dd[2];
      
      $to1=strpos($ff,"{");
      if($to1!==false){
        $to2=strpos($ff,"}",$to1+1);
        $cont=substr($ff,$to1+1,$to2-$to1-1);
        $ff=substr($ff,0,$to1).substr($ff,$to2+1);
      }
      else $cont=$dd[3];
      
      $to1=strpos($ff,"(");
      if($to1!==false){
        $to2=strpos($ff,")",$to1+1);
        $cqzone=(int)substr($ff,$to1+1,$to2-$to1-1);
        $ff=substr($ff,0,$to1).substr($ff,$to2+1);
      }
      else $cqzone=(int)$dd[4];
      
      $to1=strpos($ff,"[");
      if($to1!==false){
        $to2=strpos($ff,"]",$to1+1);
        $ituzone=(int)substr($ff,$to1+1,$to2-$to1-1);
        $ff=substr($ff,0,$to1).substr($ff,$to2+1);
      }
      else $ituzone=(int)$dd[5];
      
      $to1=strpos($ff,"<");
      if($to1!==false){
        $to2=strpos($ff,">",$to1+1);
        $to3=strpos($ff,"/",$to1+1);
        $latitude=(float)substr($ff,$to1+1,$to3-$to1-1);
        $longitude=(float)substr($ff,$to3+1,$to2-$to3-1);
        $ff=substr($ff,0,$to1).substr($ff,$to2+1);
      }
      else {
        $latitude=(float)$dd[6];
        $longitude=(float)$dd[7];
      }
      
      $to1=strpos($ff,"~");
      if($to1!==false){
        $to2=strpos($ff,"~",$to1+1);
        $gmtshift=(float)substr($ff,$to1+1,$to2-$to1-1);
        $ff=substr($ff,0,$to1).substr($ff,$to2+1);
      }
      else $gmtshift=(float)$dd[8];
      
      if($ff[0]=="=")$prefix=substr($ff,1);
      else $prefix=$ff;
      mysqli_query($con,"insert into cty (base,name,dxcc,cont,cqzone,ituzone,latitude,longitude,gmtshift,prefix) values ('$base','$name',$dxcc,'$cont',$cqzone,$ituzone,$latitude,$longitude,$gmtshift,'$prefix')");
    }
  }
  fclose($hh);
}

?>
