<?php

echo "<pre>";
$query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,lotw,eqsl,qrz,contesttx,contestrx,contest from log where callsign like '$Icallsign' and mycall='$mycall' order by start desc limit $mypage offset $page");
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
  $aux="";
  if((int)$row[6]==1)$aux.="L";
  if((int)$row[7]==1)$aux.="E";
  if((int)$row[8]==1)$aux.="Q";
  printf("%s %12s %7.1f %4s %5s %5s %-3s ",$row[0],$row[1],$row[2]/1000,$row[3],$row[4],$row[5],$aux);
  if(strlen($row[11])>0)printf("(%s,%s,%s)",$row[11],$row[9],$row[10]);
  printf("\n");
}
echo "</pre>";
mysqli_free_result($query);

?>
