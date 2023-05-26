<?php
include "local.php";
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
$mycall="IK4LZH";
$query=mysqli_query($con,"select act from booking where mycall='IK4LZH'");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $act=$row["act"];
  $cc=explode("|",$act);
  switch($cc[0]){
    case "D":
      $start=$cc[1];
      $callsign=$cc[2];
      echo "delete from log where mycall='$mycall' and start='$start' and callsign='$callsign'";
      break;
  }
}
mysqli_free_result($query);
mysqli_close($con);

?>
