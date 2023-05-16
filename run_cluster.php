<?php

echo "<pre>";
$myrow=0;
$query=mysqli_query($con,"select dx,spotter,freq,timespot from dxc order by timespot desc limit 1000");
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
  $freq=$row[2]/1000;
  for($i=0;;$i++){
    if(!isset($bpfreq[$i]))break;
    if($freq>=$bpfreq[$i][1]&&$freq<$bpfreq[$i][2])break;
  }
  if(isset($bpfreq[$i])&&$dxcsel[$bpfreq[$i][0]]&&$dxcsel[$bpfreq[$i][3]]){
    printf("%s %12s ",$row[3],$row[0]);
    printf("<button type=\"button\" onclick=\"myfreq(%s)\">%7.1f</button> ",$row[2],$freq);
    printf("%10s %s\n",$row[1],myqso($con,$mycall,$row[0]));
    $myrow++;
    if($myrow>$mypage)break;
  }
}
echo "</pre>";
mysqli_free_result($query);

?>
<script>
function myfreq(freq){
  var xmlhttp=new XMLHttpRequest();
  xmlhttp.open("GET","setfreq.php?freq="+freq+"&rigIP=<?php echo $rigIP; ?>&rigPORT=<?php echo $rigPORT; ?>",true);
  xmlhttp.send();
}
</script>
