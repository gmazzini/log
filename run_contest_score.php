<?php

$ll=array();
$ll[]=array("ARIDX","https://ik4lzh.mazzini.org/aridx.php");
$ll[]=array("CQWWSSB","https://ik4lzh.mazzini.org/cqww.php");
$ll[]=array("CQWWCW","https://ik4lzh.mazzini.org/cqww.php");
$ll[]=array("CQWWDIGI","https://ik4lzh.mazzini.org/cqwwdigi.php");
$ll[]=array("EUHF","https://ik4lzh.mazzini.org/eudx.php");
$ll[]=array("CQWPXSSB","https://ik4lzh.mazzini.org/cqwpx.php");
$ll[]=array("CQWPXCW","https://ik4lzh.mazzini.org/cqwpx.php");
$ll[]=array("ARRLSSB","https://ik4lzh.mazzini.org/arrldx.php");
$ll[]=array("ARRLCW","https://ik4lzh.mazzini.org/arrldx.php");
$ll[]=array("HADX","https://ik4lzh.mazzini.org/hadx.php");
$ll[]=array("IARUHF","https://ik4lzh.mazzini.org/iaruhf.php");
$ll[]=array("SPDX","https://ik4lzh.mazzini.org/spdx.php");
$ll[]=array("UBASSB","https://ik4lzh.mazzini.org/ubadx.php");
$ll[]=array("UBACW","https://ik4lzh.mazzini.org/ubadx.php");
$ll[]=array("4080","https://ik4lzh.mazzini.org/4080.php");
$ll[]=array("CQBB","https://ik4lzh.mazzini.org/bandebasse.php");

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
  $fn="/home/www/log.chaos.cc/files/$name";
  $fp=fopen($fn,"w");
  fprintf($fp,"START-OF-LOG: 3.0\n");
  fprintf($fp,"CALLSIGN: $mycall\n");
  $query=mysqli_query($con,"select start,callsign,freqtx,mode,signaltx,signalrx,end,freqrx,contesttx,contestrx from log where mycall='$mycall' and contest='$Icontest' order by start");
  for(;;){
    $row=mysqli_fetch_assoc($query);
    if($row==null)break;
    fprintf($fp,"QSO: %5d %2s %04d-%02d-%02d ",$row["freqtx"]/1000,$mymode[$row["mode"]],substr($row["start"],0,4),substr($row["start"],5,2),substr($row["start"],8,2));
    fprintf($fp,"%02d%02d %-13s %3s %-6s %-13s %3s %-6s 0\n",substr($row["start"],11,2),substr($row["start"],14,2),$mycall,$row["signaltx"],$row["contesttx"],$row["callsign"],$row["signalrx"],$row["contestrx"]);
  }
  mysqli_free_result($query);
  fprintf($fp,"END-OF-LOG:\n");
  fclose($fp);
  $aux=file_get_contents("$go?fromlog=$fn");
  echo $aux;
}

?>
