<?php

if(isset($_FILES['myfile']['tmp_name'])){
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
  $export_comtest=myextract($aux,"export_contest");
  if(strlen($export_contest)>1)$query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx,contesttx,contestrx from log where mycall='$mycall' and contest='$export_contest' order by start");
  else $query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx,contesttx,contestrx from log where mycall='$mycall' and start>='$export_from' and start<='$export_to' order by start");
  for(;;){
    $row=mysqli_fetch_assoc($query);
    if($row==null)break;
    fprintf($fp,"QSO: %5d %2s %04d-%02d-%02d ",$row["freqtx"]/1000,$mymode[$row["mode"]],substr($row["start"],0,4),substr($row["start"],5,2),substr($row["start"],8,2));
    fprintf($fp,"%02d%02d %-13s %3s %-6s %-13s %3s %-6s 0\n",substr($row["start"],11,2),substr($row["start"],14,2),$mycall,$row["signaltx"],$row["contesttx"],$row["callsign"],$row["signalrx"],$row["contestrx"]);
  }
  fprintf($fp,"END-OF-LOG:\n");
  fclose($fp);
  echo "<pre><a href='https://log.chaos.cc/files/$name' download>Download Cabrillo</a><br>";
  echo "$export_from $export_to\n";
}

?>
