<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
include "def_qrzwc.php";
$mycall="IK4LZH";
$myshow=0;
$process=300;

$con=mysqli_connect($dbhost,$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
$query1=mysqli_query($con,"select email from who where callsign='$mycall'");
$row1=mysqli_fetch_assoc($query1);
@$myemail=$row1["email"];
mysqli_free_result($query1);
$co=json_decode(file_get_contents("/home/www/data/qrz_cookie"),true);

$query=mysqli_query($con,"select callsign,Nwc from qrzwebcontact where mycall='$mycall' and sent=0 and qrzed=0 and source='oth' and me=0 and you=0 and Ewc=1 order by Nwc desc");
$myprocess=0;
for(;;){
  sleep(rand(2,6));
  $myprocess++;
  if($myprocess>$process)break;
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  $Nwc=$row["Nwc"];
  echo "$myprocess $callsign $Nwc\n";

  if(!myqrzsetwebcontact($callsign))continue;
  $tt=(int)(time()/86400);
  mysqli_query($con,"update qrzwebcontact set me=1,qrzed=$tt where mycall='$mycall' and callsign='$callsign'");
  sleep(rand(2,6));
  qrz($con,$callsign);
  $query1=mysqli_query($con,"select email from who where callsign='$callsign'");
  $row1=mysqli_fetch_assoc($query1);
  @$email=$row1["email"];
  mysqli_free_result($query1);
  $query1=mysqli_query($con,"select count(email) from qrzwebcontact_email where email='$email'");
  $row1=mysqli_fetch_row($query1);
  @$justsent=$row1[0];
  mysqli_free_result($query1);
  echo "... ask $callsign $email $justsent\n";
  if(strlen($email)>5 && $justsent==0){
    echo "... sending\n";
    $msg='Hi '.$callsign.',<br><br> I noticed that in your profile 
    on qrz.com you have enabled "Web Contacts" and have 
    collected '.$Nwc.' entries, when I visited you. I have also added my 
    callsign to your list with great pleasure. It would really make 
    me happy if you could also add your callsign to my qrz.com page 
    "Web Contacts," where I am collecting a large number of friends. 
    If you decide to proceed, you can: <ul>
    <li>1. log in to the qrz.com website <a href="https://www.qrz.com/">
    https://www.qrz.com/</a> with your credentials,</li>
    <li>2. search for my callsign by typing '.$mycall.' or by clicking 
    the link <a href="https://www.qrz.com/lookup/'.$mycall.'">
    https://www.qrz.com/lookup/'.$mycall.'</a></li>
    <li>3. click on the tab labeled "Web",</li>
    <li>4. go to the box labeled "Add your Web Contact", 
    and click on the button that says "DE '.$callsign.'"</li></ul><br><br>
    Thank you very much, and I hope to connect with you again 
    soon.<br><br> 73 de '.$mycall;
    myemailsend($mycall.'<'.$myemail.'>',$email,'QRZ Web Contacts',$msg);
    mysqli_query($con,"update qrzwebcontact set sent=1 where mycall='$mycall' and callsign='$callsign'");
    mysqli_query($con,"insert ignore into qrzwebcontact_email (email) values ('$email')");
  }  
}

mysqli_free_result($query);
mysqli_close($con);

?>
