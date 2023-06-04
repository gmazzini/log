<?php

unset($w);
$query=mysqli_query($con,"select callsign,freqtx,mode,lotw,eqsl,qrz,dxcc from log where mycall='$mycall'");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $band=$myband[floor($row["freqtx"]/1000000)];
  $mode=$mymode[$row["mode"]];
  $dxcc=$row["dxcc"];
  $callsign=$row["callsign"];
  myinc($w,0,$callsign);
  myinc($w,1,$band);
  myinc($w,2,$mode);
  if($row["lotw"]==1){myinc($w,3,$callsign);myinc($w,4,$dxcc);}
}
mysqli_free_result($query);

echo "<pre><table>";

for($i=0;$i<=4;$i++){
  echo "<td>";
  arsort($w[$i]);
  $cc=0;
  foreach($w[$i] as $k => $v){
    printf("%10s %6d<br>",$k,$v);
    $cc++;
    if($cc>30)break;
  }
  echo "</td>";
}

echo "</table></pre>";

?>
