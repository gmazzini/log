<?php

echo "<pre>";
$query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,lotw,eqsl,qrz,contesttx,contestrx,contest from log where callsign like '$Icallsign' and mycall='$mycall' order by start desc limit $mypage offset $page");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $aux="";
  if((int)$row["lotw"]==1)$aux.="L";
  if((int)$row["eqsl"]==1)$aux.="E";
  if((int)$row["qrz"]==1)$aux.="Q";
  printf("%s %12s %7.1f %4s %5s %5s %-3s ",$row["start"],$row["callsign"],$row["freqtx"]/1000,$row["mode"],$row["signaltx"],$row["signalrx"],$aux);
  if(strlen($row["contest"])>0)printf("(%s,%s,%s)",$row["contest"],$row["contesttx"],$row["contestrx"]);
  printf("\n");
}
echo "</pre>";
mysqli_free_result($query);

?>
