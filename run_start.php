<?php
// also triggered by ENTER in Call input
include "def_qrz.php";
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
  
  if($act_start==""){
    $query=mysqli_query($con,"select firstname from who where callsign='$Icallsign'");
    $row=mysqli_fetch_assoc($query);
    mysqli_free_result($query);
    if($row==null||strlen($row["firstname"])==0){
      $aux=qrz($con,$Icallsign);
      if($aux==0)$aux=ru($con,$Icallsign);
    }
  }
  elseif($act_start=="qrz")qrz($con,$Icallsign);
  elseif($act_start=="ru")ru($con,$Icallsign);
  
  $query=mysqli_query($con,"select firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,image,src from who where callsign='$Icallsign'");
  $row=mysqli_fetch_assoc($query);
  mysqli_free_result($query);
  printf("<table>");
  printf("<td><pre>%s %s\n%s\n%s\n%s %s %s\n%s\n%s\n%s %s %s %s\n</pre></td>",cyrlat($row["firstname"],$tra),cyrlat($row["lastname"],$tra),cyrlat($row["addr1"],$tra),cyrlat($row["addr2"],$tra),cyrlat($row["state"],$tra),$row["zip"],cyrlat($row["country"],$tra),$row["grid"],$row["email"],$row["cqzone"],$row["ituzone"],$row["born"],$row["src"]);
  if(strlen($row["image"])>0)printf("<td><a href=\"%s\" target=\"_blank\"><img align=\top\" src=\"%s\" width=\"200\"></a></td>",$row["image"],$row["image"]);
  echo "<td id=\"myq1\">";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"renew qrz\">QRZ.com</button><br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"renew ru\">QRZ.ru</button>";
  echo "</td>";
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
  
  $dxcc=$mys["dxcc"];
  $query=mysqli_query($con,"select count(callsign) from log where mycall='$mycall' and dxcc=$dxcc");
  $row=mysqli_fetch_row($query);
  mysqli_free_result($query);
  echo "Same dxcc[$dxcc]: $row[0]\n\n";
  
  $mydbt=dbt($con,$mycall,$Icallsign);
  myprint($mydbt);
  echo "\n\n";
  
  $mydbt=griddb($con,$mycall,$Icallsign);
  myprint($mydbt);
  
}

echo "</pre>";

?>
