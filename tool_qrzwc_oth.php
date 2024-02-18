<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
include "def_qrzwc.php";
$mycall="IK4LZH";
$myshow=0;
$process=1000;

$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query=mysqli_query($con,"select callsign from qrzwebcontact where mycall='$mycall' and looked=0 order by rand()");
$myprocess=0;
for(;;){
  $myprocess++;
  if($myprocess>$process)break;
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  echo "Looking: $callsign\n";
  mysqli_query($con,"update qrzwebcontact set looked=1 where mycall='$mycall' and callsign='$callsign'");
  $out=myqrzwebcontact($callsign);
  sleep(5);
  if($out==null)continue;
  foreach($out as $v){
    $query1=mysqli_query($con,"select count(*) from qrzwebcontact where mycall='$mycall' and callsign='$v'");
    $row1=mysqli_fetch_row($query1);
    $aux=(int)$row1[0];
    mysqli_free_result($query1);
    if($aux==0){
      echo "insert into qrzwebcontact (mycall,callsign,sent,source,looked) value ('$mycall','$v',0,'oth',0)\n";
      mysqli_query($con,"insert into qrzwebcontact (mycall,callsign,sent,source,looked) value ('$mycall','$v',0,'oth',0)");
    }
  }
}
mysqli_free_result($query);

mysqli_close($con);
?>
