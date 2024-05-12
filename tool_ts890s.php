<?php
include "local.php";
$sock1=socket_create(AF_INET,SOCK_DGRAM,0);
socket_bind($sock1,"0.0.0.0",22222);
$sock2=socket_create(AF_INET,SOCK_STREAM,0);
socket_connect($sock2,"10.0.0.10",60000);
for(;;){
  socket_recvfrom($sock1,$aux,1000,0,$remote_ip,$remote_port);
  echo $aux."\n";

}

function xx($ss,$mm){
  socket_write($ss,$mm,strlen($mm));
  $rr=socket_read($ss,1024);
}
?>
