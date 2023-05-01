<?php
include "local.php";

function myextract($buf,$token){
  $pos=stripos($buf,"<".$token.":");
  if($pos===false)return null;
  $pose=stripos($buf,">",$pos);
  $ltok=strlen($token)+2;
  $ll=(int)substr($buf,$pos+$ltok,$pose-$pos-$ltok);
  return substr($buf,$pose+1,$ll);
}

function myinsert($buf,$token){
  return "<".$token.":".strlen($buf).">".$buf; 
}

$act=(int)$_POST['act'];
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
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

  echo "<input type=\"submit\" name=\"run\" value=\"importadi\">";
  echo "<input type=\"submit\" name=\"run\" value=\"importlzh\">";
  echo "<input type=\"submit\" name=\"run\" value=\"exportadi\">";
  echo "<input type=\"submit\" name=\"run\" value=\"exportcbr\">";
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
      if($page<0)$page=0;
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
      if($page<0)$page=0;
      break;
  }
  echo "<h1>$mycall $mygrid $page</h1>";
  switch($run){
    case "exportcbr":
      if(!isset($_FILES['myfile']['tmp_name']))break;
      $name=rand().rand().rand().rand().".cbr";
      $fp=fopen("/home/www/log.chaos.cc/files/$name","w");
      fprintf($fp,"START-OF-LOG: 3.0\n");
      fprintf($fp,"CONTEST: xxxxxx\n");
      fprintf($fp,"CALLSIGN: $mycall\n");
      fprintf($fp,"CATEGORY-OPERATOR: SINGLE-OP\n");
      fprintf($fp,"CATEGORY-ASSISTED: ASSISTED\n");
      fprintf($fp,"CATEGORY-BAND: ALL\n");
      fprintf($fp,"CATEGORY-POWER: LOW\n");
      fprintf($fp,"CATEGORY-TRANSMITTER: ONE\n");
      fprintf($fp,"CREATED-BY: IK4LZH logger\n");
      fprintf($fp,"NAME: xxxxxxx xxxxxx\n");
      fprintf($fp,"ADDRESS: xxxxxx\n");
      fprintf($fp,"ADDRESS-CITY: xxxxx\n");
      fprintf($fp,"ADDRESS-POSTALCODE: xxxxxx\n");
      fprintf($fp,"ADDRESS-COUNTRY: xxxxxx\n");
      fprintf($fp,"OPERATORS: $mycall\n");
      $aux=file_get_contents($_FILES['myfile']['tmp_name']);
      $export_from=myextract($aux,"export_from");
      $export_to=myextract($aux,"export_to");
      $query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx from log where mycall='$mycall' and start>='$export_from' and start<='$export_to' order by start");
      $mymode=array("SSB"=>"PH","CW"=>"CW","USB"=>"PH","LSB"=>"PH","FT8"=>"DG","RTTY"=>"DG","MFSK"=>"DG","FT4"=>"DG");
      for(;;){
        $row=mysqli_fetch_array($query);
        if($row==null)break;
        fprintf($fp,"QSO: %5d %2s %04d-%02d-%02d ",$row[2]/1000,$mymode[$row[3]],substr($row[0],0,4),substr($row[0],5,2),substr($row[0],8,2));
        fprintf($fp,"%02d%02d %-13s %3s %-6s %-13s %3s %-6s 0\n",substr($row[0],11,2),substr($row[0],14,2),$mycall,$row[4],"",$row[1],$row[5],"");
      }
      fclose($fp);
      echo "<pre><a href='https://log.chaos.cc/files/$name' download>Download Cabrillo</a><br>";
      echo "$export_from $export_to\n";
      break;
      
    case "exportadi":
      if(!isset($_FILES['myfile']['tmp_name']))break;
      $name=rand().rand().rand().rand().".adi";
      $fp=fopen("/home/www/log.chaos.cc/files/$name","w");
      fprintf($fp,"%s\n",myinsert("LZHlogger","PROGRAMID"));
      fprintf($fp,"<EOH>\n\n");
      $aux=file_get_contents($_FILES['myfile']['tmp_name']);
      $export_from=myextract($aux,"export_from");
      $export_to=myextract($aux,"export_to");
      $query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx from log where mycall='$mycall' and start>='$export_from' and start<='$export_to' order by start");
      for(;;){
        $row=mysqli_fetch_array($query);
        if($row==null)break;
        fprintf($fp,"%s\n",myinsert($row[1],"CALL"));
        fprintf($fp,"%s\n",myinsert(substr($row[0],0,4).substr($row[0],5,2).substr($row[0],8,2),"QSO_DATE"));
        fprintf($fp,"%s\n",myinsert(substr($row[0],11,2).substr($row[0],14,2).substr($row[0],17,2),"TIME_ON"));
        fprintf($fp,"%s\n",myinsert(substr($row[6],0,4).substr($row[6],5,2).substr($row[6],8,2),"QSO_DATE_OFF"));
        fprintf($fp,"%s\n",myinsert(substr($row[6],11,2).substr($row[6],14,2).substr($row[6],17,2),"TIME_OFF"));
        fprintf($fp,"%s\n",myinsert(sprintf("%7.5f",$row[2]/1000000),"FREQ"));
        fprintf($fp,"%s\n",myinsert(sprintf("%7.5f",$row[7]/1000000),"FREQ_RX"));
        fprintf($fp,"%s\n",myinsert($row[4],"RST_SENT"));
        fprintf($fp,"%s\n",myinsert($row[5],"RST_RCVD"));
        fprintf($fp,"%s\n",myinsert($row[3],"MODE"));                
        fprintf($fp,"<EOR>\n\n");
      }
      fclose($fp);
      echo "<pre><a href='https://log.chaos.cc/files/$name' download>Download ADIF</a><br>";
      echo "$export_from $export_to\n";
      break;
      
    case "end":
      $qsoend=gmdate('Y-m-d H:i:s');
      $ftx=$Ifreqtx*1000;
      $frx=$ftx;
      mysqli_query($con,"insert into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx) value ('$mycall','$Icallsign','$qsostart','$qsoend','$Imode',$ftx,$frx,'$Isignaltx','$Isignalrx')");
      break;
      
    case "start":
      $qsostart=gmdate('Y-m-d H:i:s');
      $query=mysqli_query($con,"select firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born from who where callsign='$Icallsign'");
      $row=mysqli_fetch_array($query);
      if($row==null){
        $q1=file_get_contents("http://xmldata.qrz.com/xml/current/?s=$qrzs;callsign=$Icallsign");
        $q2=simplexml_load_string($q1);
        $row[0]=$q2->Callsign->fname;
        if(strlen($row[0])>0){
          if(isset($q2->Callsign->nickname))$row[0].=' "'.$q2->Callsign->nickname.'"';
          $row[1]=$q2->Callsign->name;
          $row[2]=$q2->Callsign->addr1;
          $row[3]=$q2->Callsign->addr2;
          $row[4]=$q2->Callsign->state;
          $row[5]=$q2->Callsign->zip;
          $row[6]=$q2->Callsign->country;
          $row[7]=$q2->Callsign->grid;
          $row[8]=$q2->Callsign->email;
          $row[9]=(int)$q2->Callsign->cqzone;
          $row[10]=(int)$q2->Callsign->ituzone;
          $row[11]=(int)$q2->Callsign->born;
          $mynow=gmdate('Y-m-d H:i:s');
          mysqli_query($con,"insert into who (callsign,firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,myupdate) value ('$Icallsign','$row[0]','$row[1]','$row[2]','$row[3]','$row[4]','$row[5]','$row[6]','$row[7]','$row[8]',$row[9],$row[10],$row[11],'$mynow')");
        }
      }
      mysqli_free_result($query);
      echo "<pre>";
      printf("%s %s\n%s\n%s\n%s %s %s\n%s\n%s\n%s %s %s\n",$row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6],$row[7],$row[8],$row[9],$row[10],$row[11]);
      echo "</pre>";
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

    case "importlzh";
      if(!isset($_FILES['myfile']['tmp_name']))break;
      $hh=fopen($_FILES['myfile']['tmp_name'],"r");
      $dateon="";
      $freq="";
      while(!feof($hh)){
        $line=strtoupper(trim(fgets($hh)));
        if(substr($line,0,1)=="D"){$dateon=substr($line,1); continue;}
        if(substr($line,0,1)=="F"){$freqtx=substr($line,1)*1000; continue;}
        if(substr($line,0,1)=="M"){$mode=substr($line,1); continue;}
        $aux=eplode(" ",$line);
        $timeon=$aux[0]."00";
        $timeoff=$aux[0]."59";
        $callsign=$aux[1];
        $freqrx=$freqtx;
        if($row[2]==null)$signaltx="59";
        else $signaltx=$row[2];
        if($row[3]==null)$signalrx="59";
        else $signalrx=$row[3];
        $dateoff=$dateon;
        $start=substr($dateon,0,4)."-".substr($dateon,4,2)."-".substr($dateon,6,2)." ".substr($timeon,0,2).":".substr($timeon,2,2).":".substr($timeon,4,2);
        $end=substr($dateoff,0,4)."-".substr($dateoff,4,2)."-".substr($dateoff,6,2)." ".substr($timeoff,0,2).":".substr($timeoff,2,2).":".substr($timeoff,4,2);
        // mysqli_query($con,"insert into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx) value ('$mycall','$callsign','$start','$end','$mode',$freqtx,$freqrx,'$signaltx','$signalrx')");
        echo "insert into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx) value ('$mycall','$callsign','$start','$end','$mode',$freqtx,$freqrx,'$signaltx','$signalrx')\n";
      }
      fclose($hh);
      break;  
      
    case "importadi";
      if(!isset($_FILES['myfile']['tmp_name']))break;
      $hh=fopen($_FILES['myfile']['tmp_name'],"r");
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

mysqli_close($con);

?>
