<?php
include "local.php";

$fp=pfsockopen($dxcaddr,$dxcport);
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

fwrite($fp,"IK4LZH");
while(!feof($fp)){
  $line=fgets($fp,128);
  $aux=explode(" ",$line);
  if($aux[0]=="DX"&&$aux[1]=="de"){
    $timespot=gmdate('Y-m-d H:i:s');
    mysqli_query($con,"insert into dxc (dx,spotter,freq,timespot) value ('$aux[4]','$aux[2]',$aux[3],'$timespot')");
    echo "insert into dxc (dx,spotter,freq,timespot) value ('$aux[4]','$aux[2]',$aux[3],'$timespot')\n";
  }
}

?>
