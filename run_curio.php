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
}
mysqli_free_result($query);

echo "<pre><table>";

echo "<td>";
arsort($w[0]);
$cc=0;
foreach($w[0] as $k => $v){
  printf("%10s %6d\n",$k,$v);
  $cc++;
  if($cc>30)break;
}
echo "</td>";

echo "</table></pre>";

?>
