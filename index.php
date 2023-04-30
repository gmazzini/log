<?php

function myextract($buf,$token){
  $pos=stripos($buf,"<".$token.":");
  if($pos===false)return null;
  $pose=stripos($buf,">",$pos);
  $ltok=strlen($token)+2;
  $ll=(int)substr($buf,$pos+$ltok,$pose-$pos-$ltok);
  return substr($buf,$pose+1,$ll);
}

$act=(int)$_POST['act'];
$con=mysqli_connect("127.0.0.1","log","log345log","log");
mysqli_query($con,"SET time_zone='+00:00'");
$mypage=30;

if($act>=1){
  $mycall=strtoupper($_POST['mycall']);
  if($act==1)$md5passwd=md5($_POST['mypasswd']);
  else $md5passwd=$_POST['md5passwd'];
  $query=mysqli_query($con,"select mygrid from user where mycall='$mycall' and md5passwd='$md5passwd'");
  $row=mysqli_fetch_array($query);
  $mygrid=strtoupper($row[0]);
  mysqli_free_result($query);
  if(strlen($mygrid)!=6)$act=0;
}

if($act==0){
  echo "<form method=\"post\">";
  echo "<input type=\"text\" name=\"mycall\">";
  echo "<input type=\"text\" name=\"mypasswd\">";
  echo "<input type=\"hidden\" name=\"act\" value=\"1\">";
  echo "<input type=\"submit\">";
  echo "</form>";
}
else {
  echo "<form method=\"post\" enctype=\"multipart/form-data\">";
  echo "<input type=\"hidden\" name=\"mycall\" value=\"$mycall\">";
  echo "<input type=\"hidden\" name=\"md5passwd\" value=\"$md5passwd\">";
  echo "<input type=\"hidden\" name=\"act\" value=\"2\">";
  echo "<input type=\"submit\" name=\"run\" value=\"list\">";
  echo "<input type=\"submit\" name=\"run\" value=\"list up\">";
  echo "<input type=\"submit\" name=\"run\" value=\"list dw\">";
  echo "<input type=\"submit\" name=\"run\" value=\"find\">";
  echo "<input type=\"submit\" name=\"run\" value=\"find up\">";
  echo "<input type=\"submit\" name=\"run\" value=\"find dw\">";
  echo "<br>";

  echo "<input type=\"submit\" name=\"run\" value=\"import\">";
  echo "<input type=\"file\" name=\"myfile\">";
  echo "<br>";

  $Icallsign=strtoupper($_POST['Icallsign']);
  $Ifreqtx=$_POST['Ifreqtx'];
  $Imode=strtoupper($_POST['Imode']);
  $Isignaltx=$_POST['Isignaltx'];
  $Isignalrx=$_POST['Isignalrx'];
  echo "<input type=\"text\" name=\"Icallsign\" value=\"$Icallsign\">";
  echo "<input type=\"text\" name=\"Ifreqtx\" value=\"$Ifreqtx\">";
  echo "<input type=\"text\" name=\"Imode\" value=\"$Imode\">";
  echo "<input type=\"text\" name=\"Isignaltx\" value=\"$Isignaltx\">";
  echo "<input type=\"text\" name=\"Isignalrx\" value=\"$Isignalrx\">";
  echo "<br>";
  
  echo "<input type=\"submit\" name=\"run\" value=\"start\">";
  echo "<input type=\"submit\" name=\"run\" value=\"end\">";
  echo "<br>";

  $run=$_POST['run'];
  $page=(int)$_POST['page'];
  $qsostart=$_POST['qsostart'];
  switch($run){
    case "list": 
      $page=0; 
      break;
    case "list up": 
      $run="list"; 
      $page+=$mypage; 
      break;
    case "list dw": 
      $run="list"; 
      $page-=$mypage; 
      break;
    case "find": 
      $page=0; 
      break;
    case "find up": 
      $run="find";
      $page+=$mypage;
      break;
    case "find dw":
      $run="find";
      $page-=$mypage;
      break;
  }
  echo "<h1>$mycall $mygrid $page $qsostart</h1>";
  switch($run){
    case "end":
      $qsoend=gmdate('Y-m-d H:i:s');
      mysqli_query($con,"insert into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx) value ('$mycall','$Icallsign','$qsostart','$qsoend','$Imode',$Ifreqtx,$Ifreqtx,'$Isignaltx','$Isignalrx')");
      break;
      
    case "start":
      echo "ciao\n";
      $qsostart=gmdate('Y-m-d H:i:s');
      break; 
    
    
    
    case "find":
    echo "<pre>";
    $query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx from log where callsign like '$Icallsign' and mycall='$mycall' order by start desc limit $mypage offset $page");
    for(;;){
      $row=mysqli_fetch_array($query);
      if($row==null)break;
      printf("%s %10s %7.1f %4s %4s %4s\n",$row[0],$row[1],$row[2]/1000,$row[3],$row[4],$row[5]);
    }
    echo "</pre>";
    mysqli_free_result($query);
    break;

    case "list";
    echo "<pre>";
    $query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx from log where mycall='$mycall' order by start desc limit $mypage offset $page");
    for(;;){
      $row=mysqli_fetch_array($query);
      if($row==null)break;
      printf("%s %10s %7.1f %4s %4s %4s\n",$row[0],$row[1],$row[2]/1000,$row[3],$row[4],$row[5]);
    }
    echo "</pre>";
    mysqli_free_result($query);
    break;

    case "import";
    if(isset($_FILES['myfile']['tmp_name']))$hh=fopen($_FILES['myfile']['tmp_name'],"r");
    $aux="";
    while(!feof($hh)){
      $line=trim(fgets($hh));
      $pp=stripos($line,"<eor>");
      if($pp===false)$aux.=$line;
      else {
        $aux.=substr($line,0,$pp);
        $callsign=myextract($aux,"call");
        $freqtx=myextract($aux,"freq")*1000000;
        $freqrx=myextract($aux,"freq_rx")*1000000;
        $signaltx=myextract($aux,"rst_sent");
        $signalrx=myextract($aux,"rst_rcvd");
        $mode=myextract($aux,"mode");
        $timeon=myextract($aux,"time_on");
        if(strlen($timeon)==4)$timeon.="00";
        $timeoff=myextract($aux,"time_off");
        if(strlen($timeoff)==4)$timeoff.="00"; 
        $dateon=myextract($aux,"qso_date");
        $dateoff=myextract($aux,"qso_date_off");
        $start=substr($dateon,0,4)."-".substr($dateon,4,2)."-".substr($dateon,6,2)." ".substr($timeon,0,2).":".substr($timeon,2,2).":".substr($timeon,4,2);
        $end=substr($dateoff,0,4)."-".substr($dateoff,4,2)."-".substr($dateoff,6,2)." ".substr($timeoff,0,2).":".substr($timeoff,2,2).":".substr($timeoff,4,2);
        mysqli_query($con,"insert into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx) value ('$mycall','$callsign','$start','$end','$mode',$freqtx,$freqrx,'$signaltx','$signalrx')");
        $aux=substr($line,$pp+5);
      }
    }
    fclose($hh);
    break;

  }
  echo "<input type=\"hidden\" name=\"qsostart\" value=\"$qsostart\">";
  echo "<input type=\"hidden\" name=\"page\" value=\"$page\">";
  echo "</form>";
}


// mysqli_query($con,"update redirect set hit=hit+1 where origin='$org'");




mysqli_close($con);

?>
