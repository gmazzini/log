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

for($sss=0;;$sss++){
  $query1=mysqli_query($con,"select min(looked) from qrzwebcontact where mycall='$mycall'");
  $row1=mysqli_fetch_row($query1);
  $minlooked=(int)$row1[0];
  mysqli_free_result($query1);
  
  $query=mysqli_query($con,"select callsign from qrzwebcontact where mycall='$mycall' and looked=$minlooked order by rand()");
  $myprocess=0;
  $totprocess=0;
  for(;;){
    $myprocess++;
    if($myprocess>$process)break;
    $row=mysqli_fetch_assoc($query);
    if($row==null)break;
    $callsign=$row["callsign"];
    $tt=(int)(time()/86400);
    echo "$myprocess:$totprocess:$tt:$sss looking: $callsign\n";
    mysqli_query($con,"update qrzwebcontact set looked=$tt where mycall='$mycall' and callsign='$callsign'");
    $out=myqrzwebcontact($callsign,$Ewc,$visited);
    sleep(rand(3,7));
    echo "... visited=$visited\n";
    mysqli_query($con,"update qrzwebcontact set visited=$visited where mycall='$mycall' and callsign='$callsign'");
    if($out==null)continue;
    echo "... Ewc=$Ewc,Nwc=".count($out)."\n";
    mysqli_query($con,"update qrzwebcontact set Ewc=$Ewc,Nwc=".count($out)." where mycall='$mycall' and callsign='$callsign'");
    $xx=0;
    foreach($out as $v){
      if($v==$mycall)mysqli_query($con,"update qrzwebcontact set me=1 where mycall='$mycall' and callsign='$callsign'");
      $query1=mysqli_query($con,"select count(*) from qrzwebcontact where mycall='$mycall' and callsign='$v'");
      $row1=mysqli_fetch_row($query1);
      $aux=(int)$row1[0];
      mysqli_free_result($query1);
      if($aux==0){
        echo "$myprocess:$xx:$totprocess insert into qrzwebcontact (mycall,callsign,source) value ('$mycall','$v','oth')\n";
        mysqli_query($con,"insert into qrzwebcontact (mycall,callsign,source) value ('$mycall','$v','oth')");
        $xx++;
        $totprocess++;
      }
    }
  }
  mysqli_free_result($query);
  sleep(120);
}

mysqli_close($con);
?>
