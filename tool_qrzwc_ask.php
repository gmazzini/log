<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
include "def_qrzwc.php";
$mycall="IK4LZH";
$myshow=0;
$process=1000;

$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
$co=json_decode(file_get_contents("/home/www/data/qrz_cookie"),true);

$query=mysqli_query($con,"select callsign,Nwc from qrzwebcontact where mycall='$mycall' and sent=0 and source='oth' and me=0 and you=0 and Ewc=1 order by Nwc desc");
$myprocess=0;
for(;;){
//  sleep(rand(10,20));
  $myprocess++;
  if($myprocess>$process)break;
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  $Nwc=$row["Nwc"];
  echo "$myprocess $callsign $Nwc\n";

  
}
mysqli_free_result($query);
mysqli_close($con);

?>
