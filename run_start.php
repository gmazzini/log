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
  $query=mysqli_query($con,"select firstname from who where callsign='$Icallsign'");
  $row=mysqli_fetch_assoc($query);
  mysqli_free_result($query);
  if($row==null||strlen($row["firstname"])==0){
    $qrzkey=trim(myrcl($con,"qrzkey"));    
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
    $gfname=mysqli_real_escape_string($con,$q2->Callsign->fname);
    if(strlen($gfname)>0){
      if(isset($q2->Callsign->nickname))$gfname.=' "'.mysqli_real_escape_string($con,$q2->Callsign->nickname).'"';
      $gname=mysqli_real_escape_string($con,$q2->Callsign->name);
      $gaddr1=mysqli_real_escape_string($con,$q2->Callsign->addr1);
      $gaddr2=mysqli_real_escape_string($con,$q2->Callsign->addr2);
      $gstate=mysqli_real_escape_string($con,$q2->Callsign->state);
      $gzip=mysqli_real_escape_string($con,$q2->Callsign->zip);
      $gcountry=mysqli_real_escape_string($con,$q2->Callsign->country);
      $ggrid=mysqli_real_escape_string($con,$q2->Callsign->grid);
      $gemail=mysqli_real_escape_string($con,$q2->Callsign->email);
      $gcqzone=(int)$q2->Callsign->cqzone;
      $gituzone=(int)$q2->Callsign->ituzone;
      $gborn=(int)$q2->Callsign->born;
      $gimage=mysqli_real_escape_string($con,$q2->Callsign->image);
      $mynow=gmdate('Y-m-d H:i:s');
      mysqli_query($con,"replace into who (callsign,firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,image,myupdate) value ('$Icallsign','$gfname','$gname','$gaddr1','$gaddr2','$gstate','$gzip','$gcountry','$ggrid','$gemail',$gcqzone,$gituzone,$gborn,'$gimage','$mynow')");
    }
  }
  $query=mysqli_query($con,"select firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,image from who where callsign='$Icallsign'");
  $row=mysqli_fetch_assoc($query);
  mysqli_free_result($query);
  printf("<table>");
  printf("<td><pre>%s %s\n%s\n%s\n%s %s %s\n%s\n%s\n%s %s %s\n</pre></td>",$row["firstname"],$row["lastname"],$row["addr1"],$row["addr2"],$row["state"],$row["zip"],$row["country"],$row["grid"],$row["email"],$row["cqzone"],$row["ituzone"],$row["born"]);
  if(strlen($row["image"])>0)printf("<td><a href=\"%s\" target=\"_blank\"><img align=\top\" src=\"%s\" width=\"200\"></a></td>",$row["image"],$row["image"]);
  printf("</table>\n");

  $query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,lotw,eqsl,qrz,contesttx,contestrx,contest from log where callsign='$Icallsign' and mycall='$mycall' order by start desc limit 5");
  for(;;){
    $row=mysqli_fetch_assoc($query);
    if($row==null)break;
    $aux="";
    if((int)$row["lotw"]==1)$aux.="L";
    if((int)$row["eqsl"]==1)$aux.="E";
    if((int)$row["qrz"]==1)$aux.="Q";
    printf("%s %12s %7.1f %4s %5s %5s %-3s ",$row["start"],$row["callsign"],$row["freqtx"]/1000,$row["mode"],$row["signaltx"],$row["signalrx"],$aux);
    if(strlen($row["contest"])>0)printf(" (%s,%s,%s)",$row["contest"],$row["contesttx"],$row["contestrx"]);
    if((int)$row["freqrx"]>0&&(int)$row["freqrx"]!=(int)$row["freqtx"])printf(" [%+.1f]",((int)$row["freqrx"]-(int)$row["freqtx"])/1000);
    printf("\n");
  }
  mysqli_free_result($query);
  echo "\n";

  echo myqso($con,$mycall,$Icallsign);
  echo "\n\n";

  $mys=searchcty($con,$Icallsign);
  myprint($mys);
  echo "\n\n";
  
  $mydbt=dbt($con,$mycall,$Icallsign);
  myprint($mydbt);
  echo "\n\n";
  
  $mydbt=griddb($con,$mycall,$Icallsign);
  myprint($mydbt);
  
}

echo "</pre>";

?>
