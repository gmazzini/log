<?php
// also triggered by ENTER in Call input
echo "<pre>";

if(is_numeric($Icallsign)){
  $setfreq=(float)$Icallsign*1000;
  echo "Freq set to $setfreq\n";
  $fp=@fsockopen($rigIP,$rigPORT);
  if($fp){
    stream_set_timeout($fp,0,200000);
    fwrite($fp,"F $setfreq\n");
    fclose($fp);
  }
}
elseif($Icallsign=="USB"||$Icallsign=="LSB"||$Icallsign=="CW"){
  $fp=@fsockopen($rigIP,$rigPORT);
  if($fp){
    stream_set_timeout($fp,0,200000);
    fwrite($fp,"M $Icallsign 0\n");
    fclose($fp);
  }
}
else {
  $qsostart=gmdate('Y-m-d H:i:s');
  $query=mysqli_query($con,"select firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born from who where callsign='$Icallsign'");
  $row=mysqli_fetch_array($query);
  $ff=0;
  if($row!=null&&strlen($row[0])==0){$ff=1; mysqli_query($con,"delete from who where callsign='$Icallsign'");}
  if($row==null||$ff){
    $qrzkey=myrcl($con,"qrzkey");    
    $q1=file_get_contents("http://xmldata.qrz.com/xml/current/?s=$qrzkey;callsign=$Icallsign");
    $q2=simplexml_load_string($q1);
    if(isset($q2->Session->Error)){
      $q1=file_get_contents("http://xmldata.qrz.com/xml/current/?username=$qrzuser;password=$qrzpassword;agent=gm01");
      $q2=simplexml_load_string($q1);
      $qrzkey=$q2->Session->Key;
      mysto($con,"qrzkey","$qrzkey\n");
      $q1=file_get_contents("http://xmldata.qrz.com/xml/current/?s=$qrzkey;callsign=$Icallsign");
      $q2=simplexml_load_string($q1);
    }
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
  printf("%s %s\n%s\n%s\n%s %s %s\n%s\n%s\n%s %s %s\n",$row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6],$row[7],$row[8],$row[9],$row[10],$row[11]);
  echo "\n";

  $query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,lotw,eqsl,qrz,contesttx,contestrx,contest from log where callsign='$Icallsign' and mycall='$mycall' order by start desc limit 5");
  for(;;){
    $row=mysqli_fetch_array($query);
    if($row==null)break;
    $aux="";
    if((int)$row[6]==1)$aux.="L";
    if((int)$row[7]==1)$aux.="E";
    if((int)$row[8]==1)$aux.="Q";
    printf("%s %12s %7.1f %4s %5s %5s %-3s ",$row[0],$row[1],$row[2]/1000,$row[3],$row[4],$row[5],$aux);
    if(strlen($row[11])>0)printf("(%s,%s,%s)",$row[11],$row[9],$row[10]);
    printf("\n");
  }
  mysqli_free_result($query);
  echo "\n";

  echo myqso($con,$mycall,$Icallsign);
  echo "\n";

  $mys=searchcty($con,$Icallsign);
  print_r($mys);
}

echo "</pre>";

?>
