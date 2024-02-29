<?php

function myqrzwebcontact($call,&$Ewc){
  $dd=array();
  $Ewc=0;
  $agent="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.75 Safari/537.36";
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,"https://www.qrz.com/lookup/$call");
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
  curl_setopt($ch,CURLOPT_HTTPHEADER,Array("User-Agent: $agent"));
  $out=curl_exec($ch);
  curl_close($ch);
  $tok='var wc_summary = "';
  $l1=strpos($out,$tok,0);
  if($l1===false)return null;
  $l1+=strlen($tok);
  $l2=strpos($out,'"',$l1);
  $myurl=substr($out,$l1,$l2-$l1);
  $tok='<a href="#t_webcon">Web <';
  $l1=strpos($out,$tok,0);
  if($l1!==false)$Ewc=1;
  
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,$myurl);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
  curl_setopt($ch,CURLOPT_HTTPHEADER,Array("User-Agent: $agent"));
  $out=curl_exec($ch);
  curl_close($ch);
  $tok='href="https://www.qrz.com/db/';
  $l2=0;
  $tot=0;
  for(;;){
    $l1=strpos($out,$tok,$l2);
    if($l1===false)break;
    $l1+=strlen($tok);
    $l2=strpos($out,'/',$l1);
    if($l2-$l1>0)$dd[$tot++]=substr($out,$l1,$l2-$l1);
  }
  return $dd;
}

?>
