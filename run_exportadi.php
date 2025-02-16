<?php

if(isset($_FILES['myfile']['tmp_name'])){
  $name=rand().rand().rand().rand().".adi";
  $fp=fopen("/home/www/log/files/$name","w");
  fprintf($fp,"%s\n",myinsert("LZHlogger","PROGRAMID"));
  fprintf($fp,"<EOH>\n\n");
  $aux=file_get_contents($_FILES['myfile']['tmp_name']);
  $export_from=myextract($aux,"export_from");
  $export_to=myextract($aux,"export_to");
  $export_contest=myextract($aux,"export_contest");
  if(strlen($export_contest)>1)$query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx,contesttx,contestrx,contest from log where mycall='$mycall' and contest='$export_contest' order by start");
  else $query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx,contesttx,contestrx,contest from log where mycall='$mycall' and start>='$export_from' and start<='$export_to' order by start");
  for(;;){
    $row=mysqli_fetch_assoc($query);
    if($row==null)break;
    fprintf($fp,"%s\n",myinsert($row["callsign"],"CALL"));
    fprintf($fp,"%s\n",myinsert(substr($row["start"],0,4).substr($row["start"],5,2).substr($row["start"],8,2),"QSO_DATE"));
    fprintf($fp,"%s\n",myinsert(substr($row["start"],11,2).substr($row["start"],14,2).substr($row["start"],17,2),"TIME_ON"));
    fprintf($fp,"%s\n",myinsert(substr($row["end"],0,4).substr($row["end"],5,2).substr($row["end"],8,2),"QSO_DATE_OFF"));
    fprintf($fp,"%s\n",myinsert(substr($row["end"],11,2).substr($row["end"],14,2).substr($row["end"],17,2),"TIME_OFF"));
    fprintf($fp,"%s\n",myinsert(sprintf("%7.5f",$row["freqtx"]/1000000),"FREQ"));
    fprintf($fp,"%s\n",myinsert(sprintf("%7.5f",$row["freqrx"]/1000000),"FREQ_RX"));
    fprintf($fp,"%s\n",myinsert($row["signaltx"],"RST_SENT"));
    fprintf($fp,"%s\n",myinsert($row["signalrx"],"RST_RCVD"));
    fprintf($fp,"%s\n",myinsert($row["mode"],"MODE")); 
    fprintf($fp,"%s\n",myinsert($row["contesttx"],"STX_STRING"));
    fprintf($fp,"%s\n",myinsert($row["contestrx"],"SRX_STRING"));
    fprintf($fp,"%s\n",myinsert($row["contest"],"CONTEST_ID"));
    fprintf($fp,"<EOR>\n\n");
  }
  mysqli_free_result($query);
  fclose($fp);
  echo "<pre><a href='https://log.mazzini.org/files/$name' download>Download ADIF</a><br>";
  echo "$export_from $export_to\n";
}

?>
