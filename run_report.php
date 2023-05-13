<?php

echo "<pre>";

$query=mysqli_query($con,"select freqtx,mode,lotw,eqsl,qrz from log where mycall='$mycall'");
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
  $band=$myband[floor($row[0]/1000000)];
  $mode=$mymode[$row[1]];
  if(isset($cc[$band.$mode]))$cc[$band.$mode]++;
  else $cc[$band.$mode]=1;
  if($row[2]==1){
    if(isset($lotw[$band.$mode]))$lotw[$band.$mode]++;
    else $lotw[$band.$mode]=1;
  }
  if($row[3]==1){
    if(isset($eqsl[$band.$mode]))$eqsl[$band.$mode]++;
    else $eqsl[$band.$mode]=1;
  }
  if($row[4]==1){
    if(isset($qrz[$band.$mode]))$qrz[$band.$mode]++;
    else $qrz[$band.$mode]=1;
  }
  $tot++;
}
mysqli_free_result($query);
ksort($cc);
printf("%6d\n",$tot);
print_r($cc);
foreach($cc as $key=>$value)printf("%s %6d %6d %6d %6d\n",$value,$lotw[$key],$eqsl[$key],$qrz[$key]);

echo "</pre>";

?>
