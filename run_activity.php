<?php

echo "<pre>";

$tt=strtotime("-1 year",time());
$ei2=date("Y-m",$tt);
$es2=date("Y-m",time());
$tt=strtotime("-1 month",time());
$ei3=date("Y-m-d",$tt);
$es3=date("Y-m-d",time());

unset($w);
$query=mysqli_query($con,"select callsign,start,mode,lotw,eqsl,qrz,dxcc from log where mycall='$mycall'");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $mode=$mymode[$row["mode"]];
  $dxcc=$row["dxcc"];
  $callsign=$row["callsign"];
  $wpx=wpx($callsign);
  $start=$row["start"];

  myinc($w,0,"ALL");
  myinc($w,1,"ALL",$mode);
  myinc($w,2,"ALL",$dxcc);
  myinc($w,3,"ALL",$callsign);
  myinc($w,4,"ALL",$wpx);
  
  $year=substr($start,0,4);
  myinc($w,0,$year);
  myinc($w,1,$year,$mode);
  myinc($w,2,$year,$dxcc);
  myinc($w,3,$year,$callsign);
  myinc($w,4,$year,$wpx);
  if($row["lotw"]==1){myinc($w,5,$year); myinc($w,5,"ALL");}
  if($row["eqsl"]==1){myinc($w,6,$year); myinc($w,6,"ALL");}
  if($row["qrz"]==1){myinc($w,7,$year); myinc($w,7,"ALL");}
  
  $yymm=substr($start,0,7);
  if($yymm>=$ei2&&$yymm<=$es2){
    myinc($w,8,$yymm);
    myinc($w,9,$yymm,$mode);
    myinc($w,10,$yymm,$dxcc);
    myinc($w,11,$yymm,$callsign);
    myinc($w,12,$yymm,$wpx);
    if($row["lotw"]==1)myinc($w,13,$yymm);
    if($row["eqsl"]==1)myinc($w,14,$yymm);
    if($row["qrz"]==1)myinc($w,15,$yymm);
  }

  $yymmdd=substr($start,0,10);
  if($yymmdd>=$ei3&&$yymmdd<=$es3){
    myinc($w,16,$yymmdd);
    myinc($w,17,$yymmdd,$mode);
    myinc($w,18,$yymmdd,$dxcc);
    myinc($w,19,$yymmdd,$callsign);
    myinc($w,20,$yymmdd,$wpx);
    if($row["lotw"]==1)myinc($w,21,$yymmdd);
    if($row["eqsl"]==1)myinc($w,22,$yymmdd);
    if($row["qrz"]==1)myinc($w,23,$yymmdd);
  }
  
}
mysqli_free_result($query);

printf("<p id=\"myh1\">%10s %7s %7s %7s %7s %8s %7s %4s %8s %8s %8s</p>","YYYY-MM-DD","QSO","QSO.cw","QSO.dg","QSO.ph","QSO.uniq","QSO.wpx","DXCC","QSL.LOTW","QSL.EQSL","QSL.QRZ");
$key=array_keys($w[16]);
rsort($key);
foreach($key as &$kk)@printf("%10s %7d %7d %7d %7d %8d %7d %4d %8d %8d %8d\n",$kk,$w[16][$kk],$w[17][$kk]["CW"],$w[17][$kk]["DG"],$w[17][$kk]["PH"],count($w[19][$kk]),count($w[20][$kk]),count($w[18][$kk]),$w[21][$kk],$w[22][$kk],$w[23][$kk]);
echo "\n";

printf("<p id=\"myh1\">%10s %7s %7s %7s %7s %8s %7s %4s %8s %8s %8s</p>","YYYY-MM","QSO","QSO.cw","QSO.dg","QSO.ph","QSO.uniq","QSO.wpx","DXCC","QSL.LOTW","QSL.EQSL","QSL.QRZ");
$key=array_keys($w[8]);
rsort($key);
foreach($key as &$kk)@printf("%10s %7d %7d %7d %7d %8d %7d %4d %8d %8d %8d\n",$kk,$w[8][$kk],$w[9][$kk]["CW"],$w[9][$kk]["DG"],$w[9][$kk]["PH"],count($w[11][$kk]),count($w[12][$kk]),count($w[10][$kk]),$w[13][$kk],$w[14][$kk],$w[15][$kk]);
echo "\n";

printf("<p id=\"myh1\">%10s %7s %7s %7s %7s %8s %7s %4s %8s %8s %8s</p>","YYYY","QSO","QSO.cw","QSO.dg","QSO.ph","QSO.uniq","QSO.wpx","DXCC","QSL.LOTW","QSL.EQSL","QSL.QRZ");
$key=array_keys($w[0]);
rsort($key);
foreach($key as &$kk)@printf("%10s %7d %7d %7d %7d %8d %7d %4d %8d %8d %8d\n",$kk,$w[0][$kk],$w[1][$kk]["CW"],$w[1][$kk]["DG"],$w[1][$kk]["PH"],count($w[3][$kk]),count($w[4][$kk]),count($w[2][$kk]),$w[5][$kk],$w[6][$kk],$w[7][$kk]);
echo "\n";

echo "</pre>";

?>
