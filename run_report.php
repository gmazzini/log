<?php

echo "<pre>";

$query=mysqli_query($con,"select freqtx,mode,lotw,eqsl,qrz from log where mycall='$mycall'");
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
mysqli_free_result($query);
ksort($cc);
printf("%6d\n",$tot);
print_r($cc);
foreach($cc as $key=>$value)printf("%s %6d %6d %6d %6d\n",$value,$lotw[$key],$eqsl[$key],$qrz[$key]);

echo "</pre>";

?>
