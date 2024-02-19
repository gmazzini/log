<?php
include "local.php";
include "utility.php";
include "def_qrz.php";
include "def_qrzwc.php";
$mycall="IK4LZH";
$myshow=0;
$process=100;

$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
$query1=mysqli_query($con,"select email from who where callsign='$mycall'");
$row1=mysqli_fetch_assoc($query1);
@$myemail=$row1["email"];
mysqli_free_result($query1);

$i=0;
$query=mysqli_query($con,"select callsign from qrzwebcontact where mycall='$mycall' and sent=0 and source='me' order by rand()");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $callsign=$row["callsign"];
  
  $out=myqrzwebcontact($callsign);
  if($out!=null){
    foreach($out as $v){
      if($v==$mycall){
        mysqli_query($con,"update qrzwebcontact set sent=1 where mycall='$mycall' and callsign='$callsign'");
        break;
      }
    }
    if($v==$mycall)continue;
  }
  
  qrz($con,$callsign);
  sleep(5);
  $query1=mysqli_query($con,"select email from who where callsign='$callsign'");
  $row1=mysqli_fetch_assoc($query1);
  @$email=$row1["email"];
  mysqli_free_result($query1);
  if(strlen($email)>5){
    echo "$i $callsign $email \n";
    $msg='Hi '.$callsign.',<br><br> in the past, we have connected
    and indeed, you are in my log. I noticed that you also have
    a profile on qrz.com, and I do too. It would really make me
    happy if you could add your callsign to my qrz.com page called
    "Web Contacts," where I am collecting a large number of
    friends. If you decide to proceed, you can: <ul> 
    <li>1. log in to the qrz.com website <a href="https://www.qrz.com/">
    https://www.qrz.com/</a> with your credentials,</li>
    <li>2. search for my callsign by typing $mycall or by clicking 
    the link <a href="https://www.qrz.com/lookup/'.$mycall.'">
    https://www.qrz.com/lookup/'.$mycall.'</a></li>
    <li>3. click on the tab labeled "Web",</li>
    <li>4. go to the box labeled "Add your Web Contact", 
    and click on the button that says "DE '.$callsign.'"</li></ul><br><br>
    Thank you very much, and I hope to connect with you again 
    soon.<br><br> 73 de '.$mycall;
    myemailsend($mycall.'<'.$myemail.'>',$email,'QRZ Web Contacts request',$msg);
    mysqli_query($con,"update qrzwebcontact set sent=1 where mycall='$mycall' and callsign='$callsign'");
    $i++;
    if($i==$process)break;
  }  
}
mysqli_free_result($query);
mysqli_close($con);

function myemailsend($from,$to,$subject,$html){
  global $mailgun_secret;
  $post=array('to' => $to,'from' => $from,'subject' => $subject,'html' => $html);
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,"https://api.eu.mailgun.net/v3/mg.mazzini.org/messages");
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
  curl_setopt($ch,CURLOPT_HTTPHEADER,Array("Authorization: Basic ".base64_encode($mailgun_secret)));
  curl_setopt($ch,CURLOPT_POST,1);
  curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
  echo curl_exec($ch);
  curl_close($ch);
}

?>
