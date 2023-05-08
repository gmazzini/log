<title>LZH Logger V0.17 by IK4LZH</title>
<style><?php include "style.css"; ?></style>
<?php
include "local.php";
include "utility.php";
include "bandplane.php";

$act=(int)$_POST['act'];
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
$mypage=30;
if($act>=1){
  $mycall=strtoupper($_POST['mycall']);
  if($act==1)$md5passwd=md5($_POST['mypasswd']);
  else $md5passwd=$_POST['md5passwd'];
  $query=mysqli_query($con,"select mygrid,cluster,rigconnect from user where mycall='$mycall' and md5passwd='$md5passwd'");
  $row=mysqli_fetch_array($query);
  if($row!=null){
    $mygrid=strtoupper($row[0]);
    $aux=explode(":",$row[2]);
    $rigIP=$aux[0];
    $rigPORT=$aux[1];
    $aux=explode(",",$row[1]);
    if($act==1)foreach($aux as &$vv)$dxcsel[$vv]=1;
  }
  else $act=0;
  mysqli_free_result($query);
}
?>

<script>
  function updategeneral() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        aaa=this.responseText.split("\n");
        document.getElementById("frig").textContent=aaa[0];
        document.getElementById("Pfrig").value=aaa[0];
        document.getElementById("srig").textContent=aaa[1];
        document.getElementById("Psrig").value=aaa[1];
        document.getElementById("mrig").textContent=aaa[2];
        document.getElementById("Pmrig").value=aaa[2];
      }
    }
    xhttp.open("GET","rig.php?rigIP=$rigIP&rifPORT=$rigPORT",true);
    xhttp.send();
  }
  function nextgeneral(){
    updategeneral();
    setTimeout(nextgeneral,1000);
  }  
  updategeneral();
  setTimeout(nextgeneral,1000);
</script>

