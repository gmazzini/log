<?php
include "local.php";

$fp=pfsockopen($dxcaddr,$dxcport);
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
mysqli_query($con,"delete from dxc");
while(!feof($fp)){
  $line="";
  for(;;){
    $ch=fgetc($fp);
    if($ch=="\n"||$line=="login:")break;
    $line.=$ch;
  }
  if($line=="login:")fwrite($fp,"IK4LZH\n");
  else {
    $aux=preg_split('/\s+/',$line);
    if($aux[0]=="DX"&&$aux[1]=="de"){
      $timespot=gmdate('Y-m-d H:i:s');
      $spotter=substr($aux[2],0,-1);
      $freq=(float)$aux[3]*1000;
      mysqli_query($con,"insert into dxc (dx,spotter,freq,timespot) value ('$aux[4]','$spotter',$freq,'$timespot')");
    }
  }
}

?>
