<?php

echo "<pre>";

function myinc(&$w,$in,$el){
  if(isset($w[$in][$el]))$w[$in][$el]++;
  else $w[$in][$el]=1;
}

unset($w);
$query=mysqli_query($con,"select freqtx,mode,lotw,eqsl,qrz,callsign from log where mycall='$mycall'");
$tot=0;
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
  $band=$myband[floor($row[0]/1000000)];
  $mode=$mymode[$row[1]];
  $tt=$band.$mode;
  myinc($w,0,$tt);
  if($row[2]==1)myinc($w,1,$tt);
  if($row[3]==1)myinc($w,2,$tt);
  if($row[4]==1)myinc($w,3,$tt);
  $lookup=json_decode(findcall("IK4LZH"));
  print_r($lookup);
  echo $lookup["dxcc"];
  exit(0);
  $tot++;
}
mysqli_free_result($query);

printf("%6d\n",$tot);

$key=array_keys($w[0]);
function mycmpkey($a,$b){
  if($a==$b)return 0;
  return ((float)$a<(float)$b)?-1:1;
}
usort($key,mycmpkey);
foreach($key as &$kk)printf("%10s %6d %6d %6d %6d\n",$kk,$w[0][$kk],$w[1][$kk],$w[2][$kk],$w[3][$kk]);

echo "</pre>";

?>
