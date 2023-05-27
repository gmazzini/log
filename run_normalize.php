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

$query=mysqli_query($con,"select command,myupdate from booking where mycall='$mycall' order by myupdate");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $command=$row["command"];
  $myupdate=$row["myupdate"];
  $cc=explode(",",$command);
  $para=$cc[1];
  switch($cc[0]){
    case "DEL":
    case "DELETE":
      $do="delete from log where mycall='$mycall' and ";
      break;
  }
}
mysqli_free_result($query);

echo "</pre>";

?>
