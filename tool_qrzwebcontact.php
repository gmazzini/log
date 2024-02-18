<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
$mycall="IK4LZH";
$myshow=0;
$process=10;

$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query=mysqli_query($con,"select distinct callsign from log where mycall='$mycall' and callsign not in (select callsign from qrzwebcontact where mycall='$mycall') order by callsign");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  mysqli_query($con,"insert into qrzwebcontact (mycall,callsign,sent,source) value ('$mycall','$callsign',0,'me')");
}
mysqli_free_result($query);

$out=myqrzwebcontact($mycall);
foreach($out as $v){
  mysqli_query($con,"insert ignore into qrzwebcontact (mycall,callsign,sent,source) value ('$mycall','$v',1,'web')");
}

$query=mysqli_query($con,"select callsign from qrzwebcontact where mycall='$mycall' and sent=0 and source='me' order by rand() limit $process");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  $aux=qrz($con,$callsign);
  if($aux==1){
    $query1=mysqli_query($con,"select email from who where callsign='$callsign'");
    $row1=mysqli_fetch_assoc($query1);
    $email=$row1["email"];
    mysqli_free_result($query1);
    if(strlen($email)>5){
      echo "$callsign $email \n";
    }
  }  
}
mysqli_free_result($query);

mysqli_close($con);

function myqrzwebcontact($call){
  $agent="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.75 Safari/537.36";
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,"https://www.qrz.com/lookup/$call");
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
  curl_setopt($ch,CURLOPT_HTTPHEADER,Array("User-Agent: $agent"));
  $out=curl_exec($ch);
  curl_close($ch);
  $tok='var wc_summary = "';
  $l1=strpos($out,$tok,0);
  if($l1===false)return null;
  $l1+=strlen($tok);
  $l2=strpos($out,'"',$l1);
  $myurl=substr($out,$l1,$l2-$l1);

  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,$myurl);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
  curl_setopt($ch,CURLOPT_HTTPHEADER,Array("User-Agent: $agent"));
  $out=curl_exec($ch);
  curl_close($ch);
  $tok='href="https://www.qrz.com/db/';
  $l2=0;
  $tot=0;
  for(;;){
    $l1=strpos($out,$tok,$l2);
    if($l1===false)break;
    $l1+=strlen($tok);
    $l2=strpos($out,'/',$l1);
    if($l2-$l1>0)$dd[$tot++]=substr($out,$l1,$l2-$l1);
  }
  return $dd;
}

?>
