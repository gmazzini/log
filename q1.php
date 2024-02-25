<?php

$co=json_decode(file_get_contents("c1.txt"),true);
$call="2E0GSZ";

  $dd=array();
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

  $cookie="";
  foreach($co as $v)$cookie.=$v["name"]."=".$v["value"]."; ";
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,$myurl);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
  curl_setopt($ch,CURLOPT_COOKIE,$cookie);
  curl_setopt($ch,CURLOPT_HTTPHEADER,Array("User-Agent: $agent"));
  $out=curl_exec($ch);
file_put_contents("c2.txt",$out);
  curl_close($ch);
  $tok='name="wc_userid" value="';
  $l1=strpos($out,$tok,0);
  if($l1===false)return null;
  $l1+=strlen($tok);
  $l2=strpos($out,'"',$l1);
  $userid=substr($out,$l1,$l2-$l1);

  $params="webcon=1&wc_userid=$userid";
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,"https://www.qrz.com/db/".$call);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
  curl_setopt($ch,CURLOPT_COOKIE,$cookie);
  curl_setopt($ch,CURLOPT_POST,1);
  curl_setopt($ch,CURLOPT_POSTFIELDS,$params);
  curl_setopt($ch,CURLOPT_HTTPHEADER,Array("User-Agent: $agent"));
  curl_exec($ch);
  curl_close($ch);

?>
