<?php
include "local.php";
$sock=socket_create(AF_INET,SOCK_DGRAM,0);
socket_bind($sock,"0.0.0.0",22222);
for(;;){
  socket_recvfrom($sock,$aux,1000,0,$remote_ip,$remote_port);
  echo $aux."\n";

}
?>
