<?php

echo "<pre>";

$qq=0;
$query=mysqli_query($con,"select start,callsign from log where mycall='$mycall' and dxcc=0");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $aux=searchcty($con,$row["callsign"]);
  if($aux!=null){
    $dxcc=(int)$aux["dxcc"];
    $mstart=$row["start"];
    $mcallsign=$row["callsign"];
    mysqli_query($con,"update log set dxcc=$dxcc where mycall='$mycall' and start='$mstart' and callsign='$mcallsign' and dxcc=0");
    $qq++;
  }
}
mysqli_free_result($query);
echo "Set dxcc: $qq\n\n";

$query=mysqli_query($con,"select callsign,start,command,myupdate from booking where mycall='$mycall' order by myupdate");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  $start=$row["start"];
  $command=$row["command"];
  $myupdate=$row["myupdate"];
  $cc=explode(",",$command);
  switch($cc[0]){
    case "DEL":
    case "DELETE":
      $do="delete from log where mycall='$mycall' and callsign='$callsign' and start='$start'";
      break;
    case "FT":
    case "FREQTX":
      $freqtx=(int)($cc[1]*1000);
      $do="update log set freqtx=$freqtx where mycall='$mycall' and callsign='$callsign' and start='$start'";
      break;
    case "FR":
    case "FREQRX":
      $freqrx=(int)($cc[1]*1000);
      $do="update log set freqrx=$freqrx where mycall='$mycall' and callsign='$callsign' and start='$start'";
      break;
    case "M":
    case "MODE":
      $do="update log set mode='$cc[1]' where mycall='$mycall' and callsign='$callsign' and start='$start'";
      break;
    case "ST":
    case "SIGNALTX":
      $do="update log set signaltx='$cc[1]' where mycall='$mycall' and callsign='$callsign' and start='$start'";
      break;
    case "SR":
    case "SIGNALRX":
      $do="update log set signalrx='$cc[1]' where mycall='$mycall' and callsign='$callsign' and start='$start'";
      break;
    case "C":
    case "CALL":
      $do="update log set signalrx='$cc[1]' where mycall='$mycall' and callsign='$callsign' and start='$start'";
      break;
    case "DT":
    case "DATETIME":
      $do="update log set start='$cc[1]' where mycall='$mycall' and callsign='$callsign' and start='$start'";
      break;   
  }
  printf("%s\n",$do);
  mysqli_query($con,$do);
  mysqli_query($con,"delete from booking where mycall='$mycall' and myupdate='$myupdate'");
}
mysqli_free_result($query);

echo "</pre>";

?>
