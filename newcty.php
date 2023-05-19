<?php

include "local.php";
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);

loadcty();

function loadcty(){
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
        $localtime=(float)substr($ff,$to1+1,$to2-$to1-1);
        $ff=substr($ff,0,$to1).substr($ff,$to2+1);
      }
      else $localtime=(float)$dd[8];
      
      if($ff[0]=="=")$pref=substr($f,1);
      else $pref=$ff;
      
      echo "$pref - $base $name $dxcc $cont $cqzone $ituzone $latitude $longitude $localtime\n";
    }
  }
  fclose($hh);
}

?>
