<?php

function mylist($con,$condition,$mycall,$md5passwd){
  echo "<pre>";
  $query=mysqli_query($con,"select start,end,callsign,freqtx,freqrx,mode,signaltx,signalrx,lotw,eqsl,qrz,contesttx,contestrx,contest from log $condition");
  for(;;){
    $row=mysqli_fetch_assoc($query);
    if($row==null)break;
    $aux="";
    if((int)$row["lotw"]==1)$aux.="L";
    if((int)$row["eqsl"]==1)$aux.="E";
    if((int)$row["qrz"]==1)$aux.="Q";
    
    $timediff=strtotime($row["end"])-strtotime($row["start"]);
    if($timediff==0)$taux="(0s)";
    elseif($timediff<60)$taux=sprintf("(%ds)",$timediff);
    elseif ($timediff<3600)$taux=sprintf("(%dm)",$timediff/60);
    else $taux=sprintf("(%dh)",$timediff/3600);
    
    printf("<button type=\"button\" id=\"myb2\" onclick=\"mycommand('%s','%s','%s','%s')\"> </button> ",$mycall,$md5passwd,$row["start"],$row["callsign"]);
    printf("%s%5s %12s %7.1f %4s %5s %5s %-3s ",$row["start"],$taux,$row["callsign"],$row["freqtx"]/1000,$row["mode"],$row["signaltx"],$row["signalrx"],$aux);
    if(strlen($row["contest"])>0)printf(" (%s,%s,%s)",$row["contest"],$row["contesttx"],$row["contestrx"]);
    if((int)$row["freqrx"]>0&&(int)$row["freqrx"]!=(int)$row["freqtx"])printf(" [%+.1f]",((int)$row["freqrx"]-(int)$row["freqtx"])/1000);
    printf("\n");
  }
  echo "</pre>";
  mysqli_free_result($query);
}

?>
<script>
function mycommand(mycall,md5passwd,start,callsign){
  var xmlhttp=new XMLHttpRequest();
  let command=prompt("DEL DELETE FT,xxx FREQTX,xxx FR,xxx FREQRX,xxx M,xxx MODE,xxx ST,xxx SIGNALTX,xxx SR,xxx SIGNALRX,xxx C,xxx CALL,xxx DT,xxx DATETIME,xxx CO,xxx CONTEST,xxx COT,xxx CONTESTTX,xxx COR,xxx CONTESTRX,xxx","");
  xmlhttp.open("GET","act_command.php?mycall="+encodeURIComponent(mycall)+"&md5passwd="+encodeURIComponent(md5passwd)+"&start="+encodeURIComponent(start)+"&callsign="+encodeURIComponent(callsign)+"&command="+encodeURIComponent(command),true);
  xmlhttp.send();
}
</script>
