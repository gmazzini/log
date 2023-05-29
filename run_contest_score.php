<?php

$ll=array();
$ll[]=array("ARIDX","https://ik4lzh.mazzini.org/aridx.php");
$ll[]=array("CQWWSSB","https://ik4lzh.mazzini.org/cqww.php");
$ll[]=array("CQWWCW","https://ik4lzh.mazzini.org/cqww.php");

echo "<pre>";
$go="";
if(strlen($Icontest)>0){
  foreach($ll as $v){
    if(strstr($Icontest,$v[0])){
      $go=$v[1];
      break;
    }
  }
}

if($go!=""){
  $name=rand().rand().rand().rand().".cbr";
  $fp=fopen("/home/www/log.chaos.cc/files/$name","w");
  fprintf($fp,"START-OF-LOG: 3.0\n");
  fprintf($fp,"CALLSIGN: $mycall\n");


  if(strlen($export_contest)>1)$query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx,contesttx,contestrx from log where mycall='$mycall' and contest='$export_contest' order by start");
  else $query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx,contesttx,contestrx from log where mycall='$mycall' and start>='$export_from' and start<='$export_to' order by start");
  for(;;){
    $row=mysqli_fetch_assoc($query);
    if($row==null)break;
    fprintf($fp,"QSO: %5d %2s %04d-%02d-%02d ",$row["freqtx"]/1000,$mymode[$row["mode"]],substr($row["start"],0,4),substr($row["start"],5,2),substr($row["start"],8,2));
    fprintf($fp,"%02d%02d %-13s %3s %-6s %-13s %3s %-6s 0\n",substr($row["start"],11,2),substr($row["start"],14,2),$mycall,$row["signaltx"],$row["contesttx"],$row["callsign"],$row["signalrx"],$row["contestrx"]);
  }
  mysqli_free_result($query);
  fprintf($fp,"END-OF-LOG:\n");
  fclose($fp);

echo "$go\n";
echo "</pre>";

?>
