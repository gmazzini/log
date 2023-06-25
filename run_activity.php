<?php

echo "<pre>CIAO";

unset($w);
$query=mysqli_query($con,"select callsign,startx,mode,lotw,eqsl,qrz,dxcc from log where mycall='$mycall'");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $mode=$mymode[$row["mode"]];
  $dxcc=$row["dxcc"];
  $callsign=$row["callsign"];
  $wpx=wpx($callsign);
  $year=substr($row["startx"],0,4);
  myinc($w,0,$year);
  myinc($w,1,$year,$mode);
  myinc($w,2,$year,$dxcc);
  myinc($w,3,$year,$callsign);
  myinc($w,4,$year,$wpx;
  if($row["lotw"]==1){myinc($w,5,$year);}
  if($row["eqsl"]==1){myinc($w,6,$year);}
  if($row["qrz"]==1){myinc($w,7,year);}
}
mysqli_free_result($query);

printf("<p id=\"myh1\">%10s %6s %8s %8s %8s %8s %8s</p>","Band/Mode","QSO","QSO.uniq","QSO.wpx","QSL.LOTW","QSL.EQSL","QSL.QRZ");
printf("<p id=\"myh2\">%10s %6d %8d %8d %8d %8d %8d</p>","Tot",array_sum($w[0]),count($w[10]),count($w[12]),array_sum($w[1]),array_sum($w[2]),array_sum($w[3]));
$key=array_keys($w[0]);
usort($key,"mycmpkey");
foreach($key as &$kk)@printf("%10s %6d %8d %8d %8d %8d %8d\n",$kk,$w[0][$kk],count($w[9][$kk]),count($w[11][$kk]),$w[1][$kk],$w[2][$kk],$w[3][$kk]);
echo "\n";


echo "</pre>";

?>
