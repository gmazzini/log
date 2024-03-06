<?php

function myqrzsetwebcontact($callsign){
  $agent="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.75 Safari/537.36";
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,"https://www.qrz.com/lookup/$callsign");
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
  curl_setopt($ch,CURLOPT_HTTPHEADER,Array("User-Agent: $agent"));
  $out=curl_exec($ch);
  curl_close($ch);
  $tok='var wc_summary = "';
  $l1=strpos($out,$tok,0);
  if($l1===false)return 0;
  $l1+=strlen($tok);
  $l2=strpos($out,'"',$l1);
  $myurl=substr($out,$l1,$l2-$l1);
  if(strlen($myurl)<5)return 0;
  echo "... myurl $myurl\n";

  $co=json_decode(file_get_contents("/home/www/data/qrz_cookie"),true);
  $cookie="";
  foreach($co as $v)$cookie.=$v["name"]."=".$v["value"]."; ";
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,$myurl);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
  curl_setopt($ch,CURLOPT_COOKIE,$cookie);
  curl_setopt($ch,CURLOPT_HTTPHEADER,Array("User-Agent: $agent"));
  $out=curl_exec($ch);
  curl_close($ch);
  $tok='name="wc_userid" value="';
  $l1=strpos($out,$tok,0);
  if($l1===false)return 0;
  $l1+=strlen($tok);
  $l2=strpos($out,'"',$l1);
  $userid=(int)substr($out,$l1,$l2-$l1);
  if($userid==0)return 0;
  echo "... userid $userid\n";

  $params="webcon=1&wc_userid=$userid";
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,"https://www.qrz.com/db/".$callsign);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
  curl_setopt($ch,CURLOPT_COOKIE,$cookie);
  curl_setopt($ch,CURLOPT_POST,1);
  curl_setopt($ch,CURLOPT_POSTFIELDS,$params);
  curl_setopt($ch,CURLOPT_HTTPHEADER,Array("User-Agent: $agent"));
  curl_exec($ch);
  curl_close($ch);

  return 1;
}

function myqrzwebcontact($call,&$Ewc,&$visit){
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
  $tok='<span class="ml1">Lookups: ';
  $l1=strpos($out,$tok,0);
  if($l1===false)return null;
  $l1+=strlen($tok);
  $l2=strpos($out,'<',$l1);
  $visit=(int)substr($out,$l1,$l2-$l1);
  
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

function myemailsend($from,$to,$subject,$html){
  global $mailgun_secret;
  $post=array('to' => $to,'from' => $from,'subject' => $subject,'html' => $html);
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,"https://api.eu.mailgun.net/v3/mg.mazzini.org/messages");
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
  curl_setopt($ch,CURLOPT_HTTPHEADER,Array("Authorization: Basic ".base64_encode($mailgun_secret)));
  curl_setopt($ch,CURLOPT_POST,1);
  curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
  echo curl_exec($ch);
  curl_close($ch);
}

?>
