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
  myinc($w,0,$callsign); $h[0]="call";
  myinc($w,1,$band); $h[1]="band";
  myinc($w,2,$mode); $h[2]="mode";
  if($row["lotw"]==1){
    myinc($w,3,$callsign); $h[3]="call.lotw";
    myinc($w,4,$dxcc); $h[4]="dxcc.lotw";
  }
  if($row["eqsl"]==1){
    myinc($w,5,$callsign); $h[5]="call.eqsl";
    myinc($w,6,$dxcc); $h[6]="dxcc.eqsl";
  }
  if($row["qrz"]==1){
    myinc($w,7,$callsign); $h[7]="call.qrz";
    myinc($w,8,$dxcc); $h[8]="dxcc.qrz";
  }
}
mysqli_free_result($query);

echo "<pre><table>";

for($i=0;$i<=8;$i++){
  echo "<td>";
  arsort($w[$i]);
  $cc=0;
  printf("<b>%s</b>",$h[$i]);
  foreach($w[$i] as $k => $v){
    printf("%10s %6d<br>",$k,$v);
    $cc++;
    if($cc>30)break;
  }
  echo "</td>";
}

echo "</table></pre>";

?>
