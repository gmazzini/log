<?php

echo "<pre>";

$query=mysqli_query($con,"select freqtx,mode,lotw,eqsl,qrz from log where mycall='$mycall' and lotw=1");
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
  $band=$myband[floor($row[0]/1000000)];
  $mode=$mymode[$row[1]];
  $cc[$band.$mode]++;
  if($row[2]==1)$lotw[$band.$mode]++;
  if($row[3]==1)$eqsl[$band.$mode]++;
  if($row[4]==1)$qrz[$band.$mode]++;
  $tot++;
}
ksort($cc);
printf("%3d ",$tot);
foreach($cc as $key=>$value)printf("%s %6d %6d %6d %6d\n",$value,$lotw[$key],$eqsl[$key],$qrz[$key]);
mysqli_free_result($query);

echo "</pre>";

?>
