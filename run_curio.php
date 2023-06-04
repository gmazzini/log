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
  myinc($w,0,$callsign); $h[0]="call"; $p[0]=0;
  myinc($w,1,$band); $h[1]="band"; $p[1]=0;
  myinc($w,2,$mode); $h[2]="mode"; $p[2]=0;
  if($row["lotw"]==1){
    myinc($w,3,$callsign); $h[3]="call.lotw"; $p[3]=0;
    myinc($w,4,$dxcc); $h[4]="dxcc.lotw"; $p[4]=1;
  }
  if($row["eqsl"]==1){
    myinc($w,5,$callsign); $h[5]="call.eqsl"; $p[5]=0;
    myinc($w,6,$dxcc); $h[6]="dxcc.eqsl"; $p[6]=1;
  }
  if($row["qrz"]==1){
    myinc($w,7,$callsign); $h[7]="call.qrz"; $p[7]=0;
    myinc($w,8,$dxcc); $h[8]="dxcc.qrz"; $p[8]=1;
  }
}
mysqli_free_result($query);

echo "<table>";

for($i=0;$i<=8;$i++){
  echo "<td>";
  arsort($w[$i]);
  $cc=0;
  printf("<b>%s</b><br>",$h[$i]);
  foreach($w[$i] as $k => $v){
    if($p[$i]==1){
      $query=mysqli_query($con,"select base from cty where dxcc=$k limit 1");
      $row=mysqli_fetch_assoc($query);
      $base=$row["base"];
      mysqli_free_result($query);
      printf("%s@%s %6d<br>",$k,$base,$v);
    }
    else printf("<pre>%10s %6d</pre>",$k,$v);
    $cc++;
    if($cc>30)break;
  }
  echo "</td>";
}

echo "</table>";

?>
