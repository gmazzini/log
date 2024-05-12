<?php
include "login.php";
$sock1=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
socket_bind($sock1,"0.0.0.0",6780);
socket_listen($sock1,5);
$sock2=socket_create(AF_INET,SOCK_STREAM,0);
socket_connect($sock2,"10.0.0.10",60000);
$aux=trim(xx($sock2,"##CN;"));
if($aux!="##CN1;"){echo "CN problem\n"; exit(-1);}
$aux=trim(xx($sock2,"##ID00".sprintf("%d%d%s%s;",strlen($ts890s_login),strlen($ts890s_passwd),$ts890s_login,$ts890s_passwd)));
if($aux!="##ID1;"){echo "ID problem\n"; exit(-1);}
msg=socket_accept($sock1);  
for(;;){
  $buf=socket_read($msg,1024);
  if(strpos($buf,"i")!==false){
    $aux=xx($sock2,"FA;");
    $mm=substr($aux,2,-1)."\n";
    socket_write($msg,$mm,strlen($mm));
  }
  echo ".\n";
}
function xx($ss,$mm){
  socket_write($ss,$mm,strlen($mm));
  $rr=socket_read($ss,1024);
  return $rr;
}
?>
