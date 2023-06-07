<?php
include "def_list.php";

$query=mysqli_query($con,"select max(serial) from log where mycall='$mycall'");
$row=mysqli_fetch_row($query);
$lastserial=(int)$row[0];
mysqli_free_result($query);
$query=mysqli_query($con,"select callsign,start from log where mycall='$mycall' and serial=0 order by start");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  $start=$row["start"];
  $lastserial++;
  mysqli_query($con,"update log set serial=$lastserial where mycall='$mycall' and callsign='$callsign' and start='$start'");
}
mysqli_free_result($query);
if($page<0){
  $auxstart=strval(-$page);
  $auxstart=sprintf("%s-%s-%s 00:00:00",substr($auxstart,0,4),substr($auxstart,4,2),substr($auxstart,6,2));
  $query=mysqli_query($con,"select serial from log where mycall='$mycall' and start>='$auxstart' order by start limit 1");
  $row=mysqli_fetch_assoc($query);
  $baseserial=(int)$row["serial"];
  mysqli_free_result($query);
  $page=$lastserial-$baseserial;
}
else $baseserial=$lastserial-$page;

mylist($con,"where mycall='$mycall' and serial<=$baseserial order by serial desc limit $mypage",$mycall,$md5passwd);

?>
