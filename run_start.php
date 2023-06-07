<?php
// also triggered by ENTER in Call input
include "def_qrz.php";
include "def_list.php";
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

  mylist($con,"where callsign='$Icallsign' and mycall='$mycall' order by start desc limit 5",$mycall,$md5passwd);  
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
