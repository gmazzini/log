<?php

include "local.php";
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);

loadcty($con);

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
      
      if($ff[0]=="=")$pref=substr($ff,1);
      else $pref=$ff;
      mysqli_query($con,"insert into cty (base,name,dxcc,cont,cqzone,ituzone,latitude,longitude,gmtshift,pref) values ('$base','$name',$dxcc,'$cont',$cqzone,$ituzone,$latitude,$longitude,$gmtshift,'$pref')");
            
      echo "insert into cty (base,name,dxcc,cont,cqzone,ituzone,latitude,longitude,gmtshift,pref) values ('$base','$name',$dxcc,'$cont',$cqzone,$ituzone,$latitude,$longitude,$gmtshift,'$pref')";

    }
  }
  fclose($hh);
}

?>
