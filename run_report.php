<?php

echo "<pre>";

function myinc(&$w,$in,$el){
  if(isset($w[$in][$el]))$w[$in][$el]++;
  else $w[$in][$el]=1;
}

unset($w);
$query=mysqli_query($con,"select freqtx,mode,lotw,eqsl,qrz,dxcc from log where mycall='$mycall'");
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
  $band=$myband[floor($row[0]/1000000)];
  $mode=$mymode[$row[1]];
  $tt=$band.$mode;
  $dxcc=$row[5];
  myinc($w,0,$tt);
  myinc($w,4,$dxcc);
  if($row[2]==1){myinc($w,1,$tt); myinc($w,5,$dxcc);}
  if($row[3]==1){myinc($w,2,$tt); myinc($w,6,$dxcc);}
  if($row[4]==1){myinc($w,3,$tt); myinc($w,7,$dxcc);}
}
mysqli_free_result($query);

printf("<b>%10s %6d %6d %6d %6d</b>\n","band/mode",array_sum($w[0]),array_sum($w[1]),array_sum($w[2]),array_sum($w[3]));
$key=array_keys($w[0]);
function mycmpkey($a,$b){
  if($a==$b)return 0;
  return ((float)$a<(float)$b)?-1:1;
}
usort($key,mycmpkey);
foreach($key as &$kk)printf("%10s %6d %6d %6d %6d\n",$kk,$w[0][$kk],$w[1][$kk],$w[2][$kk],$w[3][$kk]);
echo "\n";

arsort($w[4]);
printf("<b>%10s %6d %6d %6d %6d</b>\n","dxcc",count($w[4]),count($w[5]),count($w[6]),count($w[7]));
$key=array_keys($w[4]);
foreach($key as &$kk)printf("%10s %6d %6d %6d %6d\n",$kk,$w[4][$kk],$w[5][$kk],$w[6][$kk],$w[7][$kk]);

echo "</pre>";

?>
