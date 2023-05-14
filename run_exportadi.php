<?php

if(isset($_FILES['myfile']['tmp_name'])){
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
}

?>