<?php
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
  echo "<input type=\"submit\" name=\"run\" value=\"list\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"list up\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"list dw\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"find\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"find up\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"find dw\">&nbsp;";
  echo "<br>";

  echo "<input type=\"submit\" name=\"run\" value=\"importadi\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"importlzh\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"exportadi\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"exportcbr\">&nbsp;";
  echo "<input type=\"file\" name=\"myfile\">&nbsp;";
  echo "<br>";

  $Icallsign=strtoupper($_POST['Icallsign']);
  $Ifreqtx=$_POST['Ifreqtx'];
  $Imode=strtoupper($_POST['Imode']);
  $Isignaltx=$_POST['Isignaltx'];
  $Isignalrx=$_POST['Isignalrx'];
  $Icontest=$_POST['Icontest'];
  $Icontesttx=$_POST['Icontesttx'];
  $Icontestrx=$_POST['Icontestrx'];
  
  $run=$_POST['run'];
  $page=(int)$_POST['page'];
  $qsostart=$_POST['qsostart'];
  $runcontest=(int)$_POST['runcontest'];
  $riglink=(int)$_POST['riglink'];
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
    case "contest":
      $runcontest=1;
      break;
    case "contest off":
      $runcontest=0;
      break;
    case "riglink":
      $riglink=1;
      break;
    case "riglink off":
      $riglink=0;
      break;
  }
  
  echo "<label>Call</label>";
  echo "<input type=\"text\" name=\"Icallsign\" value=\"$Icallsign\" maxlength=\"20\" size=\"10\">&nbsp;";
  if(!$riglink){
    echo "<label>Freq</label>";
    echo "<input type=\"text\" name=\"Ifreqtx\" value=\"$Ifreqtx\" maxlength=\"10\" size=\"10\">&nbsp;";
    echo "<label>Mode</label>";
    echo "<input type=\"text\" name=\"Imode\" value=\"$Imode\" maxlength=\"8\" size=\"4\">&nbsp;";
  }
  else {
    echo "<input type=\"hidden\" name=\"Ifreqtx\" value=\"$Ifreqtx\">";
    echo "<input type=\"hidden\" name=\"Imode\" value=\"$Imode\">";
  }
  echo "<label>SigTX</label>";
  echo "<input type=\"text\" name=\"Isignaltx\" value=\"$Isignaltx\" maxlength=\"8\" size=\"4\">&nbsp;";
  echo "<label>SigRX</label>";
  echo "<input type=\"text\" name=\"Isignalrx\" value=\"$Isignalrx\" maxlength=\"8\" size=\"4\">&nbsp;";
  if($riglink)echo "<input type=\"submit\" name=\"run\" value=\"riglink off\">&nbsp;";
  echo "<br>";
  
  if($runcontest){
    echo "<input type=\"submit\" name=\"run\" value=\"contest off\">&nbsp;";
    echo "<label>contestTX</label>";
    echo "<input type=\"text\" name=\"Icontesttx\" value=\"$Icontesttx\" maxlength=\"6\" size=\"6\">&nbsp;";
    echo "<label>ContestRX</label>";
    echo "<input type=\"text\" name=\"Icontestrx\" value=\"$Icontestrx\" maxlength=\"6\" size=\"6\">&nbsp;";
    echo "<label>contest</label>";
    echo "<input type=\"text\" name=\"Icontest\" value=\"$Icontest\" maxlength=\"12\" size=\"12\">&nbsp;";
    echo "<br>"; 
  }
  else {
    echo "<input type=\"submit\" name=\"run\" value=\"contest\">&nbsp;";
    echo "<input type=\"hidden\" name=\"Icontesttx\" value=\"$Icontesttx\">";
    echo "<input type=\"hidden\" name=\"Icontestrx\" value=\"$Icontestrx\">";
    echo "<input type=\"hidden\" name=\"Icontest\" value=\"$Icontest\">";
  }
  
  echo "<input type=\"submit\" name=\"run\" value=\"start\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"end\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"cluster\">&nbsp;";
  
  foreach($_POST['dxcsel'] as &$vv)$dxcsel[$vv]=1;
  foreach(array("160","80","60","40","30","20","17","15","12","10","PH","CW","DG") as &$vv){
    echo "<input type=\"checkbox\" name=\"dxcsel[]\" value=\"$vv\"";
    if($dxcsel[$vv])echo " checked";
    echo ">";
    echo "<label>$vv</label>&nbsp;";
  }  
  echo "<br>";
  
  echo "<h2>Frig: <span id=\"frig\"></span>&nbsp;";
  echo "Srig: <span id=\"srig\"></span>&nbsp;";
  echo "Mrig: <span id=\"mrig\"></span>&nbsp;";
  if(!$riglink)echo "<input type=\"submit\" name=\"run\" value=\"riglink\">&nbsp;";
  echo "<br></h2>";  
  echo "<h1>$mycall $mygrid $page</h1>";
  switch($run){
    case "cluster":
      echo "<pre>";
      $myrow=0;
      $query=mysqli_query($con,"select dx,spotter,freq,timespot from dxc order by timespot desc limit 1000");
      for(;;){
        $row=mysqli_fetch_array($query);
        if($row==null)break;
        $freq=$row[2]/1000;
        for($i=0;;$i++){
          if(!isset($bpfreq[$i]))break;
          if($freq>=$bpfreq[$i][1]&&$freq<$bpfreq[$i][2])break;
        }
        if(isset($bpfreq[$i])&&$dxcsel[$bpfreq[$i][0]]&&$dxcsel[$bpfreq[$i][3]]){
          printf("%s %10s %7.1f %10s %s\n",$row[3],$row[0],$row[2]/1000,$row[1],myqso($con,$mycall,$row[0]));
          $myrow++;
          if($myrow>$mypage)break;
        }
      }
      echo "</pre>";
      mysqli_free_result($query);
      break;
      
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
      fprintf($fp,"EMAIL: xxxxxx\n");
      fprintf($fp,"ADDRESS: xxxxxx\n");
      fprintf($fp,"ADDRESS-CITY: xxxxx\n");
      fprintf($fp,"ADDRESS-POSTALCODE: xxxxxx\n");
      fprintf($fp,"ADDRESS-COUNTRY: xxxxxx\n");
      fprintf($fp,"OPERATORS: $mycall\n");
      fprintf($fp,"CLUB: xxxxxx\n");
      $aux=file_get_contents($_FILES['myfile']['tmp_name']);
      $export_from=myextract($aux,"export_from");
      $export_to=myextract($aux,"export_to");
      $query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx,contesttx,contestrx from log where mycall='$mycall' and start>='$export_from' and start<='$export_to' order by start");
      for(;;){
        $row=mysqli_fetch_array($query);
        if($row==null)break;
        fprintf($fp,"QSO: %5d %2s %04d-%02d-%02d ",$row[2]/1000,$mymode[$row[3]],substr($row[0],0,4),substr($row[0],5,2),substr($row[0],8,2));
        fprintf($fp,"%02d%02d %-13s %3s %-6s %-13s %3s %-6s 0\n",substr($row[0],11,2),substr($row[0],14,2),$mycall,$row[4],$row[8],$row[1],$row[5],$row[9]);
      }
      fprintf($fp,"END-OF-LOG:\n");
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
      $query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx,contesttx,contestrx,contest from log where mycall='$mycall' and start>='$export_from' and start<='$export_to' order by start");
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
        fprintf($fp,"%s\n",myinsert($row[8],"STX_STRING"));
        fprintf($fp,"%s\n",myinsert($row[9],"SRX_STRING"));
        fprintf($fp,"%s\n",myinsert($row[10],"CONTEST_ID"));
        fprintf($fp,"<EOR>\n\n");
      }
      fclose($fp);
      echo "<pre><a href='https://log.chaos.cc/files/$name' download>Download ADIF</a><br>";
      echo "$export_from $export_to\n";
      break;
      
    case "end":
      $qsoend=gmdate('Y-m-d H:i:s');
      if($riglink){
        $ftx=$_POST['Pfrig']*1000;
        $fmode=$_POST['Pmrig'];
      }
      else {
        $ftx=$Ifreqtx*1000;
        $fmode=$Imode;
      }
      
      
      
      $frx=$ftx;
      
      
      
      if($runcontest){
        $Acontesttx=$Icontesttx;
        $Acontestrx=$Icontestrx;
        $Acontest=$Icontest;
      }
      else {
        $Acontesttx="";
        $Acontestrx="";
        $Acontest="";
      }
      mysqli_query($con,"insert into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx,contesttx,contestrx,contest) value ('$mycall','$Icallsign','$qsostart','$qsoend','$fmode',$ftx,$frx,'$Isignaltx','$Isignalrx','$Acontesttx','$Acontestrx','$Acontest')");
      break;
      
    case "start":
      $qsostart=gmdate('Y-m-d H:i:s');
      $query=mysqli_query($con,"select firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born from who where callsign='$Icallsign'");
      $row=mysqli_fetch_array($query);
      $ff=0;
      if($row!=null&&strlen($row[0])==0){$ff=1; mysqli_query($con,"delete from who where callsign='$Icallsign'");}
      if($row==null||$ff){
        $q1=file_get_contents("http://xmldata.qrz.com/xml/current/?s=$qrzs;callsign=$Icallsign");
        $q2=simplexml_load_string($q1);
        $row[0]=mysqli_real_escape_string($con,$q2->Callsign->fname);
        if(strlen($row[0])>0){
          if(isset($q2->Callsign->nickname))$row[0].=' "'.mysqli_real_escape_string($con,$q2->Callsign->nickname).'"';
          $row[1]=mysqli_real_escape_string($con,$q2->Callsign->name);
          $row[2]=mysqli_real_escape_string($con,$q2->Callsign->addr1);
          $row[3]=mysqli_real_escape_string($con,$q2->Callsign->addr2);
          $row[4]=mysqli_real_escape_string($con,$q2->Callsign->state);
          $row[5]=mysqli_real_escape_string($con,$q2->Callsign->zip);
          $row[6]=mysqli_real_escape_string($con,$q2->Callsign->country);
          $row[7]=mysqli_real_escape_string($con,$q2->Callsign->grid);
          $row[8]=mysqli_real_escape_string($con,$q2->Callsign->email);
          $row[9]=(int)$q2->Callsign->cqzone;
          $row[10]=(int)$q2->Callsign->ituzone;
          $row[11]=(int)$q2->Callsign->born;
          $mynow=gmdate('Y-m-d H:i:s');
          mysqli_query($con,"insert into who (callsign,firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,myupdate) value ('$Icallsign','$row[0]','$row[1]','$row[2]','$row[3]','$row[4]','$row[5]','$row[6]','$row[7]','$row[8]',$row[9],$row[10],$row[11],'$mynow')");
        }
      }
      mysqli_free_result($query);
      $query=mysqli_query($con,"select firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born from who where callsign='$Icallsign'");
      $row=mysqli_fetch_array($query);
      mysqli_free_result($query);
      echo "<pre>";
      printf("%s %s\n%s\n%s\n%s %s %s\n%s\n%s\n%s %s %s\n",$row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6],$row[7],$row[8],$row[9],$row[10],$row[11]);
      echo "\n";
      echo myqso($con,$mycall,$Icallsign);
      echo "\n";
      $mys=findcall($Icallsign);
      print_r($mys);
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
      echo "<pre>";
      while(!feof($hh)){
        $line=strtoupper(trim(fgets($hh)));
        if(substr($line,0,1)=="D"){$dateon=substr($line,1); continue;}
        if(substr($line,0,1)=="F"){$freqtx=substr($line,1)*1000; continue;}
        if(substr($line,0,1)=="M"){$mode=substr($line,1); continue;}
        $aux=explode(" ",$line);
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
        mysqli_query($con,"insert into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx,contesttx,contestrx,contest) value ('$mycall','$callsign','$start','$end','$mode',$freqtx,$freqrx,'$signaltx','$signalrx','','','')");
        echo "('$mycall','$callsign','$start','$end','$mode',$freqtx,$freqrx,'$signaltx','$signalrx','','','')\n";
      }
      echo "</pre>";
      fclose($hh);
      break;  
      
    case "importadi";
      if(!isset($_FILES['myfile']['tmp_name']))break;
      $hh=fopen($_FILES['myfile']['tmp_name'],"r");
      $aux="";
      echo "<pre>";
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
          if(strlen($timeoff)==0)$timeoff=$timeon;
          if(strlen($timeoff)==4)$timeoff.="00";
          $contesttx=myextract($aux,"stx_string");
          if(strlen($contesttx)==0)$contesttx=myextract($aux,"stx");
          $contestrx=myextract($aux,"srx_string");
          if(strlen($contestrx)==0)$contestrx=myextract($aux,"srx");
          $contest=myextract($aux,"contest_id");
          $dateon=myextract($aux,"qso_date");
          $dateoff=myextract($aux,"qso_date_off");
          if(strlen($dateoff)==0)$dateoff=$dateon;
          $start=substr($dateon,0,4)."-".substr($dateon,4,2)."-".substr($dateon,6,2)." ".substr($timeon,0,2).":".substr($timeon,2,2).":".substr($timeon,4,2);
          $end=substr($dateoff,0,4)."-".substr($dateoff,4,2)."-".substr($dateoff,6,2)." ".substr($timeoff,0,2).":".substr($timeoff,2,2).":".substr($timeoff,4,2);
          mysqli_query($con,"insert into log (mycall,callsign,start,end,mode,freqtx,freqrx,signaltx,signalrx,contesttx,contestrx,contest) value ('$mycall','$callsign','$start','$end','$mode',$freqtx,$freqrx,'$signaltx','$signalrx','$contesttx','$contestrx','$contest')");
          echo "('$mycall','$callsign','$start','$end','$mode',$freqtx,$freqrx,'$signaltx','$signalrx','$contesttx','$contestrx','$contest')\n";
          $aux=substr($line,$pp+5);
        }
      }
      echo "</pre>";
      fclose($hh);
      break;
  }
  echo "<input type=\"hidden\" name=\"qsostart\" value=\"$qsostart\">";
  echo "<input type=\"hidden\" name=\"page\" value=\"$page\">";
  echo "<input type=\"hidden\" name=\"runcontest\" value=\"$runcontest\">";
  echo "<input type=\"hidden\" name=\"riglink\" value=\"$riglink\">";
  echo "<input type=\"text\" name=\"Pfrig\" id=\"Pfrig\">";
  echo "<input type=\"text\" name=\"Psrig\" id=\"Psrig\">";
  echo "<input type=\"text\" name=\"Pmrig\" id=\"Pmrig\">";
  echo "</form>";
}

mysqli_close($con);

?>
