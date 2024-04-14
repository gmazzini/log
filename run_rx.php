<?php
$sock=socket_create(AF_INET,SOCK_DGRAM,0);
socket_bind($sock, "0.0.0.0",2333);
$conn=mysqli_connect("127.0.0.1","power","power123","power");
for(;;){
  socket_recvfrom($sock,$buf,1000,0,$remote_ip,$remote_port);
  $q=explode(" ",trim($buf));
  $tt= date('Y-m-d H:i:s');
  $sql="INSERT INTO power (tt,L1,L2,L3) VALUES ('$tt',$q[2],$q[3],$q[4])";
  mysqli_query($conn,$sql);
}
?>
