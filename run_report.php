<?php

echo "<pre>";

$qq=0;
$query=mysqli_query($con,"select start,callsign from log where mycall='$mycall' and dxcc=0");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $aux=searchcty($con,$row["callsign"]);
  if($aux!=null){
    $dxcc=(int)$aux["dxcc"];
    $mstart=$row["start"];
    $mcallsign=$row["callsign"];
    mysqli_query($con,"update log set dxcc=$dxcc where mycall='$mycall' and start='$mstart' and callsign='$mcallsign' and dxcc=0");
    $qq++;
  }
}
mysqli_free_result($query);
echo "Set dxcc: $qq\n\n";

unset($w);
$query=mysqli_query($con,"select callsign,freqtx,mode,lotw,eqsl,qrz,dxcc from log where mycall='$mycall'");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $band=$myband[floor($row["freqtx"]/1000000)];
  $mode=$mymode[$row["mode"]];
  $tt=$band.$mode;
  $dxcc=$row["dxcc"];
  $callsign=$row["callsign"];
  myinc($w,0,$tt);
  myinc($w,4,$dxcc);
  myinc($w,8,$dxcc,$callsign);
  if($row["lotw"]==1){myinc($w,1,$tt); myinc($w,5,$dxcc);}
  if($row["eqsl"]==1){myinc($w,2,$tt); myinc($w,6,$dxcc);}
  if($row["qrz"]==1){myinc($w,3,$tt); myinc($w,7,$dxcc);}
}
mysqli_free_result($query);

printf("<p id=\"myh1\">%10s %6s %8s %8s %8s</p>","Band/Mode","QSO","QSL.LOTW","QSL.EQSL","QSL.QRZ");
printf("<p id=\"myh2\">%10s %6d %8d %8d %8d</p>","Tot",array_sum($w[0]),array_sum($w[1]),array_sum($w[2]),array_sum($w[3]));
$key=array_keys($w[0]);
usort($key,"mycmpkey");
foreach($key as &$kk)@printf("%10s %6d %8d %8d %8d\n",$kk,$w[0][$kk],$w[1][$kk],$w[2][$kk],$w[3][$kk]);
echo "\n";

arsort($w[4]);
printf("<p id=\"myh1\">%10s %6s %8s %8s %8s %s</p>","dxcc","QSO","QSL.LOTW","QSL.EQSL","QSL.QRZ","Country");
printf("<p id=\"myh2\">%10s %6d %8d %8d %8d</p>","Tot",count($w[4]),count($w[5]),count($w[6]),count($w[7]));
$key=array_keys($w[4]);
foreach($key as &$kk){
  $query=mysqli_query($con,"select name from cty where dxcc=$kk limit 1");
  $row=mysqli_fetch_assoc($query);
  mysqli_free_result($query);
  @printf("%10s %8d %6d %8d %8d %8d %s\n",$kk,count($[8][$kk]),$w[4][$kk],$w[5][$kk],$w[6][$kk],$w[7][$kk],$row["name"]);
}

echo "</pre>";

?>
